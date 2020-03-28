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

/**
 * Class LaunchSite
 * @package Dollie\Core\Modules\Forms
 */
class LaunchSite extends Singleton {

	private $form_key = 'form_dollie_launch_site';

	/**
	 * LaunchSite constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'acf/init', [ $this, 'acf_init' ] );

	}

	public function acf_init() {

		// Placeholders/Change values
		add_filter( 'acf/prepare_field/name=site_blueprint', [ $this, 'populate_blueprints' ] );
		add_filter( 'acf/prepare_field/name=site_url', [ $this, 'append_site_url' ] );

		// Form args
		add_filter( 'af/form/after_fields/key=' . $this->form_key, [ $this, 'add_modal_data' ], 10, 2 );
		add_filter( 'af/form/args/key=' . $this->form_key, [ $this, 'change_form_args' ] );

		// Form submission action.
		add_action( 'af/form/before_submission/key=' . $this->form_key, [ $this, 'submission_callback' ], 10, 3 );

	}

	public function submission_callback( $form, $fields, $args ) {
		$domain    = af_get_field( 'site_url' );
		$email     = af_get_field( 'site_admin_email' );
		$blueprint = isset( $_COOKIE['dollie_blueprint_id'] ) ? $_COOKIE['dollie_blueprint_id'] : af_get_field( 'site_blueprint' );
		$demo      = esc_url_raw( get_site_url() );

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

				$container_uri     = get_post_meta( $blueprint, 'wpd_container_uri', true );
				$blueprint_install = str_replace( 'https://', '', $container_uri );

				Api::post( Api::ROUTE_BLUEPRINT_DEPLOY, [
					'container_url' => $dataContainer['uri'],
					'blueprint_url' => $blueprint_install
				] );

				Log::add( $domain . ' will use blueprint' . $blueprint_install, '', 'deploy' );
				update_post_meta( $post_id, 'wpd_blueprint_deployment_complete', 'yes' );
			}
		}

	}

	public function test_site_deployment( $url ) {
		$response = wp_remote_get( $url );

		return wp_remote_retrieve_response_code( $response ) === 200;
	}

	public function append_site_url( $field ) {
		$field['append'] = DOLLIE_DOMAIN;

		return $field;
	}

	public function add_modal_data() {
		Tpl::load( DOLLIE_MODULE_TPL_PATH . 'launch-splash', [], true );
	}

	public function change_form_args( $args ) {
		$args['redirect']    = add_query_arg( 'site', 'new', $args['redirect'] );
		$args['submit_text'] = esc_html__( 'Launch New Site', 'dollie' );

		return $args;
	}

	public function populate_blueprints( $field ) {
		$query = new \WP_Query( [
			'post_type'      => 'container',
			'posts_per_page' => 1000,
			'meta_query'     => [
				'relation' => 'AND',
				[
					'key'   => 'wpd_blueprint_created',
					'value' => 'yes',
				],
				[
					'key'   => 'wpd_is_blueprint',
					'value' => 'yes',
				],
				[
					'key'     => 'wpd_installation_blueprint_title',
					'compare' => 'EXISTS',
				]
			],
			'p'              => isset( $_COOKIE['dollie_blueprint_id'] ) ? $_COOKIE['dollie_blueprint_id'] : '',
		] );

		$choices = [];

		// Build field options array.
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$private = get_field( 'wpd_private_blueprint' );

				if ( $private === 'yes' && ! current_user_can( 'manage_options' ) ) {
					continue;
				}

				if ( get_field( 'wpd_blueprint_image' ) === 'custom' ) {
					$image = get_field( 'wpd_blueprint_custom_image' );
				} elseif ( get_field( 'wpd_blueprint_image' ) === 'theme' ) {
					$image = wpthumb( get_post_meta( get_the_ID(), 'wpd_installation_site_theme_screenshot', true ), 'width=900&crop=0' );
				} else {
					$image = get_post_meta( get_the_ID(), 'wpd_site_screenshot', true );
				}

				$choices[ get_the_ID() ] = '<img data-toggle="tooltip" data-placement="bottom" ' .
				                           'title="' . get_post_meta( get_the_ID(), 'wpd_installation_blueprint_description', true ) . '" ' .
				                           'class="fw-blueprint-screenshot" src=' . $image . '>' .
				                           get_post_meta( get_the_ID(), 'wpd_installation_blueprint_title', true );

			}

			$field['choices'] = $choices;

		}

		// Hide the blueprints field
		if ( isset( $_COOKIE['dollie_blueprint_id'] ) || empty( $choices ) ) {
			$field['class'] = 'acf-hidden';
		}

		// return the field
		return $field;

	}

}
