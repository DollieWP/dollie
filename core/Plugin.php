<?php

namespace Dollie\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Modules\Integrations\Integrations;
use Dollie\Core\Modules\Vip\Hooks as VipHooks;
use Dollie\Core\Modules\AccessGroups\AccessGroups as AccessGroups;
use Dollie\Core\Modules\Logging;
use Dollie\Core\Modules\Forms;

use Dollie\Core\Hooks\PermissionControl;
use Dollie\Core\Hooks\Container;
use Dollie\Core\Hooks\Blueprints;
use Dollie\Core\Hooks\BulkActions;
use Dollie\Core\Hooks\RecurringActions;
use Dollie\Core\Hooks\Staging;
use Dollie\Core\Hooks\Domain;
use Dollie\Core\Hooks\Acf;

use Dollie\Core\Jobs\SyncContainersJob;
use Dollie\Core\Jobs\RemoveOldLogsJob;
use Dollie\Core\Jobs\ChangeContainerRoleJob;
use Dollie\Core\Jobs\CustomerAccessCheckJob;

use Dollie\Core\Routing\Processor;
use Dollie\Core\Routing\Route;
use Dollie\Core\Routing\Router;
use Dollie\Core\Services\HubDataService;
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

/**
 * Class Plugin
 *
 * @package Dollie\Core
 */
class Plugin extends Singleton {
	/**
	 * @var string
	 */
	public static $minimum_elementor_version = '3.0.0';

	/**
	 * Plugin constructor.
	 */
	public function __construct() {
		parent::__construct();

		Admin::instance();

		add_action( 'plugins_loaded', array( $this, 'load_early_dependencies' ), - 10 );
		add_action( 'plugins_loaded', array( $this, 'load_dependencies' ), 0 );
		add_action( 'plugins_loaded', array( $this, 'load_late_dependencies' ), 99 );


		add_action( 'plugins_loaded', array( $this, 'initialize' ) );
		add_action( 'acf/init', array( $this, 'acf_add_local_field_groups' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'load_resources' ), 12 );
		add_action( 'route_login_redirect', array( $this, 'load_login_route' ) );
		add_action( 'route_preview', array( $this, 'load_preview_route' ) );
		add_action( 'route_wizard', array( $this, 'load_wizard_route' ) );
		add_action( 'route_remote_data', array( HubDataService::instance(), 'load_route' ) );
	}

	/**
	 * Load early dependencies
	 */
	public function load_early_dependencies() {
		require_once DOLLIE_PATH . 'core/Extras/action-scheduler/action-scheduler.php';
	}

	public function load_late_dependencies() {
		if ( file_exists( DOLLIE_PATH . 'core/Extras/platform/platform/platform.php' )
		&& ! defined('PLATFORM_VERSION') ) {
			require_once DOLLIE_PATH . 'core/Extras/platform/platform/platform.php';
		}
	}

