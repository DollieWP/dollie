<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;
use Dollie\Core\Log;

/**
 * Class Jobs
 * @package Dollie\Core\Modules
 */
class Jobs extends Singleton {

	/**
	 * Jobs constructor.
	 */
	public function __construct() {
		parent::__construct();

		// Init tasks
		add_action( 'init', [ $this, 'init_recurring_tasks' ] );

		// Jobs hooks
		add_action( 'dollie/jobs/single/change_container_customer_role', [
			$this,
			'run_change_customer_role_task'
		], 10, 3 );
		add_action( 'dollie/jobs/single/sync_containers', [ $this, 'run_sync_containers_task' ], 10 );
		add_action( 'dollie/jobs/recurring/sync_containers', [ $this, 'run_sync_containers_task' ], 10 );
	}

	/**
	 * Init recurring tasks
	 */
	public function init_recurring_tasks() {
		if ( false === as_next_scheduled_action( 'dollie/jobs/recurring/sync_containers' ) ) {
			as_schedule_recurring_action( strtotime( 'today' ), DAY_IN_SECONDS, 'dollie/jobs/recurring/sync_containers' );
		}
	}

	/**
	 * Change customer role task
	 *
	 * @param $params
	 * @param null $user_id
	 * @param null $role
	 *
	 * @return bool
	 */
	public function run_change_customer_role_task( $params, $user_id = null, $role = null ) {
		$user_id = $user_id ?: get_current_user_id();
		$role    = $role ?: dollie()->get_customer_user_role( $user_id );

		if ( ! is_array( $params ) || ! isset( $params['container_uri'], $params['email'], $params['password'], $params['username'] ) || ! $role ) {
			Log::add( 'Client user role change failed due to missing param.' );

			return false;
		}

		$data = [
			'container_uri'  => $params['container_uri'],
			'email'          => $params['email'],
			'password'       => $params['password'],
			'username'       => $params['username'],
			'super_email'    => get_option( 'admin_email' ),
			'super_password' => wp_generate_password(),
			'super_username' => get_option( 'options_wpd_admin_user_name' ),
			'switch_to'      => $role
		];

		Api::post( Api::ROUTE_CHANGE_USER_ROLE, $data );

		Log::add( $params['container_uri'] . ' client access was set to ' . $role );

		return false;
	}

	/**
	 * Create containers if they don't exist locally
	 * Delete containers if they are no longer running, eq. deleted, undeployed
	 *
	 * @return array|mixed
	 */
	public function run_sync_containers_task() {

		// Get list of container from remote API
		$get_containers_request = Api::post( Api::ROUTE_CONTAINER_GET, [
			'dollie_domain' => DOLLIE_INSTALL,
			'dollie_token'  => Api::get_dollie_token(),
		] );

		// Convert JSON into array.
		$get_containers_response = json_decode( wp_remote_retrieve_body( $get_containers_request ), true );

		if ( $get_containers_response['status'] === 500 ) {
			return [];
		}

		$containers           = json_decode( $get_containers_response['body'], true );
		$synced_container_ids = [];

		foreach ( $containers as $key => $container ) {
			$domain = '';
			if ( $container['uri'] ) {
				$full_url        = parse_url( $container['uri'] );
				$stripped_domain = explode( '.', $full_url['host'] );
				$domain          = $stripped_domain[0];
			}

			// Skip if no domain
			if ( ! $domain ) {
				continue;
			}

			// Get container from client's WP install with the server's container ID
			$client_containers = get_posts( [
				'post_type'  => 'container',
				'meta_query' => [
					[
						'key'     => 'wpd_container_id',
						'value'   => $container['id'],
						'compare' => '=',
					],
				]
			] );

			// Get email from the description field and then find author ID based on email.
			$description = explode( '|', $container['description'], 2 );
			$email       = trim( $description[0] );
			$author      = get_user_by( 'email', $email );

			if ( ! $author ) {
				$author = wp_get_current_user();
			}

			$container_post_id = false;

			// If any such container found, update the container author ID based on the email in the "description" field from server's container.
			if ( $client_containers ) {
				foreach ( $client_containers as $client_container ) {
					$container_post_id = $client_container->ID;

					// Update author field of all containers.
					wp_update_post( [
						'ID'         => $client_container->ID,
						// 'post_author' => $author->ID, // If we reassign the container, this will put it back to the old user
						'post_name'  => $domain,
						'post_title' => $domain,
					] );
				}
			} else {
				// If no such container found, create one with details from server's container.
				// Add new container post to client's WP
				$container_post_id = wp_insert_post( [
					'post_type'   => 'container',
					'post_status' => 'publish',
					'post_name'   => $domain,
					'post_title'  => $domain,
					'post_author' => $author->ID,
					'meta_input'  => [
						'wpd_container_id'          => $container['id'],
						'wpd_container_user'        => $container['containerSshUsername'],
						'wpd_container_port'        => $container['containerSshPort'],
						'wpd_container_password'    => $container['containerSshPassword'],
						'wpd_container_ip'          => $container['containerHostIpAddress'],
						'wpd_container_status'      => $container['status'],
						'wpd_container_launched_by' => $email,
						'wpd_container_deploy_time' => $container['deployedAt'],
						'wpd_container_uri'         => $container['uri'],
						'wpd_node_added'            => 'yes',
						'wpd_setup_complete'        => 'yes',
						'wpd_refetch_secret_key'    => 'yes',
					],
				] );

				Log::add( 'Container added from sync '. $domain );

			}

			// Trash container if is not deployed
			if ( $container['status'] !== 'Running' && $container_post_id ) {
				wp_trash_post( $container_post_id );
			}

			$synced_container_ids[] = $container['id'];
		}

		// Delete posts if they have no corresponding container
		$stored_containers = get_posts( [
			'post_type' => 'container'
		] );

		foreach ( $stored_containers as $stored_container ) {
			$container_id = get_post_meta( $stored_container->ID, 'wpd_container_id', true );

			if ( ! in_array( $container_id, $synced_container_ids, false ) ) {
				wp_trash_post( $stored_container->ID );
			}
		}

		// Flush permalink
		flush_rewrite_rules();

		return $containers;
	}

}
