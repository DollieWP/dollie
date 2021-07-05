<?php

namespace Dollie\Core\Modules\Sites;

use Dollie\Core\Log;
use Dollie\Core\Modules\Blueprints;
use Dollie\Core\Modules\Container;
use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WP
 *
 * @package Dollie\Core\Modules
 */
final class WP extends Singleton {

	const POST_SUFFIX = '-failed';
	/**
	 * Used for remote call. Gives false positive in WP repo checks
	 */
	const PLATFORM_PATH = '/wp-content/mu-plugins/platform/'; // phpcs:ignore

	public function __construct() {
		parent::__construct();

		add_action( 'template_redirect', [ $this, 'update_deploy' ] );
		add_action( 'template_redirect', [ $this, 'update_deploy_setup_data' ], 12 );
		add_action( 'wp_ajax_dollie_check_deploy', [ $this, 'check_deploy' ] );
	}

	/**
	 * Deploy site
	 *
	 * @param array $deploy_data
	 * @param array $setup_data
	 *
	 * @return bool|void
	 */
	public function deploy_site( $deploy_data, $setup_data = [] ) {
		if ( ! isset( $deploy_data ) ) {
			return false;
		}

		$email             = $deploy_data['email'];
		$domain            = $deploy_data['domain'];
		$user_id           = $deploy_data['user_id'];
		$site_type         = $deploy_data['site_type'];
		$blueprint         = isset( $deploy_data['blueprint'] ) ? $deploy_data['blueprint'] : null;
		$blueprint_install = $blueprint ? dollie()->get_wp_site_data( 'uri', $blueprint ) : null;

		$deploy_start_log  = Log::WP_SITE_DEPLOY_STARTED;
		$deploy_failed_log = Log::WP_SITE_DEPLOY_FAILED;

		$post_id = wp_insert_post(
			[
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'post_author'    => $user_id,
				'post_name'      => $domain,
				'post_title'     => $domain . ' [deploy pending]',
				'post_status'    => 'publish',
				'post_type'      => 'container',
			]
		);

		// Mark site as blueprint.
		if ( 'blueprint' === $site_type ) {
			update_post_meta( $post_id, 'wpd_is_blueprint', 'yes' );
			$deploy_start_log  = Log::WP_BLUEPRINT_DEPLOY_STARTED;
			$deploy_failed_log = Log::WP_BLUEPRINT_DEPLOY_FAILED;
		}

		Container::instance()->set_status( $post_id, 'pending' );

		$env_vars_extras = apply_filters( 'dollie/launch_site/extras_envvars', [], $domain, $user_id, $email, $blueprint, $deploy_data );

		if ( $blueprint ) {
			setcookie( DOLLIE_BLUEPRINTS_COOKIE, '', time() - 3600, '/' );
			update_post_meta( $post_id, 'wpd_from_blueprint', $blueprint );
		}

		$post_body = [
			'route'           => 'blueprint' !== $site_type ? $domain . DOLLIE_DOMAIN : $domain . '.wp-site.xyz',
			'description'     => $email . ' | ' . get_site_url(),
			'site_type'       => $site_type,
			'apply_blueprint' => $blueprint_install,
			'envVars'         => array_merge(
				$env_vars_extras,
				[
					'S5_DEPLOYMENT_URL' => get_site_url(),
				]
			),
		];

		// Send the API request.
		$request_container_deploy  = Api::post( Api::ROUTE_CONTAINER_DEPLOY, $post_body );
		$response_container_deploy = Api::process_response( $request_container_deploy );

		if ( ! $response_container_deploy ) {
			Log::add_front(
				$deploy_failed_log,
				dollie()->get_current_object( $post_id ),
				$domain,
				print_r( $request_container_deploy, true )
			);

			$this->set_failed_site( $post_id );

			return false;
		}

		if ( ! $response_container_deploy['job'] ) {
			Log::add_front(
				$deploy_failed_log,
				dollie()->get_current_object( $post_id ),
				$domain,
				print_r( $request_container_deploy, true )
			);

			$this->set_failed_site( $post_id );
			delete_transient( 'wpd_partner_subscription' );

			return false;
		}

		update_post_meta( $post_id, 'wpd_container_launched_by', $email );
		update_post_meta( $post_id, '_wpd_setup_data', $setup_data );

		Container::instance()->set_deploy_job( $post_id, $response_container_deploy['job'] );

		Log::add_front(
			$deploy_start_log,
			dollie()->get_current_object( $post_id ),
			$domain
		);

		// Prevent any backup request for a bit.
		$backups_transient_name = 'dollie_' . $domain . '_backups_data';
		set_transient( $backups_transient_name, [], 60 * 10 );

		return true;
	}

