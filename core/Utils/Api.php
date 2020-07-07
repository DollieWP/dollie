<?php

namespace Dollie\Core\Utils;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;

/**
 * Class Api
 * @package Dollie\Core\Utils
 */
class Api extends Singleton {

	const
		ROUTE_CONTAINER_GET = 'containers',
		ROUTE_CONTAINER_CREATE = 'containers/create',
		ROUTE_CONTAINER_TRIGGER = 'containers/trigger',

		ROUTE_DOMAIN_ROUTES_ADD = 'domain/routes/add',
		ROUTE_DOMAIN_ROUTES_GET = 'domain/routes/get',
		ROUTE_DOMAIN_ROUTES_DELETE = 'domain/routes/delete',
		ROUTE_DOMAIN_UPDATE = 'domain/update',
		ROUTE_DOMAIN_INSTALL_LETSENCRYPT = 'domain/install/letsencrypt',
		ROUTE_DOMAIN_INSTALL_CLOUDFLARE = 'domain/install/cloudflare',

		ROUTE_BACKUP_GET = 'backup',
		ROUTE_BACKUP_CREATE = 'backup/create',
		ROUTE_BACKUP_RESTORE = 'backup/restore',

		ROUTE_BLUEPRINT_GET = 'blueprint',
		ROUTE_BLUEPRINT_CREATE_OR_UPDATE = 'blueprint/create-or-update',
		ROUTE_BLUEPRINT_DEPLOY = 'blueprint/deploy',
		ROUTE_BLUEPRINT_DEPLOY_FOR_PARTNER = 'blueprint/deploy-partner',

		ROUTE_PLUGINS_INSTALL = 'plugins/install',
		ROUTE_PLUGINS_UPDATES_GET = 'plugins/updates/get',
		ROUTE_PLUGINS_UPDATES_APPLY = 'plugins/updates/apply',

		ROUTE_EXECUTE_JOB = 'execute/job',
		ROUTE_NODES_CREATE = 'nodes/create',
		ROUTE_NODES_GET = 'nodes',
		ROUTE_WIZARD_SETUP = 'setup',
		ROUTE_CHANGE_USER_ROLES = 'change-user-role';

	const API_URL = 'https://api.getdollie.com/api/';

	/**
	 * Api constructor.
	 */
	public function __construct() {
		parent::__construct();
	}

	public function set_custom_http_timeout() {
		return 10;
	}


	/**
	 * Make a GET request to dollie API
	 *
	 * @param $endpoint
	 *
	 * @return array|\WP_Error
	 */
	public static function get( $endpoint ) {

		add_filter( 'http_request_timeout', [ self::instance(), 'set_custom_http_timeout' ] );

		do_action( 'dollie/api/' . $endpoint . '/before', 'get' );

		$call = self::simple_get( $endpoint );

		remove_filter( 'http_request_timeout', [ self::instance(), 'set_custom_http_timeout' ] );

		$call = apply_filters( 'dollie/api/after/get', $call, $endpoint );

		do_action( 'dollie/api/' . $endpoint . '/after', 'get' );

		return $call;

	}

	/**
	 * @param $endpoint
	 *
	 * @return array|\WP_Error
	 */
	public static function simple_get( $endpoint ) {
		return wp_remote_get( self::API_URL . $endpoint, [
			'headers' => [
				'Accept'        => 'application/json',
				'Authorization' => self::get_auth_data( 'access_token' )
			]
		] );
	}

	/**
	 * Make a POST request to dollie API
	 *
	 * @param $endpoint
	 * @param array $data
	 *
	 * @return array|\WP_Error
	 */
	public static function post( $endpoint, $data = [] ) {

		add_filter( 'http_request_timeout', [ self::instance(), 'set_custom_http_timeout' ] );

		do_action( 'dollie/api/' . $endpoint . '/before', 'post', $data );

		$call = self::simple_post( $endpoint, $data );

		remove_filter( 'http_request_timeout', [ self::instance(), 'set_custom_http_timeout' ] );

		$call = apply_filters( 'dollie/api/after/post', $call, $endpoint, $data );

		do_action( 'dollie/api/' . $endpoint . '/after', 'post', $data );

		return $call;
	}

	/**
	 * @param $endpoint
	 * @param array $data
	 *
	 * @return array|\WP_Error
	 */
	public static function simple_post( $endpoint, $data = [] ) {
		return wp_remote_post( self::API_URL . $endpoint, [
			'method'  => 'POST',
			'body'    => $data,
			'headers' => [
				'Accept'        => 'application/json',
				'Authorization' => self::get_auth_data( 'access_token' )
			]
		] );
	}

	/**
	 * @return string
	 */
	public static function get_dollie_token() {
		return base64_encode( DOLLIE_S5_USER . ':' . DOLLIE_S5_PASSWORD );
	}

	/**
	 * @param $response
	 *
	 * @return bool|mixed
	 */
	public static function process_response( $response ) {
		if ( is_wp_error( $response ) ) {
			return false;
		}

		if ( wp_remote_retrieve_response_code( $response ) === 500 ) {
			return false;
		}

		$answer_body = wp_remote_retrieve_body( $response );

		if ( empty( $answer_body ) ) {
			return false;
		}

		$answer = json_decode( $answer_body, true );

		if ( ! is_array( $answer ) || ! isset( $answer['status'] ) || $answer['status'] === 500 ) {
			return false;
		}

		return @json_decode( $answer['body'], true );
	}

	/**
	 * @return bool
	 */
	public static function auth_token_is_active() {
		return (bool) get_option( 'dollie_auth_token_status', 0 );
	}

	/**
	 * @param $status
	 */
	public static function update_auth_token_status( $status ) {
		update_option( 'dollie_auth_token_status', $status );
	}

	/**
	 * @param $data
	 */
	public static function update_auth_data( $data ) {
		update_option( 'dollie_token_data', $data );
		self::update_auth_token_status( 1 );
	}

	/**
	 *
	 */
	public static function delete_auth_data() {
		delete_option( 'dollie_token_data' );
	}

	/**
	 * @param $type
	 *
	 * @return bool
	 */
	public static function get_auth_data( $type ) {
		$data = get_option( 'dollie_token_data', [] );

		if ( empty( $data ) ) {
			return false;
		}

		if ( isset( $data[ $type ] ) ) {
			return $data[ $type ];
		}

		return false;
	}

}
