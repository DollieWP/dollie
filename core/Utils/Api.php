<?php

namespace Dollie\Core\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Log;
use Dollie\Core\Modules\Options;
use Dollie\Core\Singleton;

/**
 * Class Api
 *
 * @package Dollie\Core\Utils
 */
class Api extends Singleton {

	const
		ROUTE_CONTAINER_GET                     = 'containers',
		ROUTE_CONTAINER_DEPLOY                  = 'containers/deploy',
		ROUTE_CONTAINER_DEPLOY_GET              = 'containers/deploy/{uuid}',
		ROUTE_CONTAINER_TRIGGER                 = 'containers/trigger',
		ROUTE_CONTAINER_CHANGE_USER_ROLE        = 'containers/change-user-role',
		ROUTE_CONTAINER_SCREENSHOT              = 'containers/take-screenshot',
		ROUTE_CONTAINER_SCREENSHOT_REGEN        = 'containers/regenerate-screenshots',
		ROUTE_CONTAINER_BLUEPRINT_AVAILABILITY  = 'containers/blueprint-availability',
		ROUTE_CONTAINER_STAGING_SET_STATUS      = 'containers/staging/status',
		ROUTE_CONTAINER_STAGING_DEPLOY          = 'containers/staging/deploy',
		ROUTE_CONTAINER_STAGING_UNDEPLOY        = 'containers/staging/undeploy',
		ROUTE_CONTAINER_STAGING_SYNC            = 'containers/staging/sync',
		ROUTE_CONTAINER_BULK_ACTION             = 'containers/bulk-action',
		ROUTE_CONTAINER_BULK_ACTION_STATUS      = 'containers/bulk-action/status',
		ROUTE_CONTAINER_RECURRING_ACTION_GET    = 'containers/recurring-action',
		ROUTE_CONTAINER_RECURRING_ACTION_CREATE = 'containers/recurring-action/create',

		ROUTE_DOMAIN_ADD                 = 'domain/add',
		ROUTE_DOMAIN_UPDATE              = 'domain/update',
		ROUTE_DOMAIN_CHECK               = 'domain/check',
		ROUTE_DOMAIN_REMOVE              = 'domain/remove',
		ROUTE_DOMAIN_ROUTES_ADD          = 'domain/routes/add',
		ROUTE_DOMAIN_ROUTES_GET          = 'domain/routes/get',
		ROUTE_DOMAIN_ROUTES_DELETE       = 'domain/routes/delete',
		ROUTE_DOMAIN_INSTALL_LETSENCRYPT = 'domain/install/letsencrypt',
		ROUTE_DOMAIN_INSTALL_CLOUDFLARE  = 'domain/install/cloudflare',

		ROUTE_BACKUP_GET     = 'backup',
		ROUTE_BACKUP_CREATE  = 'backup/create',
		ROUTE_BACKUP_RESTORE = 'backup/restore',

		ROUTE_BLUEPRINT_GET                  = 'blueprint',
		ROUTE_BLUEPRINT_CREATE_OR_UPDATE     = 'blueprint/create-or-update',
		ROUTE_BLUEPRINT_CHECK_DYNAMIC_FIELDS = 'blueprint/check-dynamic-fields',

		ROUTE_PLUGINS_UPDATES_GET   = 'plugins/updates/get',
		ROUTE_PLUGINS_UPDATES_APPLY = 'plugins/updates/apply',

		ROUTE_CHECK_SUBSCRIPTION    = 'partner/check-subscription',
		ROUTE_ADD_CUSTOM_BACKUP     = 'partner/add-custom-backup',
		ROUTE_DISABLE_CUSTOM_BACKUP = 'partner/disable-custom-backup',

		ROUTE_WIZARD_SETUP  = 'setup',
		ROUTE_GET_EXECUTION = 'execution/status';

