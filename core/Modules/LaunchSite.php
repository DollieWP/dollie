<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;
use Dollie\Core\Utils\Tpl;
use Dollie\Core\Log;
use GFFormsModel;

/**
 * Class LaunchSite
 * @package Dollie\Core\Modules
 */
class LaunchSite extends Singleton {


	/**
	 * LaunchSite constructor.
	 */
	public function __construct() {
		parent::__construct();

		$launch_forms = dollie()->get_dollie_gravity_form_ids( 'dollie-launch' );
		foreach ( $launch_forms as $form_id ) {
			add_action( 'gform_field_validation_' . $form_id, [ $this, 'add_new_site' ], 10, 4 );
		}

		add_action( 'wp_footer', [ $this, 'launch_splash' ] );
		add_filter( 'gform_field_value_siteurl', [ $this, 'populate_site_url' ] );
		add_action( 'template_redirect', [ $this, 'redirect_to_container_launch' ] );
	}

	public function add_new_site( $result, $value, $form, $field ) {
		$entry = GFFormsModel::get_current_lead();

		$domain = rgar( $entry, '1' );
		$email  = rgar( $entry, '2' );
		$demo   = rgar( $entry, '3' );

		$blueprint = isset( $_COOKIE['dollie_blueprint_id'] ) ? $_COOKIE['dollie_blueprint_id'] : rgar( $entry, '4' );

		$post_body = [
			'domain'          => $domain . DOLLIE_DOMAIN,
			'package'         => DOLLIE_PACKAGE,
			'containerMemory' => DOLLIE_MEMORY,
			'username'        => 'sideadmin',
			'password'        => '1234567890',
			'description'     => $email . ' | ' . get_site_url(),
			'envVars'         => [
				'S5_DEPLOYMENT_URL'          => get_site_url(),
				'S5_EMAIL_DELIVERY_USERNAME' => get_option( 'options_wpd_delivery_username' ),
				'S5_EMAIL_DELIVERY_PORT'     => get_option( 'options_wpd_delivery_smtp' ),
				'S5_EMAIL_DELIVERY_HOST'     => get_option( 'options_wpd_delivery_smtp_host' ),
				'S5_EMAIL_DELIVERY_EMAIL'    => get_option( 'options_wpd_delivery_email' ),
				'S5_EMAIL_DELIVERY_PASSWORD' => get_option( 'options_wpd_delivery_password' )
			]
		];

		$answer = Api::postRequestDollie( '', $post_body, 45 );

		if ( is_wp_error( $answer ) ) {
			Log::add( $domain . ' API error for ' . DOLLIE_INSTALL . ' (see log)', print_r( $answer, true ), 'deploy' );

//			if ( $field->id === '1' ) {
//				$result['is_valid'] = false;
//				$field->failed_validation      = true;
//				$field->validation_message     = 'Sorry, We could not launch this site. Please try again with a different URL. Keep having problems? Please get in touch with our support!';
//			}
		}

		Log::add( $domain . ' API request made to Dollie install ' . DOLLIE_INSTALL . ' (see log)', print_r( $answer, true ), 'deploy' );

		$response = json_decode( wp_remote_retrieve_body( $answer ), true );

		// Show an error of S5 API can't add the Route.
		if ( ! array_key_exists( 'id', $response ) ) {
//			if ( $field->id === '1' ) {
//				$result['is_valid'] = false;
//				$field->failed_validation      = true;
//				$field->validation_message     = 'Sorry, We could not launch this site. Please try again with a different URL. Keep having problems? Please get in touch with our support!';
//			}
		} else {
			sleep( 5 );

			$deploy = Api::postRequestDollie( $response['id'] . '/deploy', [], 120 );

			//Log::add( $domain . ' Creating Site Dollie (see log)' . $post_slug, print_r( $deploy, true ), 'deploy' );

			sleep( 20 );

			$status = false;

			while ( $status === false ) {
				sleep( 5 );
				if ( $this->test_site_deployment( 'https://' . $domain . DOLLIE_DOMAIN ) === 200 ) {
					$status = true;
				}
			}

			$update_container = Api::getRequestDollie( $response['id'] . '/', 120 );

			//Log::add( $domain . 'Deploying created site ' . $post_slug, print_r( $update_container, true ), 'deploy' );

			sleep( 3 );

			$update_response = json_decode( wp_remote_retrieve_body( $update_container ), true );

			// Show an error of S5 API has not completed the setup.
			if ( ! array_key_exists( 'id', $update_response ) ) {
//				if ( $field->id === '1' ) {
//					$result['is_valid'] = false;
//					$field->failed_validation      = true;
//					$field->validation_message     = 'Sorry, It seems like there was an issue with launching your new site. Our support team has been notified';
//				}
			} else {
				// Set the post ID so that we know the post was created successfully
				$my_post = [
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
					'post_author'    => get_current_user_id(),
					'post_name'      => $domain,
					'post_title'     => $domain,
					'post_status'    => 'publish',
					'post_type'      => 'container',
				];

				$post_id = wp_insert_post( $my_post );

				Log::add( 'New Site ' . $domain . ' has container ID of ' . $update_response['id'], '', 'deploy' );

				add_post_meta( $post_id, 'wpd_container_id', $update_response['id'], true );
				add_post_meta( $post_id, 'wpd_container_ssh', $update_response['containerSshPort'], true );
				add_post_meta( $post_id, 'wpd_container_user', $update_response['containerSshUsername'], true );
				add_post_meta( $post_id, 'wpd_container_port', $update_response['containerSshPort'], true );
				add_post_meta( $post_id, 'wpd_container_password', $update_response['containerSshPassword'], true );
				add_post_meta( $post_id, 'wpd_container_ip', $update_response['containerHostIpAddress'], true );
				add_post_meta( $post_id, 'wpd_container_deploy_time', $update_response['deployedAt'], true );
				add_post_meta( $post_id, 'wpd_container_uri', $update_response['uri'], true );
				add_post_meta( $post_id, 'wpd_container_status', 'start', true );
				add_post_meta( $post_id, 'wpd_container_launched_by', $email, true );

				//Set Flag if Demo
				if ( $demo === 'yes' ) {
					add_post_meta( $post_id, 'wpd_container_is_demo', 'yes', true );
				}

				sleep( 3 );

				//Register Node via Rundeck
				ContainerRegistration::instance()->register_worker_node($post_id);

				//Set Flag if Blueprint
				if ( $blueprint ) {
					sleep( 2 );
					add_post_meta( $post_id, 'wpd_container_based_on_blueprint', 'yes', true );
					add_post_meta( $post_id, 'wpd_container_based_on_blueprint_id', $blueprint, true );

					$blueprint_url     = get_post_meta( $blueprint, 'wpd_container_uri', true );
					$blueprint_install = str_replace( 'https://', '', $blueprint_url );

					$blueprint_body = [
						'filter'    => 'name: https://' . $domain . DOLLIE_DOMAIN . '-' . DOLLIE_RUNDECK_KEY,
						'argString' => '-url ' . $blueprint_install . ' -domain ' . $domain . DOLLIE_DOMAIN
					];

					//Set up the request
					Api::postRequestWorker( '1/job/a1a56354-a08e-4e7c-9dc5-bb72bb571dbe/run/', $blueprint_body );

					Log::add( $domain . ' will use blueprint' . $blueprint_install, '', 'deploy' );
					update_post_meta( $post_id, 'wpd_blueprint_deployment_complete', 'yes' );
				}

				if ( $demo === 'yes' && is_page( 'get-started' ) && is_plugin_active('get-dollie-extension/dollie.php') ) {
					Log::add( $domain . ' starts partner deploy', '', 'deploy' );
					wpd_apply_partner_template( $post_id, $domain, rgar( $entry, '6' ), rgar( $entry, '8' ), rgar( $entry, '9' ) );
				}

				$result['is_valid'] = true;
			}
		}

		return $result;
	}

	public function launch_splash() {
		if ( is_page( dollie()->get_launch_page_id() ) ) {
			Tpl::load( DOLLIE_MODULE_TPL_PATH . 'launch-splash', [], true );
		}
	}

	public function populate_site_url( $form ) {
		// Grab URL from HTTP Server Var and put it into a variable
		// Return that value to the form
		return esc_url_raw( get_site_url() );
	}

	public function test_site_deployment( $url ) {
		$response = wp_remote_get( $url );

		return wp_remote_retrieve_response_code( $response );
	}

	public function redirect_to_container_launch() {
		if ( current_user_can( 'manage_options' ) && dollie()->count_total_containers() === 0 && ! is_page( dollie()->get_launch_page_id() ) ) {
			wp_redirect( dollie()->get_launch_page_url() );
			exit;
		}
	}

}
