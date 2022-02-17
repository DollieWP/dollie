<?php
/**
 * DOLLIE_SETUP Frontend.
 *
 * @since 1.0-beta2
 *
 * @package Dollie_Setup
 * @subpackage Frontend
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Things DOLLIE_SETUP does on the frontend of the site.
 *
 * @since 1.0-beta2
 */
class Dollie_Setup_Frontend {

	/**
	 * Constructor.
	 */
	public function __construct() {
		/**
		 * Hook to include various files on the frontend.
		 *
		 * @since 1.1.0
		 */
		do_action( 'dollie_setup_frontend_includes' );

		// setup globals
		$this->setup_globals();

		// if no settings exist, stop now!
		if ( empty( $this->settings ) ) {
			return;
		}

		// setup our DOLLIE_SETUP plugins object
		// this will hold some plugin-specific references
		dollie_setup()->plugins = new stdClass();

		add_action( 'plugins_loaded', array( $this, 'setup' ), 100 );
	}

	/**
	 * Include the plugin mods and set up any necessary hooks
	 *
	 * Hooked to plugins_loaded to ensure that plugins have had a chance
	 * to fully initialize
	 *
	 * @since 1.0.5
	 */
	public function setup() {
		// setup includes
		$this->includes();

		// setup our hooks
		$this->setup_hooks();
	}

	/**
	 * Setup globals.
	 *
	 * @since 1.0.1
	 */
	private function setup_globals() {
		// get our admin settings; this is highly specific to the Agency package...
		$settings_key   = dollie_setup_get_package_prop( 'settings_key' );
		$this->settings = ! empty( $settings_key ) ? (array) get_blog_option( dollie_setup_get_main_site_id(), $settings_key ) : array();

		// setup autoload classes
		$this->setup_autoload();

		// merge admin settings with autoloaded ones
		$this->settings = array_merge_recursive( $this->settings, $this->autoload );
	}

	/**
	 * Setup autoload classes.
	 *
	 * @since 1.0.1
	 */
	private function setup_autoload() {
		// setup internal autoload variable
		// will hold plugins and classes that need to be autoloaded by DOLLIE_SETUP
		$this->autoload = array();

		// WordPress
		$this->autoload['wp']   = array();
		$this->autoload['wp'][] = 'Dollie_Setup_WP_Toolbar_Updates';

		// bbPress
		$this->autoload['bbpress']   = array();
		$this->autoload['bbpress'][] = 'Dollie_Setup_BBP_Autoload';

		// Group Email Subscription
		$this->autoload['ges']   = array();
		$this->autoload['ges'][] = 'Dollie_Setup_GES_All_Mail';

		// Custom Profile Filters for BuddyPress
		$this->autoload['cpf']   = array();
		$this->autoload['cpf'][] = 'Dollie_Setup_CPF_Rehook_Social_Fields';
	}

	/**
	 * Includes.
	 *
	 * We conditionally load up specific PHP files depending if a setting was
	 * saved under the DOLLIE_SETUP admin settings page.
	 */
	private function includes() {
		// get plugins from DOLLIE_SETUP settings
		$plugins = array_keys( $this->settings );

		foreach ( $plugins as $plugin ) {
			if ( file_exists( dollie_setup()->plugin_dir . "includes/frontend-{$plugin}.php" ) ) {
				require dollie_setup()->plugin_dir . "includes/frontend-{$plugin}.php";
			}
		}
	}

	/**
	 * Setup our hooks.
	 *
	 * We conditionally add our hooks depending if a setting was saved under the
	 * DOLLIE_SETUP admin settings page or if it is explicitly autoloaded by DOLLIE_SETUP.
	 */
	private function setup_hooks() {

		foreach ( $this->settings as $plugin => $classes ) {
			// if our plugin is not setup, stop loading hooks now!
			$is_setup = isset( dollie_setup()->plugins->$plugin->is_setup ) ? dollie_setup()->plugins->$plugin->is_setup : false;

			if ( ! $is_setup ) {
				continue;
			}

			// sanity check
			$classes = array_unique( $classes );

			// load our classes
			foreach ( $classes as $class ) {
				// sanity check!
				// make sure our hook is available
				if ( ! is_callable( array( $class, 'init' ) ) ) {
					continue;
				}

				// load our hook
				// @todo this hook might need to be configured at the settings level
				call_user_func( array( $class, 'init' ) );
			}
		}
	}

}