	/**
	 * Set site to failed status
	 *
	 * @param int $id
	 *
	 * @return void
	 */
	private function set_failed_site( $id ) {
		$site = get_post( $id );

		wp_update_post(
			[
				'ID'          => $id,
				'post_name'   => $site->post_name,
				'post_title'  => $site->post_name . ' [deploy failed]',
				'post_status' => 'draft',
			]
		);

		Container::instance()->remove_deploy_job( $id );
	}

	/**
	 * Check deploy status by AJAX
	 */
	public function check_deploy() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'check_deploy_nonce' ) ) {
			wp_send_json_error();
		}

		$deploy_job_uuid = Container::instance()->get_deploy_job( $_REQUEST['container'] );

		if ( ! $deploy_job_uuid ) {
			wp_send_json_error();
		}

		$deploy_check_route           = str_replace( '{uuid}', $deploy_job_uuid, Api::ROUTE_CONTAINER_DEPLOY_GET );
		$request_container_get_deploy = Api::post( $deploy_check_route, [] );

		if ( is_wp_error( $request_container_get_deploy ) ) {
			Log::add( 'API error (see log)', $request_container_get_deploy->get_error_message(), 'deploy' );

			wp_send_json_error();
		}

		$data = Api::process_response( $request_container_get_deploy );

		if ( empty( $data ) ) {
			Log::add( 'API error: Deploy not found', '', 'deploy' );

			wp_send_json_error();
		}

		if ( $data['status'] === 0 ) {
			wp_send_json_error();
		}

		wp_send_json_success();
	}

	/**
	 * Update pending deploys on template redirect
	 *
	 * @return void
	 */
	public function update_deploy() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$args = [
			'post_type' => 'container',
			'meta_key'  => 'wpd_container_deploy_job',
			'author'    => get_current_user_id(),
		];

		$pending_deploys = get_posts( $args );

		if ( ! empty( $pending_deploys ) ) {
			foreach ( $pending_deploys as $deploy_container ) {
				$this->update_deploy_for_container( $deploy_container->ID );
				$this->update_deploy_setup_data_for_container( $deploy_container->ID );
			}
		}
	}

	/**
	 * Update deploy
	 *
	 * @param null $post_id
	 */
	public function update_deploy_for_container( $post_id = null ) {

		$dollie_obj      = dollie()->get_current_object( $post_id );
		$deploy_job_uuid = Container::instance()->get_deploy_job( $post_id );

		if ( ! $deploy_job_uuid ) {
			return;
		}

		$deploy_check_route           = str_replace( '{uuid}', $deploy_job_uuid, Api::ROUTE_CONTAINER_DEPLOY_GET );
		$request_container_get_deploy = Api::post( $deploy_check_route, [] );
		$data                         = Api::process_response( $request_container_get_deploy );

		if ( ! $data ) {
			Log::add_front(
				Log::WP_SITE_DEPLOY_FAILED,
				$dollie_obj,
				$dollie_obj->slug,
				print_r( $request_container_get_deploy, true )
			);

			$this->set_failed_site( $post_id );

			return;
		}

		$domain = $data['route'];

		if ( 0 === $data['status'] || 4 === $data['status'] ) {
			return;
		}

		if ( 2 === $data['status'] ) {
			Container::instance()->set_status( $post_id, 'failed' );

			Log::add_front(
				Log::WP_SITE_DEPLOY_FAILED,
				$dollie_obj,
				$dollie_obj->slug,
				print_r( $request_container_get_deploy, true )
			);
			$this->set_failed_site( $post_id );

			return;
		}

		if ( ! isset( $data['data'] ) || empty( $data['data'] ) ) {
			Log::add( $domain . ' API error: Deploy done but not data received', '', 'deploy' );

			Log::add_front(
				Log::WP_SITE_DEPLOY_FAILED,
				$dollie_obj,
				$dollie_obj->slug,
				print_r( $request_container_get_deploy, true )
			);

			$this->set_failed_site( $post_id );

			return;
		}

		$data_container = $data['data']['deployment'];

		wp_update_post(
			[
				'ID'          => $post_id,
				'post_status' => 'publish',
				'post_name'   => $dollie_obj->slug,
				'post_title'  => $dollie_obj->slug,
			]
		);

		$this->store_container_data( $post_id, $data_container );

		Container::instance()->set_status( $post_id, 'start' );
		Container::instance()->remove_deploy_job( $post_id );
		Container::instance()->get_container_details( $post_id );
	}

	/**
	 * Update setup data after deploy. When loading the container page
	 */
	public function update_deploy_setup_data() {
		if ( ! is_singular( 'container' ) ) {
			return;
		}
		$post_id = get_the_ID();

		$this->update_deploy_setup_data_for_container( $post_id );

	}

	/**
	 * Run after the deploy to setup initial WP site details
	 * @
	 *
	 * @param null $post_id
	 */
	public function update_deploy_setup_data_for_container( $post_id = null ) {

		// in case the deploy failed
		if ( get_post_status( $post_id ) === 'draft' ) {
			return;
		}

		// if is already completed
		if ( get_post_meta( $post_id, 'wpd_setup_complete', true ) === 'yes' ) {
			return;
		}

		// bail if the container hasn't completed self::update_deploy
		if ( Container::instance()->get_deploy_job( $post_id ) ) {
			return;
		}

		$dollie_obj = dollie()->get_current_object( $post_id );

		// Get saved data
		$setup_data = get_post_meta( $post_id, '_wpd_setup_data', true );

		if ( $this->is_null_or_empty( $setup_data['username'] ) ) {
			$setup_data['username'] = get_user_by( 'ID', $dollie_obj->author )->user_login;
		}

		if ( $this->is_null_or_empty( $setup_data['password'] ) ) {
			$setup_data['password'] = wp_generate_password( 8, false );
		}

		if ( $this->is_null_or_empty( $setup_data['name'] ) ) {
			$setup_data['name'] = esc_html__( 'My New Site', 'dollie' );
		}

		if ( $this->is_null_or_empty( $setup_data['description'] ) ) {
			$setup_data['description'] = esc_html__( 'The best website in the world', 'dollie' );
		}

		if ( $setup_data ) {

			$setup_data['container_uri'] = dollie()->get_wp_site_data( 'uri', $post_id );

			$status = $this->update_site_details( $setup_data, $post_id );

			if ( is_wp_error( $status ) ) {
				Log::add_front( Log::WP_SITE_SETUP_FAILED, $dollie_obj, $dollie_obj->slug );
			}
		}

		if ( dollie()->is_blueprint( $post_id ) ) {
			Log::add_front( Log::WP_BLUEPRINT_DEPLOYED, $dollie_obj, $dollie_obj->slug );
		} else {
			Log::add_front( Log::WP_SITE_DEPLOYED, $dollie_obj, $dollie_obj->slug );

		}
	}

	private function is_null_or_empty( $val ) {
		return ! isset( $val ) || empty( $val );
	}


	/**
	 * Update WP site details.
	 *
	 * @param $data
	 * @param null $container_id
	 *
	 * @return bool|\WP_Error
	 */
	public function update_site_details( $data, $container_id = null ) {
		if ( ! is_array( $data ) || empty( $data ) ) {
			return false;
		}

		$container    = dollie()->get_current_object( $container_id );
		$container_id = $container->id;

		do_action( 'dollie/launch_site/set_details/before', $container_id, $data );

		if ( ! isset( $data ) || empty( $data ) ) {

			Log::add( 'Invalid site details data', json_encode( $data ), 'setup' );

			return new \WP_Error(
				'dollie_launch',
				__( 'Failed to update site details(invalid site data)', 'dollie' ),
				$container
			);
		}

		Api::process_response( Api::post( Api::ROUTE_WIZARD_SETUP, $data ) );

		// Change user access for site
		// TODO move to the rundeck job
		if ( dollie()->get_customer_user_role() !== 'administrator' ) {
			sleep( 5 );

			$action_id = as_enqueue_async_action(
				'dollie/jobs/single/change_container_customer_role',
				[
					'params'       => $data,
					'container_id' => $container_id,
					'user_id'      => $container->author,
				]
			);

			update_post_meta( $container_id, '_wpd_user_role_change_pending', $action_id );

		}

		dollie()->flush_container_details();

		update_post_meta( $container_id, 'wpd_setup_complete', 'yes' );

		// Log::add_front( Log::WP_SITE_SETUP_COMPLETED, $current_query, $container_slug );

		// Skip the initial backup
		// Backups::instance()->make( $container_id );

		do_action( 'dollie/launch_site/set_details/after', $container_id, $data );

		return true;
	}

	/**
	 * Get container details from post meta
	 *
	 * @param $data
	 * @param null $container_id
	 *
	 * @return false|mixed
	 */
	public function get_container_data( $data, $container_id = null ) {
		if ( null === $container_id ) {
			$container_id = dollie()->get_current_object( $container_id )->id;
		}

		if ( empty( $container_id ) ) {
			return '';
		}

		if ( 'id' === $data ) {
			return get_post_meta( $container_id, 'wpd_container_id', true );
		}

		$meta = get_post_meta( $container_id, '_wpd_container_data', true );
		if ( empty( $meta ) ) {
			$meta = [];
		}

		if ( isset( $meta[ $data ] ) ) {
			return $meta[ $data ];
		}

		// Old options.
		$map = $this->compat_meta_store_data_map();
		if ( $old_data = get_post_meta( $container_id, $map[ $data ], true ) ) {
			$meta[ $data ] = $old_data;
			update_post_meta( $container_id, '_wpd_container_data', $meta );

			delete_post_meta( $container_id, $map[ $data ] );

			return $old_data;
		}

		return '';
	}

	/**
	 * Save container details in post meta
	 *
	 * @param $post_id
	 * @param $data_container
	 */
	public function store_container_data( $post_id, $data_container ) {

		if ( ! empty( $data_container['id'] ) ) {
			update_post_meta( $post_id, 'wpd_container_id', $data_container['id'], true );
		}

		$data = $this->get_filtered_store_data( $data_container );

		if ( ! empty( $data ) ) {
			$old_data = get_post_meta( $post_id, '_wpd_container_data', true );

			if ( ! empty( $old_data ) && is_array( $old_data ) ) {
				$data = array_merge( $old_data, $data );
			}

			update_post_meta( $post_id, '_wpd_container_data', $data );
		}
	}

	/**
	 * @param $data_container
	 *
	 * @return array
	 */
	public function get_filtered_store_data( $data_container ) {
		$map_data = [
			'ssh_port'    => 'containerSshPort',
			'ssh_user'    => 'containerSshUsername',
			'ssh_pass'    => 'containerSshPassword',
			'ip'          => 'containerHostIpAddress',
			'deploy_time' => 'deployedAt',
			'uri'         => 'uri',
		];

		$data = [];

		foreach ( $map_data as $k => $item ) {
			if ( isset( $data_container[ $item ] ) ) {
				$data[ $k ] = $data_container[ $item ];
			}
		}

		return $data;
	}

	/**
	 * @return array
	 */
	private function compat_meta_store_data_map() {
		return [
			'ssh_port'    => 'wpd_container_port',
			'ssh_user'    => 'wpd_container_user',
			'ssh_pass'    => 'wpd_container_password',
			'ip'          => 'wpd_container_ip',
			'deploy_time' => 'wpd_container_deploy_time',
			'uri'         => 'wpd_container_uri',
		];
	}
}
