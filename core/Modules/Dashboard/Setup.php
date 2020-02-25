<?php

namespace Dollie\Core\Modules\Dashboard;

use Dollie\Core\Singleton;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/**
 * The main plugin class
 *
 * @since 1.0.0
 */
final class Setup extends Singleton {

	/**
	 * @var string
	 */
	public $version;
	/**
	 * @var string
	 */
	public $plugin_dir;
	/**
	 * @var string
	 */
	public $plugin_url;

	public function __construct() {
		parent::__construct();

		$this->setup_globals();
		$this->dependencies();
		$this->setup_actions();

	}

	/**
	 * Setup default class globals
	 *
	 * @since 1.0.0
	 */
	private function setup_globals() {

		$this->version    = '1.2.0';
		$this->plugin_dir = DOLLIE_PATH . '/core/Modules/Dashboard';
		$this->plugin_url = trailingslashit( DOLLIE_URL . '/core/Modules/Dashboard' );
	}

	/**
	 * Add dependencies
	 *
	 * @since 1.0.0
	 */
	private function dependencies() {

		Pages::instance();
	}

	/**
	 * Setup default actions and filters
	 *
	 */
	private function setup_actions() {

		add_action( 'init', [ $this, 'init' ] );

		do_action( 'dollie_dashboard_loaded' );
	}

	public function init() {
		do_action( 'dollie_dashboard_init' );
	}
}
