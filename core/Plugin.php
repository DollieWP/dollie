<?php

namespace Dollie\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Admin\NavMenu\Component as NavMenu;
use Dollie\Core\Modules\ContainerBulkActions;
use Dollie\Core\Modules\ContainerRecurringActions;
use Dollie\Core\Modules\Subscription\Subscription;
use Dollie\Core\Modules\AccessControl;
use Dollie\Core\Modules\Backups;
use Dollie\Core\Modules\Blueprints;
use Dollie\Core\Modules\Jobs\RemoveOldLogsJob;
use Dollie\Core\Modules\ContainerFields;
use Dollie\Core\Modules\Container;
use Dollie\Core\Modules\ContainerRegistration;
use Dollie\Core\Modules\Staging;
use Dollie\Core\Modules\Logging;
use Dollie\Core\Modules\Forms;
use Dollie\Core\Modules\Hooks;
use Dollie\Core\Modules\Options;
//use Dollie\Core\Modules\Security;
use Dollie\Core\Modules\Upgrades;
use Dollie\Core\Modules\WooCommerce;
use Dollie\Core\Modules\Domain;
use Dollie\Core\Modules\Sites\WP;

use Dollie\Core\Modules\Jobs\ChangeContainerRoleJob;
use Dollie\Core\Modules\Jobs\UpdateContainerScreenshotsJob;
use Dollie\Core\Modules\Jobs\CustomerSubscriptionCheckJob;

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
	 * @var array
	 */
	private $routes;

	/**
	 * @var string
	 */
	public static $minimum_elementor_version = '3.0.0';

	/**
	 * Plugin constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_filter( 'body_class', [ $this, 'add_timestamp_body' ] );
		add_filter( 'body_class', [ $this, 'add_deploy_class' ] );
		add_action( 'plugins_loaded', [ $this, 'load_early_dependencies' ], - 10 );
		add_action( 'plugins_loaded', [ $this, 'load_dependencies' ], 0 );
		add_action( 'plugins_loaded', [ $this, 'initialize' ] );

		add_action( 'acf/init', [ $this, 'acf_add_local_field_groups' ] );

		add_action( 'admin_notices', [ Notices::instance(), 'admin_auth_notice' ] );
		add_action( 'admin_notices', [ Notices::instance(), 'admin_deployment_domain_notice' ] );
		add_action( 'admin_notices', [ Notices::instance(), 'admin_subscription_no_credits' ] );
		add_action( 'wp_ajax_dollie_hide_domain_notice', [ Notices::instance(), 'remove_deployment_domain_notice' ] );

		add_filter( 'dollie/api/after/get', [ $this, 'check_token_get_request' ], 10, 2 );
		add_filter( 'dollie/api/after/post', [ $this, 'check_token_post_request' ], 10, 3 );

		add_action( 'wp_enqueue_scripts', [ $this, 'load_assets' ], 12 );
		add_action( 'admin_enqueue_scripts', [ $this, 'load_admin_scripts' ] );
		add_shortcode( 'dollie_blockquote', [ $this, 'blockquote_shortcode' ] );

		add_action( 'route_login_redirect', [ $this, 'do_route_login_redirect' ] );
		add_action( 'route_preview', [ $this, 'do_route_preview' ] );
		add_action('route_wizard', [$this, 'do_route_wizard']);

	}

	/**
	 * Load early dependencies
	 */
	public function load_early_dependencies() {
		require_once DOLLIE_PATH . 'core/Extras/action-scheduler/action-scheduler.php';
	}

	/**
	 * Load Dollie dependencies. Make sure to call them on plugins_loaded
	 */
	public function load_dependencies() {
		if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
			add_action( 'admin_notices', [ Notices::instance(), 'elementor_notice' ] );

			return;
		}

		// Check for the minimum required Elementor version.
		if ( ! version_compare( ELEMENTOR_VERSION, self::$minimum_elementor_version, '>=' ) ) {
			add_action( 'admin_notices', [ Notices::instance(), 'admin_notice_minimum_elementor_version' ] );
		}

		// load ACF as fallback.
		if ( ! class_exists( 'ACF' ) ) {
			require_once DOLLIE_CORE_PATH . 'Extras/advanced-custom-fields/acf.php';
		}

		require_once DOLLIE_CORE_PATH . 'Extras/options-page-for-acf/loader.php';

		// Load Color Customizer
		require_once DOLLIE_CORE_PATH . 'Modules/Colors.php';

		// Load Theme/Plugins Compability
		require_once DOLLIE_PATH . 'core/Modules/Compatibility.php';

		// Load logger.
		if ( ! class_exists( '\WDS_Log_Post' ) ) {
			require_once DOLLIE_CORE_PATH . 'Extras/wds-log-post/wds-log-post.php';
		}

		// Load TGM Class
		if ( ! class_exists( 'TGM_Plugin_Activation' ) ) {
			require_once DOLLIE_CORE_PATH . 'Extras/tgm-plugin-activation/class-tgm-plugin-activation.php';
			require_once DOLLIE_CORE_PATH . 'Extras/tgm-plugin-activation/requirements.php';
		}

		// Load TGM Class
		if (!class_exists('Dollie_Setup')) {
			require_once DOLLIE_CORE_PATH . 'Extras/commons-in-a-box/loader.php';
		}

		// Load TGM Class
		if (!class_exists('OCDI_Plugin')) {
			require_once DOLLIE_CORE_PATH . 'Extras/one-click-demo-import/one-click-demo-import.php';
		}

		// WP Thumb
		if (!class_exists('WP_Thumb')) {
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
		Api::instance();

		// Load elementor hooks.
		Elementor\Hooks::instance();

		// Load jobs.
		ChangeContainerRoleJob::instance();
		UpdateContainerScreenshotsJob::instance();
		RemoveOldLogsJob::instance();
		CustomerSubscriptionCheckJob::instance();

		// Load modules.
		Forms::instance();
		AccessControl::instance();
		Backups::instance();
		Blueprints::instance();
		Subscription::instance();
		ContainerFields::instance();
		Container::instance();
		ContainerRecurringActions::instance();
		ContainerBulkActions::instance();
		ContainerRegistration::instance();
		Logging::instance();
		Hooks::instance();
		Options::instance();
		//Security::instance();
		WooCommerce::instance();
		Upgrades::instance();
		NavMenu::instance();
		WP::instance();
		Staging::instance();
		Domain::instance();

		// Shortcodes.
		Shortcodes\Blueprints::instance();
		Shortcodes\Orders::instance();
		Shortcodes\Sites::instance();

		$this->load_routes();

		/**
		 * Allow developers to hook after dollie finished initialization
		 */
		do_action( 'dollie/initialized' );
	}

	/**
	 * Load routes
	 */
	private function load_routes() {
		$router = new Router( 'dollie_route_name' );

		$this->routes = [
			'dollie_login_redirect' => new Route( '/site_login_redirect', 'route_login_redirect' ),
		];

		if ( get_option( 'options_wpd_enable_site_preview', 1 ) ) {
			$this->routes['dollie_preview'] = new Route( '/' . dollie()->get_preview_url( 'path' ), 'route_preview' );
		}

		//if (get_option('options_wpd_enable_site_preview', 1)) {
			$this->routes['dollie_wizard'] = new Route('/wizard', 'route_wizard');
		//}

		Processor::init( $router, $this->routes );
	}

	/**
	 * Register ACF fields
	 */
	public function acf_add_local_field_groups() {
		require DOLLIE_CORE_PATH . 'Extras/AcfFields.php';
		require DOLLIE_CORE_PATH . 'Extras/AcfFormFields.php';
	}

	/**
	 * Add body timestamp
	 *
	 * @param $classes
	 *
	 * @return array
	 */
	public function add_timestamp_body( $classes ) {
		$timestamp = get_transient( 'dollie_site_new_screenshot_' . get_the_ID() );

		if ( empty( $timestamp ) ) {
			$classes[] = 'wf-site-screenshot-not-set';
		}

		return $classes;
	}

	/**
	 * Add body timestamp
	 *
	 * @param $classes
	 *
	 * @return array
	 */
	public function add_deploy_class( $classes ) {
		$status = \Dollie\Core\Modules\Container::instance()->get_status( get_the_ID() );

		if ( 'pending' === $status ) {
			$classes[] = 'dollie-is-deploying';
		}

		return $classes;
	}

	/**
	 * Enqueue styles
	 */
	public function load_assets() {
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

		wp_register_script(
			'dollie-tooltips',
			DOLLIE_ASSETS_URL . 'js/dollie-tooltips.js',
			[],
			DOLLIE_VERSION,
			true
		);

		wp_enqueue_script( 'dollie-tooltips' );
	}

	/**
	 * Load scripts
	 *
	 * @param $hook
	 */
	public function load_admin_scripts() {
		wp_register_style( 'dollie-custom-css', DOLLIE_ASSETS_URL . 'css/admin.css', [], DOLLIE_VERSION );
		wp_register_style('dollie-custom-admin', DOLLIE_ASSETS_URL . 'css/dollie.css', [], DOLLIE_VERSION);
		wp_enqueue_style( 'dollie-custom-css' );
		wp_enqueue_style('dollie-custom-admin');

		wp_enqueue_script( 'dollie-custom-js', DOLLIE_ASSETS_URL . 'js/admin.js', [], DOLLIE_VERSION );
	}

	/**
	 * Handle token response
	 *
	 * @param $response
	 *
	 * @return bool
	 */
	public function handle_token_response( $response ) {
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$code = wp_remote_retrieve_response_code( $response );

		if ( 401 === $code ) {
			Log::add( 'Api Error: Not authenticated' );

			Api::delete_auth_token();
		}

		return false;
	}

	/**
	 * @param $response
	 * @param $endpoint
	 *
	 * @return array|\WP_Error
	 */
	public function check_token_get_request( $response, $endpoint ) {
		$resend_request = $this->handle_token_response( $response );

		if ( $resend_request ) {
			return Api::simple_get( $endpoint );
		}

		return $response;
	}

	/**
	 * @param $response
	 * @param $endpoint
	 * @param $body
	 *
	 * @return array|\WP_Error
	 */
	public function check_token_post_request( $response, $endpoint, $body ) {
		$resend_request = $this->handle_token_response( $response );

		if ( $resend_request ) {
			return Api::simple_post( $endpoint, $body );
		}

		return $response;
	}

	/**
	 * Get api access link
	 *
	 * @param bool $button
	 *
	 * @return string
	 */
	public function get_api_access_link( $button = false ) {
		return sprintf(
			'<a href="%s" class="%s">%s</a>',
			$this->get_api_access_url(),
			$button ? 'button' : '',
			__( 'Connect with Dollie', 'dollie' )
		);
	}

	/**
	 * Get api access link
	 *
	 * @return string
	 */
	public function get_api_access_url() {
		$url_data = [
			'origin' => admin_url( 'admin.php?page=wpd_platform_setup' ),
		];

		return add_query_arg( $url_data, Api::PARTNERS_URL . 'auth' );
	}

	/**
	 * Register blockquote shortcode
	 *
	 * @param array  $atts
	 * @param string $content
	 *
	 * @return string
	 */
	public function blockquote_shortcode( $atts = [], $content = '' ) {
		$atts = shortcode_atts(
			[
				'icon'  => 'fas fa-info-circle',
				'type'  => 'info',
				'title' => '',
			],
			$atts,
			'dollie_blockquote'
		);

		return dollie()->load_template(
			'notice',
			[
				'type'         => $atts['type'],
				'icon'         => $atts['icon'],
				'title'        => $atts['title'],
				'message'      => $content,
				'bottom_space' => true,
			]
		);

	}

	/**
	 * Redirectt to route
	 *
	 * @return void
	 */
	public function do_route_login_redirect() {
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

		wp_redirect( dollie()->final_customer_login_url( $container_id, $location, $staging ) );
		exit;
	}

	/**
	 * Load preview
	 *
	 * @return void
	 */
	public function do_route_preview() {
		dollie()->load_template( 'preview', [], true );
		exit;
	}

	public function do_route_wizard()
	{
		dollie()->load_template('wizard', [], true);
		exit;
	}

}
