<?php

namespace Dollie\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Admin\Admin;
use Dollie\Core\Modules\BulkActions;
use Dollie\Core\Modules\RecurringActions;
use Dollie\Core\Modules\Subscription\Subscription;
use Dollie\Core\Modules\AccessControl;
use Dollie\Core\Modules\Blueprints;
use Dollie\Core\Modules\Container;
use Dollie\Core\Modules\Staging;
use Dollie\Core\Modules\Logging;
use Dollie\Core\Modules\Forms;
// use Dollie\Core\Modules\Security;

use Dollie\Core\Modules\WooCommerce;
use Dollie\Core\Modules\Domain;

use Dollie\Core\Jobs\RemoveOldLogsJob;
use Dollie\Core\Jobs\ChangeContainerRoleJob;
use Dollie\Core\Jobs\UpdateContainerScreenshotsJob;
use Dollie\Core\Jobs\CustomerSubscriptionCheckJob;

use Dollie\Core\Utils\Api;
use Dollie\Core\Utils\Notices;

use Dollie\Core\Routing\Processor;
use Dollie\Core\Routing\Route;
use Dollie\Core\Routing\Router;

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

		add_action( 'plugins_loaded', [ $this, 'load_early_dependencies' ], -10 );
		add_action( 'plugins_loaded', [ $this, 'load_dependencies' ], 0 );
		add_action( 'plugins_loaded', [ $this, 'initialize' ] );

		add_action( 'acf/init', [ $this, 'acf_add_local_field_groups' ] );

		// add_action( 'admin_notices', [ Notices::instance(), 'admin_auth_notice' ] );
		// add_action( 'admin_notices', [ Notices::instance(), 'admin_deployment_domain_notice' ] );
		// add_action( 'admin_notices', [ Notices::instance(), 'admin_subscription_no_credits' ] );
		// add_action( 'wp_ajax_dollie_hide_domain_notice', [ Notices::instance(), 'remove_deployment_domain_notice' ] );

		add_action( 'wp_enqueue_scripts', [ $this, 'load_resources' ], 12 );

		add_action( 'route_login_redirect', [ $this, 'load_login_route' ] );
		add_action( 'route_preview', [ $this, 'load_preview_route' ] );
		add_action( 'route_wizard', [ $this, 'load_wizard_route' ] );
	}

	/**
	 * Load early dependencies
	 */
	public function load_early_dependencies() {
		if ( ! dollie()->is_api_connected() ) {
			return;
		}

		require_once DOLLIE_PATH . 'core/Extras/action-scheduler/action-scheduler.php';
	}

	/**
	 * Load Dollie dependencies. Make sure to call them on plugins_loaded
	 */
	public function load_dependencies() {
		if ( ! dollie()->is_api_connected() ) {
			return;
		}

		// if (!defined('ELEMENTOR_VERSION')) {
		// add_action('admin_notices', [Notices::instance(), 'elementor_notice']);

		// return;
		// }

		// // Check for the minimum required Elementor version.
		// if (!version_compare(ELEMENTOR_VERSION, self::$minimum_elementor_version, '>=')) {
		// add_action('admin_notices', [Notices::instance(), 'admin_notice_minimum_elementor_version']);
		// }

		// load ACF as fallback.
		if ( ! class_exists( 'ACF' ) ) {
			require_once DOLLIE_CORE_PATH . 'Extras/advanced-custom-fields/acf.php';
		}

		require_once DOLLIE_CORE_PATH . 'Extras/options-page-for-acf/loader.php';

		// Load Color Customizer
		require_once DOLLIE_CORE_PATH . 'Modules/Colors.php';

		// Load Theme/Plugins Compability
		require_once DOLLIE_CORE_PATH . 'Compatibility.php';

		// Load logger.
		if ( ! class_exists( '\WDS_Log_Post' ) ) {
			require_once DOLLIE_CORE_PATH . 'Extras/wds-log-post/wds-log-post.php';
		}

		// Load TGM Class
		// if (!class_exists('TGM_Plugin_Activation')) {
		// require_once DOLLIE_CORE_PATH . 'Extras/tgm-plugin-activation/class-tgm-plugin-activation.php';
		// require_once DOLLIE_CORE_PATH . 'Extras/tgm-plugin-activation/requirements.php';
		// }

		// Load TGM Class
		if ( ! class_exists( 'Dollie_Setup' ) && dollie()->is_api_connected() ) {
			require_once DOLLIE_CORE_PATH . 'Extras/dollie-setup/loader.php';
		}

		// Load TGM Class
		if ( ! class_exists( 'OCDI_Plugin' ) && dollie()->is_api_connected() ) {
			require_once DOLLIE_CORE_PATH . 'Extras/one-click-demo-import/one-click-demo-import.php';
		}

		// WP Thumb
		if ( ! class_exists( 'WP_Thumb' ) ) {
			require_once DOLLIE_CORE_PATH . 'Extras/WPThumb/wpthumb.php';
		}

		// Load logger.
		if ( ! class_exists( '\AF' ) && ! ( is_admin() && isset( $_GET['action'] ) && 'activate' === $_GET['action'] ) ) {
			require_once DOLLIE_CORE_PATH . 'Extras/advanced-forms/advanced-forms.php';
			require_once DOLLIE_CORE_PATH . 'Extras/acf-tooltip/acf-tooltip.php';
		}

		require_once DOLLIE_CORE_PATH . 'Extras/menu-walker/bootstrap-wp-navwalker.php';
	}

	/**
	 * Initialize modules and shortcodes
	 */
	public function initialize() {
		// Load Api.
		// Api::instance();

		// Load elementor hooks.
		Elementor\Hooks::instance();

		// Load jobs.
		// ChangeContainerRoleJob::instance();
		// UpdateContainerScreenshotsJob::instance();
		// RemoveOldLogsJob::instance();
		// CustomerSubscriptionCheckJob::instance();

		// Load modules.
		Forms::instance();
		// AccessControl::instance();
		Blueprints::instance();
		// Subscription::instance();
		Container::instance();
		// RecurringActions::instance();
		// BulkActions::instance();
		// Logging::instance();
		// Security::instance();
		// WooCommerce::instance();

		// Staging::instance();
		// Domain::instance();

		// Shortcodes.
		// Shortcodes\Blockquote::instance();
		// Shortcodes\Blueprints::instance();
		// Shortcodes\Orders::instance();
		// Shortcodes\Sites::instance();

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
		require DOLLIE_CORE_PATH . 'Extras/AcfFields.php';
		require DOLLIE_CORE_PATH . 'Extras/AcfFormFields.php';
	}

	/**
	 * Load routes
	 */
	private function load_routes() {
		$router = new Router( 'dollie_route_name' );

		$routes = [
			'dollie_login_redirect' => new Route( '/site_login_redirect', 'route_login_redirect' ),
			'dollie_wizard'         => new Route( '/wizard', 'route_wizard' ),
		];

		if ( get_option( 'options_wpd_enable_site_preview', 1 ) ) {
			$routes['dollie_preview'] = new Route( '/' . dollie()->get_preview_url( 'path' ), 'route_preview' );
		}

		Processor::init( $router, $routes );
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

		wp_enqueue_style(
			'dollie-base',
			DOLLIE_ASSETS_URL . 'css/dollie' . $min . '.css',
			[],
			DOLLIE_VERSION
		);

		wp_enqueue_style(
			'dollie-tooltips',
			DOLLIE_ASSETS_URL . 'css/dollie-tooltips.css',
			[],
			DOLLIE_VERSION
		);

		wp_register_style(
			'swiper',
			DOLLIE_ASSETS_URL . 'lib/swiper/swiper-bundle.min.css',
			[],
			'6.4.15'
		);

		wp_register_script(
			'swiper',
			DOLLIE_ASSETS_URL . 'lib/swiper/swiper-bundle.min.js',
			[],
			'6.4.15',
			true
		);

		wp_register_script(
			'jquery-fitvids',
			DOLLIE_ASSETS_URL . 'lib/jquery.fitvids.min.js',
			[ 'jquery' ],
			'1.1.0',
			true
		);

		wp_register_script(
			'dollie-layout-alpine',
			DOLLIE_ASSETS_URL . 'js/alpine.min.js',
			[],
			DOLLIE_VERSION,
			true
		);

		wp_enqueue_script(
			'dollie-tooltips',
			DOLLIE_ASSETS_URL . 'js/dollie-tooltips.js',
			[],
			DOLLIE_VERSION,
			true
		);

		wp_register_script(
			'dollie-launch-dynamic-data',
			DOLLIE_ASSETS_URL . 'js/launch-dynamic-data.js',
			[ 'jquery' ],
			DOLLIE_VERSION,
			true
		);

		wp_localize_script(
			'dollie-launch-dynamic-data',
			'wpdDynamicData',
			[
				'ajaxurl'                => admin_url( '/admin-ajax.php' ),
				'validationErrorMessage' => __( 'Please fill in the Realtime Customizer fields.', 'dollie' ),
			]
		);
	}

	/**
	 * Redirect to route
	 *
	 * @return void
	 */
	public function load_login_route() {
		if ( ! isset( $_GET['site'] ) || 0 === (int) $_GET['site'] ) {
			wp_redirect( home_url() );
			exit;
		}

		if ( ! wp_verify_nonce( $_GET['_nonce'], 'get_site_login' ) ) {
			wp_redirect( home_url() );
			exit;
		}

		$container_id = (int) $_GET['site'];
		$container    = dollie()->get_current_object( $container_id );

		if ( ! current_user_can( 'manage_options' ) && get_current_user_id() != $container->author ) {
			wp_redirect( home_url() );
			exit;
		}

		$staging  = isset( $_GET['staging'] ) ? true : false;
		$location = ! empty( $_GET['location'] ) ? esc_attr( $_GET['location'] ) : null;

		// wp_redirect( dollie()->final_customer_login_url( $container_id, $location, $staging ) );
		exit;
	}

	/**
	 * Load preview
	 *
	 * @return void
	 */
	public function load_preview_route() {
		dollie()->load_template( 'preview', [], true );
		exit;
	}

	/**
	 * Load wizard route
	 *
	 * @return void
	 */
	public function load_wizard_route() {
		dollie()->load_template( 'wizard', [], true );
		exit;
	}
}