	const
		EXECUTION_STAGING_SYNC            = 'staging.sync.to.live',
		EXECUTION_BACKUP_CREATE           = 'backup.create',
		EXECUTION_BACKUP_APPLY            = 'backup.apply',
		EXECUTION_BACKUP_RESTORE          = 'backup.restore',
		EXECUTION_BACKUP_CREDENTIALS      = 'backup.credentials.change',
		EXECUTION_BLUEPRINT_CREATE        = 'blueprint.create',
		EXECUTION_BLUEPRINT_DEPLOY        = 'blueprint.deploy',
		EXECUTION_BLUEPRINT_AFTER_DEPLOY  = 'blueprint.after.deploy',
		EXECUTION_CHANGE_USER_ROLE        = 'change.user.role',
		EXECUTION_DYNAMIC_FIELDS_CHECK    = 'dynamic.fields.check',
		EXECUTION_DYNAMIC_FIELDS_REPLACE  = 'dynamic.fields.replace',
		EXECUTION_DOMAIN_UPDATE           = 'domain.update',
		EXECUTION_DOMAIN_APPLY_CLOUDFLARE = 'domain.apply.cloudflare',
		EXECUTION_PLUGIN_GET_UPDATES      = 'plugins.get.updates',
		EXECUTION_PLUGIN_APPLY_UPDATES    = 'plugins.apply.updates',
		EXECUTION_WIZARD_SETUP            = 'wizard.setup';

	// const API_BASE_URL = 'https://api-staging.getdollie.com/';
	// const API_URL      = self::API_BASE_URL . 'api/';
	// const PARTNERS_URL = 'https://partners.getdollie.com/';

	const API_BASE_URL = 'http://dollie-api.lcl/';
	const API_URL      = self::API_BASE_URL . 'api/';
	const PARTNERS_URL = 'http://dollie-wp.lcl/';

	public static $last_call = null;