	/**
	 * Load dependencies
	 */
	public function load_dependencies() {

		// load ACF as fallback.
		if ( ! class_exists( 'ACF' ) ) {
			require_once DOLLIE_CORE_PATH . 'Extras/advanced-custom-fields-pro/acf.php';
		}

		require_once DOLLIE_CORE_PATH . 'Extras/acf-extended/acf-extended.php';

		// Load Color Customizer
		require_once DOLLIE_CORE_PATH . 'Extras/Colors.php';

		// Load Theme/Plugins Compatibility.
		require_once DOLLIE_CORE_PATH . 'Compatibility.php';

		// Load logger.
		if ( ! class_exists( '\WDS_Log_Post' ) ) {
			require_once DOLLIE_CORE_PATH . 'Extras/wds-log-post/wds-log-post.php';
		}

		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		$acf_plugin_path     = WP_PLUGIN_DIR . '/advanced-custom-fields/acf.php';
		$acf_plugin_pro_path = WP_PLUGIN_DIR . '/advanced-custom-fields-pro/acf.php';

		if ( ! file_exists( $acf_plugin_path ) && ! file_exists( $acf_plugin_pro_path ) && ! defined( 'DOLLIE_DEV' ) ) {
			// add_filter( 'acf/settings/show_admin', '__return_false' );
		}

		// Load Dollie Setup Class.
		if ( ! class_exists( 'Dollie_Setup' ) ) {
			update_option( '_dollie_setup_current_package', 'agency', true );
			require_once DOLLIE_CORE_PATH . 'Extras/dollie-setup/loader.php';
		}

		// Load TGM Class.
		if ( ! class_exists( 'OCDI_Plugin' ) && dollie()->auth()->is_connected()
		     && ! ( is_admin() && isset( $_GET['action'] ) && 'activate' === $_GET['action'] ) ) {
			require_once DOLLIE_CORE_PATH . 'Extras/one-click-demo-import/one-click-demo-import.php';
		}

		// Load logger.
		if ( ! class_exists( '\AF' )
		     && ! ( is_admin() && isset( $_GET['action'] ) && 'activate' === $_GET['action'] ) ) {
			require_once DOLLIE_CORE_PATH . 'Extras/advanced-forms/advanced-forms.php';
			require_once DOLLIE_CORE_PATH . 'Extras/acf-tooltip/acf-tooltip.php';
		}

		// Custom plugin updates.
		if ( file_exists( DOLLIE_CORE_PATH . 'Extras/plugin-update-checker/plugin-update-checker.php' ) ) {
			require_once DOLLIE_CORE_PATH . 'Extras/plugin-update-checker/plugin-update-checker.php';
			PucFactory::buildUpdateChecker(
				'https://control.getdollie.com/releases/?action=get_metadata&slug=dollie',
				DOLLIE_FILE, // Full path to the main plugin file or functions.php.
				'dollie'
			);
		}

		require_once DOLLIE_CORE_PATH . 'Extras/menu-walker/bootstrap-wp-navwalker.php';
	}

	/**
	 * Initialize modules and shortcodes
	 */
	public function initialize() {
		// Disable Elementor Onboarding
		update_option( 'elementor_onboarded', true );

		// Load elementor hooks.
		Elementor\Hooks::instance();

		// Load jobs.
		SyncContainersJob::instance();
		ChangeContainerRoleJob::instance();
		RemoveOldLogsJob::instance();
		CustomerAccessCheckJob::instance();

		// Modules.
		Forms::instance();
		Logging::instance();

		Integrations::instance();
		VipHooks::instance();
		AccessGroups::instance();

		// Hooks.
		PermissionControl::instance();
		Container::instance();
		Blueprints::instance();
		BulkActions::instance();
		RecurringActions::instance();
		Staging::instance();
		Domain::instance();
		Acf::instance();

		// Shortcodes.
		Shortcodes\Blockquote::instance();
		Shortcodes\Blueprints::instance();
		Shortcodes\CustomersList::instance();
		Shortcodes\GeneralAvatar::instance();
		Shortcodes\GeneralNavigation::instance();
		Shortcodes\LatestNews::instance();
		Shortcodes\LaunchSite::instance();
		Shortcodes\LaunchSiteBanner::instance();
		Shortcodes\LaunchSiteUrl::instance();
		Shortcodes\Orders::instance();
		Shortcodes\PostData::instance();
		Shortcodes\SiteContent::instance();
		Shortcodes\SiteNavigation::instance();
		Shortcodes\SiteStats::instance();
		Shortcodes\Sites::instance();
		Shortcodes\SiteScreenshot::instance();
		Shortcodes\SitesNavigation::instance();

		Shortcodes\WooNavigation::instance();

		// Load custom WP-CLI command if WP_CLI is defined.
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            require_once DOLLIE_CORE_PATH . 'Commands/SyncContainersCommand.php';
            \WP_CLI::add_command( 'dollie sync-containers', 'Dollie\Core\Commands\SyncContainersCommand' );
        }

		$this->load_routes();

