<?php

namespace Dollie\Core\Modules\Sites;

use Dollie\Core\Log;
use Dollie\Core\Modules\Backups;
use Dollie\Core\Modules\ContainerRegistration;
use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Backups
 * @package Dollie\Core\Modules
 */
final class WP extends Singleton {


	public function deploy_site( $email, $domain, $user_id, $blueprint ) {

		$error_message = esc_html__( 'Sorry, We could not launch this site. Please try again with a different URL. Keep having problems? Please get in touch with our support!', 'dollie' );

		// Set the post ID so that we know the post was created successfully.
		$post_id = wp_insert_post( [
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'post_author'    => $user_id,
			'post_name'      => $domain . '-failed',
			'post_title'     => $domain . ' [deploy failed]',
			'post_status'    => 'draft',
			'post_type'      => 'container',
		] );

		add_post_meta( $post_id, 'wpd_container_status', 'failed', true );

		$env_vars = [
			'S5_DEPLOYMENT_URL'          => get_site_url(),
			'S5_EMAIL_DELIVERY_USERNAME' => get_option( 'options_wpd_delivery_username' ),
			'S5_EMAIL_DELIVERY_PORT'     => get_option( 'options_wpd_delivery_smtp' ),
			'S5_EMAIL_DELIVERY_HOST'     => get_option( 'options_wpd_delivery_smtp_host' ),
			'S5_EMAIL_DELIVERY_EMAIL'    => get_option( 'options_wpd_delivery_email' ),
			'S5_EMAIL_DELIVERY_PASSWORD' => get_option( 'options_wpd_delivery_password' )
		];

		$env_vars_extras = apply_filters( 'dollie/launch_site/extras_envvars', [], $domain, get_current_user_id(), $email, $blueprint );

		$post_body = [
			'domain'        => $domain . DOLLIE_DOMAIN,
			'memory'        => DOLLIE_MEMORY,
			'description'   => $email . ' | ' . get_site_url(),
			'envVars'       => array_merge( $env_vars_extras, $env_vars ),
			'dollie_domain' => DOLLIE_INSTALL,
			'dollie_token'  => Api::get_dollie_token(),
		];

		$requestContainerCreate = Api::post( Api::ROUTE_CONTAINER_CREATE, $post_body );

		if ( is_wp_error( $requestContainerCreate ) ) {
			Log::add( $domain . ' API error for ' . DOLLIE_INSTALL . ' (see log)', $requestContainerCreate->get_error_message(), 'deploy' );

			return new \WP_Error( 'dollie_launch', $error_message );
		}

		$responseContainerCreate = json_decode( wp_remote_retrieve_body( $requestContainerCreate ), true );
		$container               = json_decode( $responseContainerCreate['body'], true );

		if ( $responseContainerCreate['status'] === 500 ) {
			Log::add( $domain . ' API error for ' . DOLLIE_INSTALL . ' (see log)', print_r( $requestContainerCreate, true ), 'deploy' );

			return new \WP_Error( 'dollie_launch', $error_message );
		}

		if ( ! array_key_exists( 'id', $container ) ) {
			Log::add( $domain . ' API error for ' . DOLLIE_INSTALL . ' (see log)', print_r( $requestContainerCreate, true ), 'deploy' );

			return new \WP_Error( 'dollie_launch', $error_message );
		}

		sleep( 5 );

		$requestTriggerContainer = Api::post( Api::ROUTE_CONTAINER_TRIGGER, [
			'container_id'  => $container['id'],
			'action'        => 'deploy',
			'dollie_domain' => DOLLIE_INSTALL,
			'dollie_token'  => Api::get_dollie_token(),
		] );

		$responseTriggerContainer = json_decode( wp_remote_retrieve_body( $requestTriggerContainer ), true );

		Log::add( $domain . ' Creating Site Dollie (see log)', print_r( $responseTriggerContainer['body'], true ), 'deploy' );

		sleep( 10 );

		$status = false;

		while ( $status === false ) {
			sleep( 5 );
			if ( $this->test_site_deployment( 'https://' . $domain . DOLLIE_DOMAIN ) ) {
				$status = true;
			}
		}

		$requestGetContainer = Api::post( Api::ROUTE_CONTAINER_GET, [
			'container_id'  => $container['id'],
			'dollie_domain' => DOLLIE_INSTALL,
			'dollie_token'  => Api::get_dollie_token(),
		] );

		Log::add( $domain . 'Deploying created site ', print_r( $requestGetContainer, true ), 'deploy' );

		sleep( 3 );

		$responseContainer = json_decode( wp_remote_retrieve_body( $requestGetContainer ), true );
		$data_container     = json_decode( $responseContainer['body'], true );

		// Show an error of S5 API has not completed the setup.
		if ( ! array_key_exists( 'id', $data_container ) ) {

			return new \WP_Error( 'dollie_launch', esc_html__( 'Sorry, It seems like there was an issue with launching your new site. Our support team has been notified', 'dollie' ) );
		}

		// Update the post with the final data
		wp_update_post( [
			'ID'          => $post_id,
			'post_status' => 'publish',
			'post_name'   => $domain,
			'post_title'  => $domain,
		] );

		Log::add( 'New Site ' . $domain . ' has container ID of ' . $data_container['id'], '', 'deploy' );

		add_post_meta( $post_id, 'wpd_container_id', $data_container['id'], true );
		add_post_meta( $post_id, 'wpd_container_ssh', $data_container['containerSshPort'], true );
		add_post_meta( $post_id, 'wpd_container_user', $data_container['containerSshUsername'], true );
		add_post_meta( $post_id, 'wpd_container_port', $data_container['containerSshPort'], true );
		add_post_meta( $post_id, 'wpd_container_password', $data_container['containerSshPassword'], true );
		add_post_meta( $post_id, 'wpd_container_ip', $data_container['containerHostIpAddress'], true );
		add_post_meta( $post_id, 'wpd_container_deploy_time', $data_container['deployedAt'], true );
		add_post_meta( $post_id, 'wpd_container_uri', $data_container['uri'], true );
		update_post_meta( $post_id, 'wpd_container_status', 'start' );
		add_post_meta( $post_id, 'wpd_container_launched_by', $email, true );

		// Set Flag if Demo
		$demo = esc_url_raw( get_site_url() );
		if ( $demo === 'yes' ) {
			add_post_meta( $post_id, 'wpd_container_is_demo', 'yes', true );
		}

		sleep( 3 );

		// Register Node via Worker
		ContainerRegistration::instance()->register_worker_node( $post_id );

		// Set Flag if Blueprint
		if ( $blueprint ) {
			sleep( 2 );
			add_post_meta( $post_id, 'wpd_container_based_on_blueprint', 'yes', true );
			add_post_meta( $post_id, 'wpd_container_based_on_blueprint_id', $blueprint, true );

			$container_uri     = get_post_meta( $blueprint, 'wpd_container_uri', true );
			$blueprint_install = str_replace( 'https://', '', $container_uri );

			Api::post( Api::ROUTE_BLUEPRINT_DEPLOY, [
				'container_url' => $data_container['uri'],
				'blueprint_url' => $blueprint_install
			] );

			Log::add( $domain . ' will use blueprint' . $blueprint_install, '', 'deploy' );
			update_post_meta( $post_id, 'wpd_blueprint_deployment_complete', 'yes' );
		}

		return [
			'post_id' => $post_id,
			'data_container' => $data_container
		];
	}

