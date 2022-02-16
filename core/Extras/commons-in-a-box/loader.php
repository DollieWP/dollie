<?php
/*
Plugin Name: Commons In A Box
Plugin URI: http://commonsinabox.org
Description: A suite of community and collaboration tools for WordPress, designed especially for academic communities
Version: 1.3.2
Author: CUNY Academic Commons
Author URI: http://commons.gc.cuny.edu
Licence: GPLv3
Network: true
Core: >=4.9.8
Text Domain: commons-in-a-box
Domain Path: /languages
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class Commons_In_A_Box {
	/**
	 * Holds the single-running DOLLIE_SETUP object
	 *
	 * @var Commons_In_A_Box
	 */
	private static $instance = false;

	/**
	 * Package holder.
	 *
	 * @since 1.1.0
	 *
	 * @var object Extended class of {@link CBox_Package}
	 */
	private $package;

	/**
	 * Static bootstrapping init method
	 *
	 * @since 0.1
	 */
	public static function &init() {
		if ( ! self::$instance ) {
			self::$instance = new self();
			self::$instance->constants();
			self::$instance->setup_globals();
			self::$instance->includes();
			self::$instance->setup_actions();
			self::$instance->load_components();
		}

		return self::$instance;
	}

	/**
	 * Private constructor. Intentionally left empty.
	 *
	 * Instantiate this class by using {@link cbox()} or {@link Commons_In_A_Box::init()}.
	 *
	 * @since 0.1
	 */
	private function __construct() {}

	/**
	 * Sets up our constants
	 *
	 * @since 0.2
	 *
	 * @todo Figure out a reliable way to use plugin_dir_path()
	 */
	private function constants() {
		if ( ! defined( 'DOLLIE_SETUP_PLUGIN_DIR' ) )
			define( 'DOLLIE_SETUP_PLUGIN_DIR', trailingslashit( dirname( __FILE__ ) ) );

		if ( ! defined( 'DOLLIE_SETUP_LIB_DIR' ) )
			define( 'DOLLIE_SETUP_LIB_DIR',    trailingslashit( DOLLIE_SETUP_PLUGIN_DIR . 'lib' ) );

	}

	/**
	 * Sets up some class data
	 *
	 * @since 0.1
	 */
	private function setup_globals() {

		/** VERSION ***********************************************************/

		// DOLLIE_SETUP version
		$this->version       = '1.3.2';

		// UTC date of DOLLIE_SETUP version release
		$this->revision_date = '2022-01-20 17:00 UTC';

		/** FILESYSTEM ********************************************************/

		// the absolute directory DOLLIE_SETUP is running from
		$this->plugin_dir    = constant( 'DOLLIE_SETUP_PLUGIN_DIR' );

		// the URL to the DOLLIE_SETUP directory
		$this->plugin_url    = plugin_dir_url( __FILE__ );
	}

	/**
	 * Includes necessary files
	 *
	 * @since 0.1
	 */
	private function includes() {
		// pertinent functions used everywhere
		require( $this->plugin_dir . 'includes/functions.php' );
		require( $this->plugin_dir . 'includes/plugins.php' );

		// Admin.
		if ( dollie_setup_is_admin() ) {
			require( $this->plugin_dir . 'admin/admin-loader.php' );

		// frontend
		} else {
			require( $this->plugin_dir . 'includes/frontend.php' );
		}

		// Upgrades API - runs in admin area and on AJAX.
		if ( is_admin() || defined( 'WP_CLI' ) ) {
			// @todo maybe use autoloader.
			require( $this->plugin_dir . 'includes/upgrades/upgrade-item.php' );
			require( $this->plugin_dir . 'includes/upgrades/upgrade.php' );
			require( $this->plugin_dir . 'includes/upgrades/upgrade-registry.php' );
		}

		// WP-CLI integration
		if ( defined( 'WP_CLI' ) ) {
			$this->cli_autoloader();
		}
	}

	/**
	 * Setup actions.
	 *
	 * @since 1.0-beta4
	 */
	private function setup_actions() {
		// Package hooks.
		add_action( 'dollie_setup_admin_loaded',      array( $this, 'load_package' ), 11 );
		add_action( 'dollie_setup_frontend_includes', array( $this, 'load_package_frontend' ) );

		// Add actions to plugin activation and deactivation hooks
		add_action( 'activate_'   . plugin_basename( __FILE__ ), function() { do_action( 'dollie_setup_activation' ); } );
		add_action( 'deactivate_' . plugin_basename( __FILE__ ), function() { do_action( 'dollie_setup_deactivation' ); } );

		// localization
		add_action( 'init', array( $this, 'localization' ), 0 );

		/*
		 * CLI-specific actions.
		 *
		 * This could be improved...
		 */
		if ( defined( 'WP_CLI') ) {
			add_filter( 'upgrader_source_selection', 'dollie_setup_rename_github_folder', 1, 4 );
			add_action( 'dollie_setup_plugins_loaded', function() {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}, 91 );
			add_action( 'dollie_setup_plugins_loaded', array( 'Plugin_Dependencies', 'init' ), 91 );
		}

		// Upgrader routine.
		add_action( 'wp_loaded', function() {
			// Ensure we're in the admin area or WP CLI.
			if ( is_admin() || defined( 'WP_CLI') ) {
				// Ensure upgrader items are registered.
				$packages = dollie_setup_get_packages();
				$current  = dollie_setup_get_current_package_id();
				if ( isset( $packages[ $current ] ) && class_exists( $packages[ $current ] ) ) {
					call_user_func( array( $packages[ $current ], 'upgrader' ) );
				}
			}

			// AJAX handler.
			if ( wp_doing_ajax() && ! empty( $_POST['action'] ) &&
				( 0 === strpos( $_POST['action'], 'dollie_setup_' ) && false !== strpos( $_POST['action'], '_upgrade' ) )
			) {
				require DOLLIE_SETUP_PLUGIN_DIR . 'includes/upgrades/ajax-handler.php';
			}
		} );
	}

	/**
	 * Load up our components.
	 *
	 * @since 1.0-beta2
	 */
	private function load_components() {
		// admin area
		if ( dollie_setup_is_admin() ) {
			$this->admin    = new CBox_Admin;
			$this->plugins  = new CBox_Plugins;

		// frontend
		} else {
			$this->frontend = new CBox_Frontend;
		}

		// WP-CLI integration
		if ( defined( 'WP_CLI' ) ) {
			\WP_CLI::add_command( 'cbox',         '\DOLLIE_SETUP\CLI\Core' );
			\WP_CLI::add_command( 'cbox package', '\DOLLIE_SETUP\CLI\Package' );
			\WP_CLI::add_command( 'cbox update',  '\DOLLIE_SETUP\CLI\Update' );
			\WP_CLI::add_command( 'cbox upgrade', '\DOLLIE_SETUP\CLI\Upgrade' );
		}

		/**
		 * Hook to load components.
		 *
		 * @since 1.1.0
		 *
		 * @param Commons_In_A_Box $this
		 */
		do_action( 'dollie_setup_load_components', $this );
	}

	/** HOOKS *********************************************************/

	/**
	 * Loads the active package into DOLLIE_SETUP.
	 *
	 * @since 1.1.0
	 */
	public function load_package() {
		// Package autoloader.
		$this->package_autoloader();

		$current = dollie_setup_get_current_package_id();
		if ( empty( $current ) ) {
			return;
		}

		// Load our package.
		$packages = dollie_setup_get_packages();
		if ( isset( $packages[$current] ) && class_exists( $packages[$current] ) ) {
			$this->package = call_user_func( array( $packages[$current], 'init' ) );
		}
	}

	/**
	 * Loads package code for the frontend.
	 *
	 * @since 1.1.0
	 */
	public function load_package_frontend() {
		// Minimal package code needed for main site.
		if ( dollie_setup_is_main_site() ) {
			$this->package_autoloader();

		// Multisite: Load up all package code on sub-sites and if user is logged in.
		} elseif ( is_multisite() && dollie_setup_get_current_package_id() ) {
			$this->load_package();

			/**
			 * Load registered package plugin list.
			 *
			 * Need to run this on 'init' due to user login check.
			 */
			add_action( 'init', array( $this, 'init_package' ), 0 );
		}
	}

	/**
	 * Initialize package.
	 *
	 * @since 1.1.1
	 */
	public function init_package() {
		$plugins = get_site_option( 'active_sitewide_plugins' );
		$loader  = plugin_basename( __FILE__ );
		$is_network_active = isset( $plugins[$loader] );

		if ( $is_network_active && is_user_logged_in() ) {
			self::$instance->package_plugins = new CBox_Plugins;
		}
	}

	/**
	 * Custom textdomain loader.
	 *
	 * Checks WP_LANG_DIR for the .mo file first, then the plugin's language folder.
	 * Allows for a custom language file other than those packaged with the plugin.
	 *
	 * @since 1.0-beta4
	 *
	 * @return bool True on success, false on failure
	 */
	public function localization() {
		// If we're on not on the main site, bail.
		if ( ! dollie_setup_is_main_site() ) {
			return;
		}

		/** This filter is documented in /wp-includes/i10n.php */
		$locale = apply_filters( 'plugin_locale', get_locale(), 'commons-in-a-box' );
		$mofile = sprintf( '%1%s%2$s-%3$s.mo', trailingslashit( constant( 'WP_LANG_DIR' ) ) , 'commons-in-a-box', $locale );

		// This is for custom localizations located at /wp-content/languages/.
		$load = load_textdomain( 'commons-in-a-box', $mofile );

		// No custom file, so use regular textdomain loader.
		if ( ! $load ) {
			return load_plugin_textdomain( 'commons-in-box', false, basename( $this->plugin_dir ) . '/languages/' );
		}

		return $load;
	}

	/** HELPERS *******************************************************/

	/**
	 * WP-CLI autoloader.
	 *
	 * @since 1.1.0
	 */
	public function cli_autoloader() {
		spl_autoload_register( function( $class ) {
			$prefix = 'DOLLIE_SETUP\\CLI\\';
			$base_dir = __DIR__ . '/includes/CLI/';

			// Does the class use the namespace prefix?
			$len = strlen( $prefix );
			if ( strncmp( $prefix, $class, $len ) !== 0 ) {
				return;
			}

			// Get the relative class name.
			$relative_class = substr( $class, $len );

			// Swap directory separators and namespace to create filename.
			$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

			// If the file exists, require it.
			if ( file_exists( $file ) ) {
				require $file;
			}
		} );
	}

	/**
	 * Package autoloader.
	 *
	 * @since 1.1.0
	 */
	public function package_autoloader() {
		spl_autoload_register( array( $this, 'package_handle_autoload' ) );
	}

	/**
	 * Autoload handler.
	 *
	 * @since 1.1.1
	 *
	 * @param string $class Class name.
	 */
	public function package_handle_autoload( $class ) {
		$subdir = $relative_class = '';

		// Package prefix.
		$prefix = 'CBox_Package';
		if ( $class === $prefix ) {
			$relative_class = 'package';
		} elseif ( false !== strpos( $class, $prefix ) ) {
			$subdir = '/';
			$subdir .= $relative_class = strtolower( substr( $class, 13 ) );
		}

		// Plugins prefix.
		if ( false !== strpos( $class, 'CBox_Plugins_' ) ) {
			$subdir = '/' . strtolower( substr( $class, 13 ) );
			$relative_class = 'plugins';
		}

		// Settings prefix.
		if ( false !== strpos( $class, 'CBox_Settings_' ) ) {
			$subdir = '/' . strtolower( substr( $class, 14 ) );
			$relative_class = 'settings';
		}

		if ( '' === $relative_class ) {
			return;
		}

		$file = "{$this->plugin_dir}includes{$subdir}/{$relative_class}.php";

		if ( file_exists( $file ) ) {
			require $file;
		}
	}

	/**
	 * Get the plugin URL for DOLLIE_SETUP.
	 *
	 * @since 1.0-beta1
	 *
	 * @param str $path Path relative to the DOLLIE_SETUP plugin URL.
	 * @return str DOLLIE_SETUP plugin URL with optional path appended.
	 */
	public function plugin_url( $path = '' ) {
		if ( ! empty( $path ) && is_string( $path ) )
			return esc_url( cbox()->plugin_url . $path );
		else
			return cbox()->plugin_url;
	}

}

/**
 * The main function responsible for returning the Commons In A Box instance
 * to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $cbox = cbox(); ?>
 *
 * @return Commons_In_A_Box
 */
function cbox() {
	return Commons_In_A_Box::init();
}

// Vroom!
cbox();
