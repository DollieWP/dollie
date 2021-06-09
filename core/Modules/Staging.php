<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;
use Dollie\Core\Log;

/**
 * Class Container
 * @package Dollie\Core\Modules
 */
class Staging extends Singleton {

	const LOG_DEPLOY_STARTED = 'wp-staging-deploy-start';
	const LOG_DEPLOYED = 'wp-staging-deployed';
	const LOG_DEPLOY_FAILED = 'wp-staging-deploy-failed';

	const OPTION_DATA = '_wpd_staging_data';
	const OPTION_URL = '_wpd_staging_url';

	/**
	 * Container constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'template_redirect', [ $this, 'staging_change_action' ] );

		add_filter( 'dollie/log/actions', [ $this, 'log_action_filter' ], 10, 2 );

	}

	public function log_action_filter( $actions, $values ) {

		$actions[ self::LOG_DEPLOY_STARTED ] = [
			'title'   => __( 'Staging Site Launch Started', 'dollie' ),
			'content' => __( sprintf( 'Launching Your Staging Site %s. You\'ll get another notification when it is ready! ', $values[0] ), 'dollie' ),
			'type'    => 'deploy',
			'link'    => true,
		];

		$actions[ self::LOG_DEPLOYED ]      = [
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

		return $actions;

	}

	/**
	 * Action to enable/disable staging from front-end
	 */
	public function staging_change_action() {

		if ( isset( $_POST['staging_change'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'wpd_staging' ) ) {

			if ( ! in_array( $_POST['staging_change'], [ 0, 1 ], false ) ) {
				return;
			}
			$status  = (int) $_POST['staging_change'];
			$user_id = get_current_user_id();

			// deploy staging site
			if ( $status === 1 ) {

				// make sure we can't create staging if limit is reached
				if ( dollie()->staging_sites_limit_reached() ) {
					return;
				}

				$site          = dollie()->get_current_object();
				$rand          = rand( pow( 10, 2 ), pow( 10, 3 ) - 1 );
				$domain        = $site->slug . '-' . $rand . /*'.my-staging-site.xyz'*/
				                 DOLLIE_DOMAIN;
				$deploy_status = 'pending';

				$post_body = [
					'source'  => dollie()->get_container_url( get_the_ID() ),
					'route'   => $domain,
					'envVars' => [
						'S5_DEPLOYMENT_URL' => get_site_url(),
					]
				];

				// Send the API request.
				$request_container_deploy  = Api::post( Api::ROUTE_CONTAINER_STAGING_DEPLOY, $post_body );
				$response_container_deploy = Api::process_response( $request_container_deploy );

				if ( ! $response_container_deploy || ! $response_container_deploy['job'] ) {
					Log::add_front(
						self::LOG_DEPLOY_FAILED,
						dollie()->get_current_object( $site->id ),
						$domain,
						print_r( $request_container_deploy, true )
					);

					$deploy_status = 'failed';

				}

				// set active staging url.
				update_post_meta( $site->id, self::OPTION_URL, $domain );

				$staging_data = get_post_meta( $site->id, self::OPTION_DATA, true );
				if ( empty( $staging_data ) ) {
					$staging_data = [];
				}

				$staging_data[ $domain ] = [
					'status' => $deploy_status
				];

				if ( $deploy_status !== 'failed' ) {
					$staging_data[ $domain ]['deploy_job'] = $response_container_deploy['job'];

					Log::add_front(
						self::LOG_DEPLOY_STARTED,
						dollie()->get_current_object( $site->id ),
						$domain
					);
				}

				update_post_meta( $site->id, self::OPTION_DATA, $staging_data );

			}

		}
	}

}