		/**
		 * Allow developers to hook after dollie finished initialization
		 */
		do_action( 'dollie/initialized' );
	}

	/**
	 * Register ACF fields
	 */
	public function acf_add_local_field_groups() {
		require_once DOLLIE_CORE_PATH . 'Extras/AcfFields.php';
		require_once DOLLIE_CORE_PATH . 'Extras/AcfFormFields.php';
		require_once DOLLIE_CORE_PATH . 'Extras/AcfUserFields.php';
	}

	/**
	 * Load CSS and JS resources
	 *
	 * @return void
	 */
	public function load_resources() {
		$min = '.min';

		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			$min = '';
		}

		wp_enqueue_script('jquery');

		wp_enqueue_style(
			'dollie-base',
			DOLLIE_ASSETS_URL . 'css/dollie' . $min . '.css',
			array(),
			DOLLIE_VERSION
		);

		wp_enqueue_style(
			'dollie-tooltips',
			DOLLIE_ASSETS_URL . 'css/dollie-tooltips.css',
			array(),
			DOLLIE_VERSION
		);

		wp_register_script(
			'dollie-layout-alpine',
			DOLLIE_ASSETS_URL . 'js/alpine.min.js',
			array(),
			DOLLIE_VERSION,
			true
		);

		wp_enqueue_script(
			'dollie-tooltips',
			DOLLIE_ASSETS_URL . 'js/dollie-tooltips.js',
			array(),
			DOLLIE_VERSION,
			true
		);

		wp_enqueue_script(
			'dollie-global',
			DOLLIE_ASSETS_URL . 'js/dollie-global.js',
			array(),
			DOLLIE_VERSION,
			true
		);

		wp_register_script(
			'dollie-site-content',
			DOLLIE_ASSETS_URL . 'js/widgets/site-content.js',
			array(),
			DOLLIE_VERSION,
			true
		);

		wp_register_script(
			'dollie-launch-dynamic-data',
			DOLLIE_ASSETS_URL . 'js/launch-dynamic-data.js',
			array( 'jquery' ),
			DOLLIE_VERSION,
			true
		);

		wp_register_script(
			'dollie-site-list',
			DOLLIE_ASSETS_URL . 'js/widgets/sites-list.js',
			array(),
			DOLLIE_VERSION,
			true
		);

		wp_localize_script(
			'dollie-launch-dynamic-data',
			'wpdDynamicData',
			array(
				'ajaxurl'                => admin_url( '/admin-ajax.php' ),
				'validationErrorMessage' => __( 'Please fill in the Realtime Customizer fields.', 'dollie' ),
			)
		);
	}

	/**
	 * Load routes
	 */
	private function load_routes() {
		$router = new Router( 'dollie_route_name' );

		$routes = array(
			'dollie_login_redirect' => new Route( '/site_login_redirect', 'route_login_redirect' ),
			'dollie_wizard'         => new Route( '/wizard', 'route_wizard' ),
			'dollie_remote'         => new Route( '/dollie_remote', 'route_remote_data' ),
		);

		if ( get_option( 'options_wpd_enable_site_preview', 1 ) ) {
			$routes['dollie_preview'] = new Route( '/' . dollie()->get_preview_url( 'path' ), 'route_preview' );
		}

		Processor::init( $router, $routes );
	}

	/**
	 * Redirect to route
	 *
	 * @return void
	 */
	public function load_login_route() {
		if ( ! isset( $_GET['site_id'] ) ) {
			wp_redirect( home_url() );
			exit;
		}

		$container = dollie()->get_container( (int) $_GET['site_id'] );

		if ( is_wp_error( $container )
		     || ! $container->is_owned_by_current_user()
		     || ! wp_verify_nonce( $_GET['_nonce'], 'get_site_login' ) ) {
			wp_redirect( home_url() );
			exit;
		}

		$location  = ! empty( $_GET['location'] ) ? esc_attr( $_GET['location'] ) : '';
		$login_url = $container->get_login_url( $location );

		if ( ! $login_url ) {
			wp_redirect( $container->get_permalink() );
			exit;
		}

		wp_redirect( $login_url );
		exit;
	}

	/**
	 * Load preview
	 *
	 * @return void
	 */
	public function load_preview_route() {
		dollie()->load_template( 'preview', array(), true );
		exit;
	}

	/**
	 * Load wizard route
	 *
	 * @return void
	 */
	public function load_wizard_route() {
		dollie()->load_template( 'wizard', array(), true );
		exit;
	}
}
