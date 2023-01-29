<?php

namespace Dollie\Core\Hooks;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Services\BulkActionService;
use Dollie\Core\Singleton;

/**
 * Class BulkActions
 *
 * @package Dollie\Core\Hooks
 */
class BulkActions extends Singleton {
	/**
	 * Container constructor.
	 */
	public function __construct() {
		parent::__construct();

		$bulk_action_service = BulkActionService::instance();

		add_filter( 'dollie/log/actions', [ $bulk_action_service, 'log_action_filter' ], 10, 3 );
		add_filter( 'dollie/log/actions/content', [ $bulk_action_service, 'log_action_content_filter' ], 10, 3 );

		add_action( 'wp_ajax_dollie_do_bulk_action', [ $bulk_action_service, 'execute_bulk_action' ] );
		add_action( 'wp_ajax_dollie_check_bulk_action', [ $bulk_action_service, 'check_bulk_action' ] );
		add_action( 'wp_ajax_dollie_get_bulk_action_data', [ $bulk_action_service, 'get_action_data' ] );
	}
}
