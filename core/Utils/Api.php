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
		ROUTE_DOMAIN_INSTALL_LETSENCRYPT = 'domain/install/letsencrypt',
		ROUTE_DOMAIN_INSTALL_CLOUDFLARE = 'domain/instlal/cloudflare',

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

		ROUTE_NODES_GET = 'nodes',
		ROUTE_WIZARD_SETUP = 'setup';

	/**
	 * Api constructor.
	 */
	public function __construct() {
		parent::__construct();
	}

	public static function get( $endpoint ) {
		// todo: add route to constant
		return wp_remote_get( 'http://127.0.0.1:8000/api/' . $endpoint );
	}

	public static function post( $endpoint, $data = [] ) {
		return wp_remote_post( 'http://127.0.0.1:8000/api/' . $endpoint, [
			'method' => 'POST',
			'body'   => json_encode( $data )
		] );
	}

	/**
	 * Post request Worker API
	 *
	 * @param string $endpoint
	 * @param array $data
	 * @param string $method
	 * @param null $timeout
	 *
	 * @return array|\WP_Error
	 */
	public static function postRequestWorker( $endpoint, $data = [], $method = 'POST', $timeout = null ) {
		$requestData = [
			'method'  => $method,
			'headers' => [
				'X-Rundeck-Auth-Token' => DOLLIE_WORKER_TOKEN,
				'Content-Type'         => 'application/json',
			],
			'body'    => json_encode( $data )
		];

		if ( $timeout !== null && is_numeric( $timeout ) ) {
			$requestData['timeout'] = abs( $timeout );
		}

		return wp_remote_post( DOLLIE_WORKER_URL . '/api/' . $endpoint, $requestData );
	}

}
