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

	public static function get( $endpoint ) {
		return wp_remote_get( self::API_URL . $endpoint );
	}

	public static function post( $endpoint, $data = [] ) {
		return wp_remote_post( self::API_URL . $endpoint, [
			'method'  => 'POST',
			'body'    => $data,
			'headers' => [
				'Accept'       => 'application/json'
			]
		] );
	}

	public static function getDollieToken() {
		return base64_encode( DOLLIE_S5_USER . ':' . DOLLIE_S5_PASSWORD );
	}
}
