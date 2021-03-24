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
		ROUTE_CONTAINER_GET                    = 'containers',
		ROUTE_CONTAINER_DEPLOY                 = 'containers/deploy',
		ROUTE_CONTAINER_DEPLOY_GET             = 'containers/deploy/{uuid}',
		ROUTE_CONTAINER_TRIGGER                = 'containers/trigger',
		ROUTE_CONTAINER_CHANGE_USER_ROLE       = 'containers/change-user-role',
		ROUTE_CONTAINER_SCREENSHOT             = 'containers/take-screenshot',
		ROUTE_CONTAINER_SCREENSHOT_REGEN       = 'containers/regenerate-screenshots',
		ROUTE_CONTAINER_BLUEPRINT_AVAILABILITY = 'containers/blueprint-availability',

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

		ROUTE_BLUEPRINT_GET              = 'blueprint',
		ROUTE_BLUEPRINT_CREATE_OR_UPDATE = 'blueprint/create-or-update',

		ROUTE_PLUGINS_UPDATES_GET   = 'plugins/updates/get',
		ROUTE_PLUGINS_UPDATES_APPLY = 'plugins/updates/apply',

		ROUTE_WIZARD_SETUP       = 'setup',
		ROUTE_CHECK_SUBSCRIPTION = 'partner/check-subscription',
		ROUTE_ADD_CUSTOM_BACKUP  = 'partner/add-custom-backup';

	const API_BASE_URL = 'https://api-staging.getdollie.com/';
	const API_URL      = self::API_BASE_URL . 'api/';
	const PARTNERS_URL = 'https://partners.getdollie.com/';

	public static $last_call = null;

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
