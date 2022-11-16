<?php

namespace Dollie\Core\Hooks;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Services\RecurringActionService;
use Dollie\Core\Singleton;

/**
 * Class RecurringActions
 *
 * @package Dollie\Core\Hooks
 */
class RecurringActions extends Singleton {
	/**
	 * Container constructor.
	 */
	public function __construct() {
		parent::__construct();

		$recurring_action_service = RecurringActionService::instance();

		add_action( 'wp_ajax_dollie_create_recurring_action', [ $recurring_action_service, 'create_recurring_action' ] );
		add_action( 'wp_ajax_dollie_delete_recurring_action', [ $recurring_action_service, 'remove_recurring_action' ] );
		add_action( 'wp_ajax_dollie_delete_recurring_container', [ $recurring_action_service, 'remove_recurring_container' ] );
		add_action( 'wp_ajax_dollie_get_selected_sites', [ $recurring_action_service, 'get_selected_sites' ] );
		add_action( 'wp_ajax_dollie_get_schedule_history', [ $recurring_action_service, 'get_schedule_history' ] );
	}
}