	/**
	 * Update WP site details.
	 *
	 * @param int|null $container_id
	 * @param array|null $data
	 *
	 * @return bool|\WP_Error
	 */
	public function update_site_details( $data = null, $container_id = null ) {

		if ( ! isset( $container_id ) ) {
			$current_query = dollie()->get_current_object();

			$container_id   = $current_query->id;
			$container_slug = $current_query->slug;
		} else {
			$container_slug = get_post( $container_id )->post_name;
		}

		do_action( 'dollie/launch_site/set_details/before', $container_id, $data );

		$demo = get_post_meta( $container_id, 'wpd_container_is_demo', true );

		if ( $demo !== 'yes' ) {
			$partner = get_user_by( 'login', get_post_meta( $container_id, 'wpd_partner_ref', true ) );

			$is_partner_lead = $partner_blueprint = $blueprint_deployed = '';

			if ( $partner instanceof \WP_User ) {
				$partner_blueprint  = get_post_meta( $partner->ID, 'wpd_partner_blueprint_created', true );
				$blueprint_deployed = get_post_meta( $container_id, 'wpd_blueprint_deployment_complete', true );
				$is_partner_lead    = get_post_meta( $container_id, 'wpd_is_partner_lead', true );
			}

			if ( $is_partner_lead === 'yes' && $partner_blueprint === 'yes' && $blueprint_deployed !== 'yes' ) {
				$partner_url = get_post_meta( $partner->ID, 'wpd_url', true );

				Api::post( Api::ROUTE_BLUEPRINT_DEPLOY_FOR_PARTNER, [
					'container_uri' => get_post_meta( $container_id, 'wpd_container_uri', true ),
					'partner_url'   => $partner_url,
					'domain'        => $container_slug
				] );

				update_post_meta( $container_id, 'wpd_partner_blueprint_deployed', 'yes' );
				sleep( 5 );
			} else {

				if ( ! isset( $data ) || empty( $data ) ) {

					Log::add( $container_slug . ' has invalid site details data', json_encode( $data ), 'setup' );

					return new \WP_Error( 'dollie_launch', esc_html__( 'Processed site data is invalid.', 'dollie' ) );

				}

				$setup_request = Api::post( Api::ROUTE_WIZARD_SETUP, $data );
			}
		}

		dollie()->flush_container_details();

		update_post_meta( $container_id, 'wpd_setup_complete', 'yes' );

		Log::add( $container_slug . ' has completed the initial site setup', '', 'setup' );

		Backups::instance()->trigger_backup();

		do_action( 'dollie/launch_site/set_details/after', $container_id, $data );

		return true;

	}


	private function test_site_deployment( $url ) {
		$response = wp_remote_get( $url );

		return wp_remote_retrieve_response_code( $response ) === 200;
	}

}