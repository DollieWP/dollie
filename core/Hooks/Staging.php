<?php

namespace Dollie\Core\Hooks;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Services\StagingService;
use Dollie\Core\Singleton;

/**
 * Class Container
 *
 * @package Dollie\Core\Hooks
 */
class Staging extends Singleton {
	/**
	 * Container constructor.
	 */
	public function __construct() {
		parent::__construct();

		$staging_service = StagingService::instance();

		add_filter( 'dollie/log/actions', [ $staging_service, 'log_action_filter' ], 10, 2 );

		add_action( 'template_redirect', [ $staging_service, 'create' ] );
		add_action( 'template_redirect', [ $staging_service, 'delete' ] );
		add_action( 'template_redirect', [ $staging_service, 'sync' ] );

		add_action( 'template_redirect', [ $staging_service, 'check_deploy' ] );
		add_action( 'template_redirect', [ $staging_service, 'check_sync' ] );
	}
}
