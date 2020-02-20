<?php

/**
 * The Dollie Dashboard Plugin
 *
 * @package Dollie Dashboard
 * @subpackage Main
 */
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WeFoster' ) ) :
/**
 * The main plugin class
 *
 * @since 1.0.0
 */
final class WeFoster {

	/**
	 * Setup and return the singleton pattern
	 *
	 * @since 1.0.0
	 *
	 * @uses WeFoster::setup_globals()
	 * @uses WeFoster::setup_actions()
	 * @return The single WeFoster
	 */
	public static function instance() {

		// Store instance locally
		static $instance = null;

		if ( null === $instance ) {
			$instance = new WeFoster;
			$instance->setup_globals();
			$instance->includes();
			$instance->setup_actions();
		}

		return $instance;
	}

	/**
	 * Prevent the plugin class from being loaded more than once
	 */
	private function __construct() { /* Nothing to do */ }

	/** Private methods *************************************************/

	/**
	 * Setup default class globals
	 *
	 * @since 1.0.0
	 */
	private function setup_globals() {

		/** Versions **********************************************************/

		$this->version      = '1.0.0';

		/** Paths *************************************************************/

		// Setup some base path and URL information
		$this->file         = __FILE__;
		$this->basename     = plugin_basename( $this->file );
		$this->plugin_dir   = plugin_dir_path( $this->file );
		$this->plugin_url   = plugin_dir_url ( $this->file );

		// Includes
		$this->includes_dir = trailingslashit( $this->plugin_dir . 'includes' );
		$this->includes_url = trailingslashit( $this->plugin_url . 'includes' );

		// Languages
		$this->lang_dir     = trailingslashit( $this->plugin_dir . 'languages' );

		/** Misc **************************************************************/

		$this->extend       = new stdClass();
		$this->domain       = 'wefoster';
	}

	/**
	 * Include the required files
	 *
	 * @since 1.0.0
	 */
	private function includes() {
		/** Core **********************************************************/
		require( $this->includes_dir . 'pages.php'     );
		/** Hooks *********************************************************/
		require( $this->includes_dir . 'actions.php' );
	}
	/**
	 * Setup default actions and filters
	 *
	 * @since 1.0.0
	 *
	 * @uses do_action() Calls 'Dollie_DBloaded'
	 */
	private function setup_actions() {

		// And... we're live!
		do_action( 'Dollie_DBloaded' );
	}
}

/**
 * Return single instance of this main plugin class
 *
 * @since 1.0.0
 *
 * @return WeFoster
 */
function wefoster() {
	return WeFoster::instance();
}

// Initiate
wefoster();

endif; // class_exists
