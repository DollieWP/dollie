<?php


namespace Dollie\Core\Modules\Forms;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Log;
use Dollie\Core\Modules\ContainerRegistration;
use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;
use Dollie\Core\Utils\Tpl;
use Nubs\RandomNameGenerator\Alliteration as NameGenerator;

/**
 * Class QuickLaunch
 * @package Dollie\Core\Modules\Forms
 */
class QuickLaunch extends Singleton {

	private $form_key = 'form_dollie_quick_launch';

	/**
	 * QuickLaunch constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'acf/init', [ $this, 'acf_init' ] );

	}

	public function acf_init() {

		// Form args
		add_filter( 'af/form/after_fields/key=' . $this->form_key, [ $this, 'add_modal_data' ], 10, 2 );
		add_filter( 'af/form/args/key=' . $this->form_key, [ $this, 'change_form_args' ] );

		add_filter( 'af/field/before_render', [ $this, 'modify_fields' ], 10, 3 );
		add_action( 'af/form/before_submission/key=' . $this->form_key, array( $this, 'submission_callback' ), 10, 3 );

	}

	public function submission_callback( $form, $fields, $args ) {

		$generator = new NameGenerator();
		$domain    = strtolower( str_replace( ' ', '-', $generator->getName() ) );

		$email     = af_get_field( 'client_email' );
		$blueprint = isset( $_COOKIE['dollie_blueprint_id'] ) ? $_COOKIE['dollie_blueprint_id'] : false;
		$demo      = esc_url_raw( get_site_url() );

		// If we allow registration and not logged in - create account
		if ( ! is_user_logged_in() && get_option( 'users_can_register' ) ) {

			$user_id       = username_exists( $email );
			$user_password = af_get_field( 'client_password' ) ?: wp_generate_password( $length = 12, $include_standard_special_chars = false );

			if ( ! $user_id && false === email_exists( $email ) ) {
				$user_id = wp_create_user( $email, $user_password, $email );
				update_user_meta( $user_id, 'first_name', af_get_field( 'client_name' ) );
			} else {
				af_add_error( 'client_email', esc_html__( 'Email already exists. Please login first', 'dollie' ) );
			}
		} else {
			$user_id = get_current_user_id();
		}

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
			'dollie_token'  => Api::getDollieToken(),
		];

		$requestContainerCreate = Api::post( Api::ROUTE_CONTAINER_CREATE, $post_body );

		if ( is_wp_error( $requestContainerCreate ) ) {
			Log::add( $domain . ' API error for ' . DOLLIE_INSTALL . ' (see log)', $requestContainerCreate->get_error_message(), 'deploy' );

			af_add_submission_error( $requestContainerCreate->get_error_message() );

			return;
		}

		$responseContainerCreate = json_decode( wp_remote_retrieve_body( $requestContainerCreate ), true );
		$container               = json_decode( $responseContainerCreate['body'], true );

		$error_message = esc_html__( 'Sorry, We could not launch this site. Please try again with a different URL. Keep having problems? Please get in touch with our support!', 'dollie' );

		if ( $responseContainerCreate['status'] === 500 ) {
			Log::add( $domain . ' API error for ' . DOLLIE_INSTALL . ' (see log)', print_r( $requestContainerCreate, true ), 'deploy' );

			af_add_submission_error( $error_message );

			return;
		}

		if ( ! array_key_exists( 'id', $container ) ) {
			Log::add( $domain . ' API error for ' . DOLLIE_INSTALL . ' (see log)', print_r( $requestContainerCreate, true ), 'deploy' );

			af_add_submission_error( $error_message );

			return;
		}

		sleep( 5 );

		$requestTriggerContainer = Api::post( Api::ROUTE_CONTAINER_TRIGGER, [
			'container_id'  => $container['id'],
			'action'        => 'deploy',
			'dollie_domain' => DOLLIE_INSTALL,
			'dollie_token'  => Api::getDollieToken(),
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
			'dollie_token'  => Api::getDollieToken(),
		] );

		Log::add( $domain . 'Deploying created site ', print_r( $requestGetContainer, true ), 'deploy' );

		sleep( 3 );

		$responseContainer = json_decode( wp_remote_retrieve_body( $requestGetContainer ), true );
		$dataContainer     = json_decode( $responseContainer['body'], true );

		// Show an error of S5 API has not completed the setup.
		if ( ! array_key_exists( 'id', $dataContainer ) ) {
			af_add_submission_error( esc_html__( 'Sorry, It seems like there was an issue with launching your new site. Our support team has been notified', 'dollie' ) );

			return;
		}

		// Update the post with the final data
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

			$container_uri     = get_post_meta( $blueprint, 'wpd_container_uri', true );
			$blueprint_install = str_replace( 'https://', '', $container_uri );

			Api::post( Api::ROUTE_BLUEPRINT_DEPLOY, [
				'container_url' => $dataContainer['uri'],
				'blueprint_url' => $blueprint_install
			] );

			Log::add( $domain . ' will use blueprint' . $blueprint_install, '', 'deploy' );
			update_post_meta( $post_id, 'wpd_blueprint_deployment_complete', 'yes' );
		}


		// add WP site details
		$password = wp_generate_password( 8, false );

		$data = [
			'container_uri' => $dataContainer['uri'],
			'email'         => af_get_field( 'client_email' ),
			'name'          => esc_html__( 'My WP', 'dollie' ),
			'description'   => esc_html__( 'My WordPress Install', 'dollie' ),
			'username'      => sanitize_title( af_get_field( 'client_name' ) ),
			'password'      => $password,
		];

		$status = AfterLaunchWizard::instance()->update_site_details( $data, $post_id );

		if ( is_wp_error( $status ) ) {
			af_add_submission_error( $status->get_error_message() );
		}

		//Save our container ID
		AF()->submission['extra']['dollie_container_id'] = $post_id;

	}

	public function test_site_deployment( $url ) {
		$response = wp_remote_get( $url );

		return wp_remote_retrieve_response_code( $response ) === 200;
	}


	public function add_modal_data() {
		Tpl::load( DOLLIE_MODULE_TPL_PATH . 'launch-splash', [], true );
	}

	public function change_form_args( $args ) {
		// $args['redirect']    = add_query_arg( 'site', 'new', $args['redirect'] );
		$args['submit_text'] = esc_html__( 'Launch New Site', 'dollie' );

		return $args;
	}


	public function modify_fields( $field, $form, $args ) {

		if ( $form['key'] !== $this->form_key ) {
			return $field;
		}

		if ( is_user_logged_in() ) {

			$user = wp_get_current_user();

			if ( $field['name'] === 'client_password' ) {
				$field['wrapper']['class'] = 'acf-hidden';
			}

			if ( $field['name'] === 'client_name' ) {
				$field['value']            = $user->display_name;
				$field['wrapper']['width'] = '50';
			}

			if ( $field['name'] === 'client_email' ) {
				$field['value'] = $user->user_email;
			}

		} elseif ( ! get_option( 'users_can_register' ) ) {

			if ( $field['name'] === 'client_name' ) {
				$field['wrapper']['width'] = '50';
			}

			if ( $field['name'] === 'client_password' ) {
				$field['wrapper']['class'] = 'acf-hidden';
			}

		}

		return $field;
	}


}
