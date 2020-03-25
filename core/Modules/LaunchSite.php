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

		$action = did_action( 'wp_insert_post' );
		if ( 0 === $action ) {
			// Set the post ID so that we know the post was created successfully
			$my_post = [
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'post_author'    => get_current_user_id(),
				'post_name'      => $domain . '-failed',
				'post_title'     => $domain . ' [deploy failed]',
				'post_status'    => 'draft',
				'post_type'      => 'container',
			];
		}

		$post_id = wp_insert_post( $my_post );

		add_post_meta( $post_id, 'wpd_container_status', 'failed', true );

		$blueprint = isset( $_COOKIE['dollie_blueprint_id'] ) ? $_COOKIE['dollie_blueprint_id'] : rgar( $entry, '4' );

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
			'domain'          => $domain . DOLLIE_DOMAIN,
			'dollie_domain'   => DOLLIE_INSTALL,
			'dollie_token'    => Api::getDollieToken(),
			'containerMemory' => DOLLIE_MEMORY,
			'description'     => $email . ' | ' . get_site_url(),
			'envVars'         => array_merge( $env_vars_extras, $env_vars )
		];

		$requestContainerCreate = Api::post( Api::ROUTE_CONTAINER_CREATE, $post_body );

		$responseContainerCreate = json_decode( wp_remote_retrieve_body( $requestContainerCreate ), true );
		$container               = json_decode( $responseContainerCreate['body'], true );

		if ( $responseContainerCreate['status'] === 500 ) {
			Log::add( $domain . ' API error for ' . DOLLIE_INSTALL . ' (see log)', print_r( $data, true ), 'deploy' );

			if ( $field->id === '1' ) {
				$result['is_valid']        = false;
				$field->failed_validation  = true;
				$field->validation_message = 'Sorry, We could not launch this site. Please try again with a different URL. Keep having problems? Please get in touch with our support!';
			}

			return $result;
		}

		if ( ! array_key_exists( 'id', $container ) ) {
			if ( $field->id === '1' ) {
				$result['is_valid']        = false;
				$field->failed_validation  = true;
				$field->validation_message = 'Sorry, We could not launch this site. Please try again with a different URL. Keep having problems? Please get in touch with our support!';
			}
		} else {
			sleep( 5 );

			$requestTriggerContainer = Api::post( Api::ROUTE_CONTAINER_TRIGGER, [
				'container_id'  => $container['id'],
				'action'        => 'deploy',
				'dollie_domain' => DOLLIE_INSTALL,
				'dollie_token'  => Api::getDollieToken(),
			] );

			$responseTriggerContainer = json_decode( wp_remote_retrieve_body( $requestTriggerContainer ), true );

			Log::add( $domain . ' Creating Site Dollie (see log)', print_r( $responseTriggerContainer['body'], true ), 'deploy' );

			sleep( 20 );

			$status = false;

			while ( $status === false ) {
				sleep( 5 );
				if ( $this->test_site_deployment( 'https://' . $domain . DOLLIE_DOMAIN ) === 200 ) {
					$status = true;
				}
			}

			$requestGetContainer = Api::post( Api::ROUTE_CONTAINER_GET, [
				'container_id'  => $container['id'],
				'dollie_domain' => DOLLIE_INSTALL,
				'dollie_token'  => Api::getDollieToken(),
			] );

			Log::add( $domain . 'Deploying created site ', print_r( $requestGetContainer, true ), 'deploy' );

			sleep( 3 );

			$responseContainer = json_decode( wp_remote_retrieve_body( $requestGetContainer ), true );
			$dataContainer     = json_decode( $responseContainer['body'], true );

			// Show an error of S5 API has not completed the setup.
			if ( ! array_key_exists( 'id', $dataContainer ) ) {
				if ( $field->id === '1' ) {
					$result['is_valid']        = false;
					$field->failed_validation  = true;
					$field->validation_message = 'Sorry, It seems like there was an issue with launching your new site. Our support team has been notified';
				}
			} else {
				wp_update_post( [
					'ID'          => $post_id,
					'post_status' => 'publish',
					'post_name'   => $domain,
					'post_title'  => $domain,
				] );

				Log::add( 'New Site ' . $domain . ' has container ID of ' . $dataContainer['id'], '', 'deploy' );

				add_post_meta( $post_id, 'wpd_container_id', $dataContainer['id'], true );
				add_post_meta( $post_id, 'wpd_container_ssh', $dataContainer['containerSshPort'], true );
				add_post_meta( $post_id, 'wpd_container_user', $dataContainer['containerSshUsername'], true );
				add_post_meta( $post_id, 'wpd_container_port', $dataContainer['containerSshPort'], true );
				add_post_meta( $post_id, 'wpd_container_password', $dataContainer['containerSshPassword'], true );
				add_post_meta( $post_id, 'wpd_container_ip', $dataContainer['containerHostIpAddress'], true );
				add_post_meta( $post_id, 'wpd_container_deploy_time', $dataContainer['deployedAt'], true );
				add_post_meta( $post_id, 'wpd_container_uri', $dataContainer['uri'], true );
				update_post_meta( $post_id, 'wpd_container_status', 'start' );
				add_post_meta( $post_id, 'wpd_container_launched_by', $email, true );

				// Set Flag if Demo
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

					$blueprint_url     = get_post_meta( $blueprint, 'wpd_container_uri', true );
					$blueprint_install = str_replace( 'https://', '', $blueprint_url );

					Api::post( Api::ROUTE_BLUEPRINT_DEPLOY, [
						'container_url' => $domain,
						'blueprint_url' => $blueprint_install
					] );

					Log::add( $domain . ' will use blueprint' . $blueprint_install, '', 'deploy' );
					update_post_meta( $post_id, 'wpd_blueprint_deployment_complete', 'yes' );
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

	/**
	 * Welcome Wizard. Update WP site details.
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
				$partner_install = get_post_meta( $partner->ID, 'wpd_url', true );

				Api::post( Api::ROUTE_BLUEPRINT_DEPLOY_FOR_PARTNER, [
					'container_url' => get_post_meta( $container_id, 'wpd_container_uri', true ),
					'partner_url'   => $partner_install,
					'domain'        => $container_slug
				] );

				update_post_meta( $container_id, 'wpd_partner_blueprint_deployed', 'yes' );
				sleep( 5 );
			} else {

				if ( ! isset( $data ) || empty( $data ) ) {

					Log::add( $container_slug . ' has invalid site details data', json_encode( $data ), 'setup' );

					return new \WP_Error( 'dollie_invalid_data', esc_html__( 'Processed site data is invalid', 'dollie' ) );

				}

				Api::post( Api::ROUTE_WIZARD_SETUP, $data );
			}
		}

		dollie()->flush_container_details();

		update_post_meta( $container_id, 'wpd_setup_complete', 'yes' );

		Log::add( $container_slug . ' has completed the initial site setup', '', 'setup' );

		Backups::instance()->trigger_backup();

		do_action( 'dollie/launch_site/set_details/after', $container_id, $data );

		return true;

	}

}
