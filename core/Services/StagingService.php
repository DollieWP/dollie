<?php

namespace Dollie\Core\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;

final class StagingService extends Singleton {
	const LOG_DEPLOY_STARTED = 'wp-staging-deploy-start';
	const LOG_DEPLOYED       = 'wp-staging-deployed';
	const LOG_DEPLOY_FAILED  = 'wp-staging-deploy-failed';
	const LOG_UNDEPLOY       = 'wp-staging-undeploy';

	const OPTION_DATA = '_wpd_staging_data';
	const OPTION_URL  = '_wpd_staging_url';

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

		// deploy service

		$container = dollie()->get_container();

		wp_redirect( $container->get_permalink( 'staging' ) );
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

		$container = dollie()->get_container();

		if ( is_wp_error( $container ) || ! $container->is_staging() ) {
			return;
		}

		$container->sync();

		wp_redirect( $container->get_permalink( 'staging' ) );
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

		$container = dollie()->get_container();

		if ( is_wp_error( $container ) || ! $container->is_staging() ) {
			return;
		}

		$container->undeploy();

		wp_redirect( $container->get_permalink( 'staging' ) );
		die();
	}

	/**
	 * Update deploy
	 *
	 * @return void
	 */
	public function check_deploy() {
		if ( ! is_singular( 'container' ) ) {
			return;
		}

		$deploy_job_uuid = Container::instance()->get_staging_deploy_job( get_the_ID() );

		if ( ! $deploy_job_uuid ) {
			return;
		}

		// check for staging deploy
	}

	/**
	 * Update sync
	 *
	 * @return void
	 */
	public function check_sync() {
		if ( ! is_singular( 'container' ) ) {
			return;
		}

		// check sync status
	}
}
