<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;
use Dollie\Core\Log;
use Dollie\Core\Modules\Sites\WP;

/**
 * Class Container
 *
 * @package Dollie\Core\Modules
 */
class Staging extends Singleton {

	const LOG_DEPLOY_STARTED = 'wp-staging-deploy-start';
	const LOG_DEPLOYED       = 'wp-staging-deployed';
	const LOG_DEPLOY_FAILED  = 'wp-staging-deploy-failed';
	const LOG_UNDEPLOY       = 'wp-staging-undeploy';

	const OPTION_DATA = '_wpd_staging_data';
	const OPTION_URL  = '_wpd_staging_url';

	/**
	 * Container constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_filter( 'dollie/log/actions', [ $this, 'log_action_filter' ], 10, 2 );

		add_action( 'template_redirect', [ $this, 'create' ] );
		add_action( 'template_redirect', [ $this, 'undeploy' ] );
		add_action( 'template_redirect', [ $this, 'sync' ] );

		add_action( 'template_redirect', [ $this, 'update_deploy' ] );
		add_action( 'template_redirect', [ $this, 'update_sync' ] );
	}

	/**
	 * Log actions
	 *
	 * @param array $actions
	 * @param array $values
	 * @return array
	 */
	public function log_action_filter( $actions, $values ) {
		$actions[ self::LOG_DEPLOY_STARTED ] = [
			'title'   => __( 'Staging Site Launch Started', 'dollie' ),
			'content' => __( sprintf( 'Launching Your Staging Site %s. You\'ll get another notification when it is ready! ', $values[0] ), 'dollie' ),
			'type'    => 'deploy',
			'link'    => true,
		];

		$actions[ self::LOG_DEPLOYED ] = [
			'title'   => __( 'Staging Site Launch Completed', 'dollie' ),
			'content' => __( sprintf( 'Staging Site %s has been successfully launched.', $values[0] ), 'dollie' ),
			'type'    => 'deploy',
			'link'    => true,
		];

		$actions[ self::LOG_DEPLOY_FAILED ] = [
			'title'   => __( 'Staging Site Launch Failed', 'dollie' ),
			'content' => __( sprintf( 'Staging Site %s has failed to launch. Please contact our support if the issue persists.', $values[0] ), 'dollie' ),
			'type'    => 'deploy',
			'link'    => false,
		];

		$actions[ self::LOG_UNDEPLOY ] = [
			'title'   => __( 'Staging Site Has Been Removed', 'dollie' ),
			'content' => __( sprintf( 'Staging Site %s has been successfully removed.', $values[0] ), 'dollie' ),
			'type'    => 'deploy',
			'link'    => false,
		];

		return $actions;
	}

