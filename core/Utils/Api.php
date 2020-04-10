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
		ROUTE_NODES_GET = 'nodes',
		ROUTE_WIZARD_SETUP = 'setup';

	const API_URL = 'https://api.getdollie.com/api/';

	/**
	 * Api constructor.
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Make a GET request to dollie API
	 *
	 * @param $endpoint
	 *
	 * @return array|\WP_Error
	 */
	public static function get( $endpoint ) {

		do_action( 'dollie/api/' . $endpoint . '/before', 'get' );

		$call = wp_remote_get( self::API_URL . $endpoint, [
			'headers' => [
				'Accept'        => 'application/json',
				'Authorization' => self::get_auth_token()
			]
		] );

		do_action( 'dollie/api/' . $endpoint . '/after', 'get' );

		return $call;

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

		do_action( 'dollie/api/' . $endpoint . '/before', 'post', $data );

		$call = wp_remote_post( self::API_URL . $endpoint, [
			'method'  => 'POST',
			'body'    => $data,
			'headers' => [
				'Accept'        => 'application/json',
				'Authorization' => self::get_auth_token()
			]
		] );

		do_action( 'dollie/api/' . $endpoint . '/after', 'post', $data );

		return $call;
	}

	public static function getDollieToken() {
		return base64_encode( DOLLIE_S5_USER . ':' . DOLLIE_S5_PASSWORD );
	}

	public static function process_response( $response ) {

		if ( is_wp_error( $response ) ) {
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

	public static function get_auth_token() {
		return get_option( 'dollie_auth_token' );
	}

	public static function get_auth_refresh_token() {
		return get_option( 'dollie_auth_refresh_token' );
	}

	public static function get_auth_token_status() {
		return get_option( 'dollie_auth_token_status', '0' );
	}

	public static function update_auth_tokens( $data ) {
		if ( isset( $data['access_token'] ) ) {
			update_option( 'dollie_auth_token', $data['access_token'] );
		}
		if ( isset( $data['id_token'] ) ) {
			update_option( 'dollie_auth_id_token', $data['id_token'] );
		}

		if ( isset( $data['refresh_token'] ) ) {
			update_option( 'dollie_auth_refresh_token', $data['refresh_token'] );
		}

		if ( isset( $data['scope'] ) ) {
			update_option( 'dollie_auth_scope', $data['scope'] );
		}

		if ( isset( $data['token_type'] ) ) {
			update_option( 'dollie_auth_token_type', $data['token_type'] );
		}

		if ( isset( $data['expires_in'] ) ) {
			update_option( 'dollie_auth_expire', $data['expires_in'] );
		}

		update_option( 'dollie_auth_token_status', '1' );
	}

}