	/**
	 * Api constructor
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'wp_ajax_dollie_check_execution', [ $this, 'check_execution' ] );
	}

	/**
	 * Check execution status by AJAX
	 */
	public function check_execution() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'dollie_check_execution' ) ) {
			wp_send_json_error();
		}

		$execution = self::get_execution( $_REQUEST['container'], $_REQUEST['type'] );

		if ( ! $execution ) {
			wp_send_json_success();
		}

		if ( 0 === $execution['status'] ) {
			$data = dollie()->get_execution_status( $execution['id'], $_REQUEST['type'] );

			if ( is_wp_error( $data ) ) {
				dollie()->remove_execution( get_the_ID(), $_REQUEST['type'] );

				wp_send_json_success();
			}

			dollie()->save_execution( $_REQUEST['container'], $data );

			if ( 0 !== $data['status'] ) {
				wp_send_json_success();
			}
		}

		wp_send_json_error();
	}

	/**
	 * @param $r
	 *
	 * @return array
	 */
	public static function set_custom_http_args( $r = [] ) {
		$r['timeout'] = 25;

		return $r;
	}

	/**
	 * Make a GET request to dollie API
	 *
	 * @param $endpoint
	 *
	 * @return array|\WP_Error
	 */
	public static function get( $endpoint ) {
		add_filter( 'http_request_args', [ __CLASS__, 'set_custom_http_args' ] );

		do_action( 'dollie/api/' . $endpoint . '/before', 'get' );

		$call = self::simple_get( $endpoint );

		remove_filter( 'http_request_args', [ __CLASS__, 'set_custom_http_args' ] );

		$call = apply_filters( 'dollie/api/after/get', $call, $endpoint );

		do_action( 'dollie/api/' . $endpoint . '/after', 'get', $call );

		return $call;
	}

	/**
	 * @param $endpoint
	 *
	 * @return array|\WP_Error
	 */
	public static function simple_get( $endpoint ) {
		self::$last_call = $endpoint;

		return wp_remote_get(
			self::API_URL . $endpoint,
			[
				'headers' => [
					'Accept'        => 'application/json',
					'Authorization' => self::get_auth_token(),
				],
			]
		);
	}

	/**
	 * Make a POST request to dollie API
	 *
	 * @param $endpoint
	 * @param array    $data
	 *
	 * @return array|\WP_Error
	 */
	public static function post( $endpoint, $data = [] ) {
		add_filter( 'http_request_args', [ __CLASS__, 'set_custom_http_args' ] );

		do_action( 'dollie/api/' . $endpoint . '/before', 'post', $data );

		$call = self::simple_post( $endpoint, $data );

		remove_filter( 'http_request_args', [ __CLASS__, 'set_custom_http_args' ] );

		$call = apply_filters( 'dollie/api/after/post', $call, $endpoint, $data );

		do_action( 'dollie/api/' . $endpoint . '/after', 'post', $call, $data );

		return $call;
	}

	/**
	 * @param $endpoint
	 * @param array    $data
	 *
	 * @return array|\WP_Error
	 */
	public static function simple_post( $endpoint, $data = [] ) {
		self::$last_call = $endpoint;

		return wp_remote_post(
			self::API_URL . $endpoint,
			[
				'method'  => 'POST',
				'body'    => $data,
				'headers' => [
					'Accept'        => 'application/json',
					'Authorization' => self::get_auth_token(),
				],
			]
		);
	}

	/**
	 * @param $response
	 *
	 * @param string   $return
	 *
	 * @return bool|mixed
	 */
	public static function process_response( $response, $return = 'body' ) {

		if ( is_wp_error( $response ) ) {
			Log::add( 'Api error on ' . self::$last_call, $response->get_error_message() );

			return false;
		}

		if ( wp_remote_retrieve_response_code( $response ) === 500 ) {
			Log::add( 'Api 500 error on ' . self::$last_call, print_r( $response, true ) );

			return false;
		}

		$answer_body = wp_remote_retrieve_body( $response );

		if ( empty( $answer_body ) ) {
			Log::add( 'Api error on ' . self::$last_call, 'Response has empty body.' );

			return false;
		}

		$answer = json_decode( $answer_body, true );

		if ( ! is_array( $answer ) || ! isset( $answer['status'] ) || 500 === $answer['status'] ) {
			Log::add( 'Api error on ' . self::$last_call, 'Invalid body data: ' . print_r( $answer, true ) );

			return false;
		}

		$answer = dollie()->maybe_decode_json( $answer, true, true );

		if ( ! empty( $return ) ) {

			if ( ! empty( $answer ) && isset( $answer[ $return ] ) ) {

				return dollie()->maybe_decode_json( $answer[ $return ], true );
			}

			return false;
		}

		return $answer;
	}

	/**
	 * Get execution status
	 *
	 * @param string $execution_id
	 * @param string $execution_type
	 *
	 * @return int|\WP_Error
	 */
	public static function get_execution_status( $execution_id, $execution_type = '' ) {
		$data = [
			'execution_id' => $execution_id,
		];

		if ( $execution_type ) {
			$data['execution_type'] = $execution_type;
		}

		$response = self::process_response( self::simple_post( self::ROUTE_GET_EXECUTION, $data ) );

		if ( $response ) {
			if ( ! $response['execution_id'] ) {
				return new \WP_Error( 'failed', __( 'Execution does not exist', 'dollie' ) );
			}

			return $response;
		}

		return new \WP_Error( 'failed', __( 'Failed to get execution status', 'dollie' ) );
	}

	/**
	 * Get execution
	 *
	 * @param int    $container_id
	 * @param string $execution_type
	 *
	 * @return null|string
	 */
	public static function get_execution( $container_id, $execution_type ) {
		return get_post_meta( $container_id, 'dollie.' . $execution_type, true );
	}

	/**
	 * Save execution
	 *
	 * @param int   $container_id
	 * @param array $execution
	 *
	 * @return void
	 */
	public static function save_execution( $container_id, $execution ) {
		update_post_meta(
			$container_id,
			'dollie.' . $execution['execution_type'],
			[
				'id'     => $execution['execution_id'],
				'status' => $execution['status'],
			]
		);
	}

	/**
	 * Remove execution
	 *
	 * @param int    $container_id
	 * @param string $execution_type
	 *
	 * @return void
	 */
	public static function remove_execution( $container_id, $execution_type ) {
		delete_post_meta( $container_id, 'dollie.' . $execution_type );
	}

	/**
	 * Update auth token
	 *
	 * @param $token
	 */
	public static function update_auth_token( $token ) {
		update_option( 'dollie_auth_token', $token );
	}

	/**
	 * Delete auth token
	 */
	public static function delete_auth_token() {
		delete_option( 'dollie_auth_token' );
	}

	/**
	 * Get auth token
	 *
	 * @return mixed|void
	 */
	public static function get_auth_token() {
		return get_option( 'dollie_auth_token' );
	}

	/**
	 * Process token data
	 *
	 * @return void
	 */
	public function process_token() {
		if ( isset( $_GET['data'], $_GET['page'] ) && $_GET['data'] && Options::PANEL_SLUG === $_GET['page'] ) {
			$data = @base64_decode( $_GET['data'] );
			$data = @json_decode( $data, true );

			if ( is_array( $data ) && isset( $data['token'], $data['domain'] ) && $data['token'] && $data['domain'] ) {
				delete_transient( 'wpd_partner_subscription' );

				self::update_auth_token( $data['token'] );

				update_option( 'options_wpd_api_domain', sanitize_text_field( $data['domain'] ) );

				set_transient( 'wpd_just_connected', 1, MINUTE_IN_SECONDS );

				wp_redirect( admin_url( 'admin.php?page=' . Options::API_SLUG ) );
				die();
			}
		}
	}

}