	/**
	 * Create staging
	 *
	 * @return void
	 */
	public function create() {
		if ( ! isset( $_POST['create_staging'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'wpd_staging_create' ) ) {
			return;
		}

		$staging_enabled = get_field( 'wpd_enable_staging', 'options' );

		if ( ! $staging_enabled ) {
			return;
		}

		if ( dollie()->staging_sites_limit_reached() ) {
			return;
		}

		$deploy_job_uuid = Container::instance()->get_staging_deploy_job( get_the_ID() );

		if ( $deploy_job_uuid ) {
			return;
		}

		$container_id  = get_the_ID();
		$deploy_status = 'pending';

		$post_body = [
			'source'  => dollie()->get_wp_site_data('uri', $container_id),
			'envVars' => [
				'S5_DEPLOYMENT_URL' => get_site_url(),
			],
		];

		// Send the API request.
		$request_container_deploy  = Api::post( Api::ROUTE_CONTAINER_STAGING_DEPLOY, $post_body );
		$response_container_deploy = Api::process_response( $request_container_deploy );

		if ( is_array( $response_container_deploy ) && ! $response_container_deploy['job'] ) {
			Log::add_front(
				self::LOG_DEPLOY_FAILED,
				dollie()->get_current_object( $container_id ),
				$response_container_deploy['route'],
				print_r( $request_container_deploy, true )
			);

			$deploy_status = 'failed';
		} elseif ( ! is_array( $response_container_deploy ) ) {
			Log::add_front(
				self::LOG_DEPLOY_FAILED,
				dollie()->get_current_object( $container_id ),
				'',
				print_r( $request_container_deploy, true )
			);

			$deploy_status = 'failed';
		}

		$domain = $response_container_deploy['route'];

		$staging_data = get_post_meta( $container_id, self::OPTION_DATA, true );

		if ( empty( $staging_data ) ) {
			$staging_data = [];
		}

		$staging_data[ $domain ] = [
			'status' => $deploy_status,
		];

		if ( 'failed' !== $deploy_status ) {
			Log::add_front(
				self::LOG_DEPLOY_STARTED,
				dollie()->get_current_object( $container_id ),
				$domain
			);

			Container::instance()->set_staging_deploy_job( $container_id, $response_container_deploy['job'] );
			update_post_meta( $container_id, self::OPTION_URL, $domain );
			update_post_meta( $container_id, self::OPTION_DATA, $staging_data );
		}

		wp_redirect( dollie()->get_site_url( get_the_ID(), 'staging' ) );
		die();
	}

	/**
	 * Sync staging
	 *
	 * @return void
	 */
	public function sync() {
		if ( ! isset( $_POST['sync_staging'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'wpd_staging_sync' ) ) {
			return;
		}

		$container_id = get_the_ID();
		$domain       = dollie()->get_container_url( $container_id );
		$staging_data = get_post_meta( $container_id, self::OPTION_DATA, true );

		if ( ! $domain || ! is_array( $staging_data ) || ! isset( $staging_data[ $domain ] ) ) {
			return;
		}

		$post_body = [
			'source' => $domain,
			'target' => str_replace( 'https://', '', dollie()->get_container_url( $container_id ) ),
		];

		$request_container_sync  = Api::post( Api::ROUTE_CONTAINER_STAGING_SYNC, $post_body );
		$response_container_sync = Api::process_response( $request_container_sync );

		if ( $response_container_sync ) {
			Api::save_execution( $container_id, $response_container_sync );
		}

		wp_redirect( dollie()->get_site_url( get_the_ID(), 'staging' ) );
		die();
	}

	/**
	 * Undeploy staging
	 *
	 * @return void
	 */
	public function undeploy() {
		if ( ! isset( $_POST['undeploy_staging'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'wpd_staging_undeploy' ) ) {
			return;
		}

		$container_id = get_the_ID();
		$staging_url  = get_post_meta( $container_id, '_wpd_staging_url', true );
		$staging_data = get_post_meta( $container_id, self::OPTION_DATA, true );
		$domain       = get_post_meta( $container_id, self::OPTION_URL, true );

		if ( ! $staging_url || ! $staging_data ) {
			return;
		}

		$request_container_undeploy  = Api::post(
			Api::ROUTE_CONTAINER_STAGING_UNDEPLOY,
			[
				'container_id' => $staging_data[ $staging_url ]['data']['id'],
			]
		);
		$response_container_undeploy = Api::process_response( $request_container_undeploy );

		if ( 200 === $response_container_undeploy['status'] ) {
			delete_post_meta( $container_id, self::OPTION_URL );
			delete_post_meta( $container_id, self::OPTION_DATA );

			Log::add_front(
				self::LOG_UNDEPLOY,
				dollie()->get_current_object( $container_id ),
				$domain
			);
		}

		wp_redirect( dollie()->get_site_url( get_the_ID(), 'staging' ) );
		die();
	}

	/**
	 * Update deploy
	 *
	 * @return void
	 */
	public function update_deploy() {

		if ( ! is_singular( 'container' ) ) {
			return;
		}

		$deploy_job_uuid = Container::instance()->get_staging_deploy_job( get_the_ID() );

		if ( ! $deploy_job_uuid ) {
			return;
		}

		$data   = WP::instance()->process_deploy_status( $deploy_job_uuid );
		$site   = dollie()->get_current_object();
		$domain = dollie()->get_container_url( $site->id );

		if ( false === $data ) {
			return;
		}

		if ( is_wp_error( $data ) ) {
			Log::add_front(
				self::LOG_DEPLOY_FAILED,
				dollie()->get_current_object( $site->id ),
				$domain
			);

			delete_post_meta( $site->id, self::OPTION_URL );
			delete_post_meta( $site->id, self::OPTION_DATA );
			Container::instance()->remove_staging_deploy_job( $site->id );

			return;
		}

		Log::add_front(
			self::LOG_DEPLOYED,
			dollie()->get_current_object( $site->id ),
			$domain
		);

		$staging_data = get_post_meta( $site->id, self::OPTION_DATA, true );

		$staging_data[ $domain ]['status'] = 'live';
		$staging_data[ $domain ]['data']   = $data['data']['deployment'];
		update_post_meta( $site->id, self::OPTION_DATA, $staging_data );

		Container::instance()->remove_staging_deploy_job( $site->id );
	}

	/**
	 * Update sync
	 *
	 * @return void
	 */
	public function update_sync() {

		if ( ! is_singular( 'container' ) ) {
			return;
		}

		$execution = dollie()->get_execution( get_the_ID(), Api::EXECUTION_STAGING_SYNC );

		if ( ! $execution ) {
			return;
		}

		if ( 0 === $execution['status'] ) {
			$data = dollie()->get_execution_status( $execution['id'], Api::EXECUTION_STAGING_SYNC );

			if ( is_wp_error( $data ) ) {
				dollie()->remove_execution( get_the_ID(), Api::EXECUTION_STAGING_SYNC );

				return;
			}

			dollie()->save_execution( get_the_ID(), $data );
		}
	}

}
