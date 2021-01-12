<?php

namespace Dollie\Core\Modules\Jobs;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Modules\Sites\WP;
use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;
use Dollie\Core\Log;

/**
 * Class SyncContainersJob
 *
 * @package Dollie\Core\Modules
 */
class SyncContainersJob extends Singleton {

	/**
	 * Jobs constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'init', [ $this, 'init_recurring_tasks' ] );

		add_action( 'dollie/jobs/single/sync_containers', [ $this, 'run' ], 10 );
		add_action( 'dollie/jobs/recurring/sync_containers', [ $this, 'run' ], 10 );
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
	 * Create containers if they don't exist locally
	 * Delete containers if they are no longer running, eq. deleted, undeployed
	 *
	 * @return array|mixed
	 */
	public function run() {
		// Get list of container from remote API
		$get_containers_request = Api::post( Api::ROUTE_CONTAINER_GET );
		$containers_response    = Api::process_response( $get_containers_request, null );

		if ( false === $containers_response || 500 === $containers_response['status'] ) {
			return [];
		}

		$containers = dollie()->maybe_decode_json( $containers_response['body'], true );

		$synced_container_ids = [];

		if ( ! $containers || ! is_array( $containers ) || isset( $containers['error'] ) ) {
			return [];
		}

		foreach ( $containers as $key => $container ) {
			$domain = '';

			if ( $container['uri'] ) {
				$full_url        = parse_url( $container['uri'] );
				$stripped_domain = explode( '.', $full_url['host'] );
				$domain          = $stripped_domain[0] === 'www' ? $stripped_domain[1] : $stripped_domain[0];
			}

			// Skip if no domain
			if ( ! $domain ) {
				continue;
			}

			// Get container from client's WP install with the server's container ID
			$client_containers = get_posts(
				[
					'post_type'   => 'container',
					'post_status' => [ 'publish', 'draft', 'trash' ],
					'meta_query'  => [
						[
							'key'     => 'wpd_container_id',
							'value'   => $container['id'],
							'compare' => '=',
						],
					],
				]
			);

			// Get email from the description field and then find author ID based on email.
			$description = explode( '|', $container['description'], 2 );
			$email       = trim( $description[0] );
			$author      = get_user_by( 'email', $email );

			if ( ! $author ) {
				$author = wp_get_current_user();
			}

			$container_post_id = false;

			// If no such container found, create one with details from server's container.
			if ( ! $client_containers ) {

				$container_post_id = wp_insert_post(
					[
						'post_type'   => 'container',
						'post_status' => 'publish',
						'post_name'   => $domain,
						'post_title'  => $domain,
						'post_author' => $author->ID,
						'meta_input'  => [
							'wpd_container_id'          => $container['id'],
							'_wpd_container_data'       => WP::instance()->get_filtered_store_data( $container ),
							'wpd_container_status'      => $container['status'],
							'wpd_node_added'            => 'yes',
							'wpd_setup_complete'        => 'yes',
							'wpd_refetch_secret_key'    => 'yes',
							'wpd_container_launched_by' => $email,
						],
					]
				);

				Log::add( 'Container added from sync ' . $domain );

			}

			// Trash container if is not deployed
			if ( 'Running' !== $container['status'] && $container_post_id ) {
				wp_trash_post( $container_post_id );
			}

			$synced_container_ids[] = $container['id'];
		}

		// Delete posts if they have no corresponding container
		$stored_containers = get_posts(
			[
				'post_type'   => 'container',
				'post_status' => [ 'publish', 'draft' ],
			]
		);

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
