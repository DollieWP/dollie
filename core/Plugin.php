<?php

namespace Dollie\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Modules\AccessControl;
use Dollie\Core\Modules\Backups;
use Dollie\Core\Modules\Blueprints;
use Dollie\Core\Modules\CheckSubscription;
use Dollie\Core\Modules\ContainerFields;
use Dollie\Core\Modules\ContainerManagement;
use Dollie\Core\Modules\ContainerRegistration;
use Dollie\Core\Modules\Custom;
use Dollie\Core\Modules\Forms;
use Dollie\Core\Modules\Hooks;
use Dollie\Core\Modules\Options;
use Dollie\Core\Modules\SecurityChecks;
use Dollie\Core\Modules\Upgrades;
use Dollie\Core\Modules\WooCommerce;

use Dollie\Core\Utils\Api;
use Dollie\Core\Routing\Processor;
use Dollie\Core\Routing\Route;
use Dollie\Core\Routing\Router;
use WP_Query;

/**
 * Class Plugin
 * @package Dollie\Core
 */
class Plugin extends Singleton {

	/**
	 * @var array
	 */
	private $routes;

	/**
	 * Plugin constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_filter( 'body_class', [ $this, 'add_timestamp_body' ] );

		add_action( 'template_redirect', [ $this, 'remove_customer_domain' ] );
		add_action( 'template_redirect', [ $this, 'redirect_to_container_launch' ] );

		add_action( 'init', [ $this, 'set_default_view_time_total_containers' ] );

		add_action( 'plugins_loaded', [ $this, 'load_dependencies' ], 0 );
		add_action( 'plugins_loaded', [ $this, 'initialize' ] );

		add_action( 'acf/init', [ $this, 'acf_add_local_field_groups' ] );

		add_action( 'admin_notices', [ $this, 'check_auth_admin_notice' ] );

		add_filter( 'dollie/api/after/get', [ $this, 'check_token_get_request' ], 10, 2 );
		add_filter( 'dollie/api/after/post', [ $this, 'check_token_post_request' ], 10, 3 );
	}

	/**
	 * Load Dollie dependencies. Make sure to call them on plugins_loaded
	 */
	public function load_dependencies() {

	    // load ACF as fallback.
		if ( ! class_exists( 'ACF' ) ) {
			require_once DOLLIE_PATH . 'core/Extras/advanced-custom-fields-pro/acf.php';
		}

		// Load logger.
		if ( ! class_exists( '\WDS_Log_Post' ) ) {
			require_once DOLLIE_PATH . 'core/Extras/wds-log-post/wds-log-post.php';
		}

		// Load TGM Class
		if (!class_exists('TGM_Plugin_Activation')) {
			require_once DOLLIE_PATH . 'core/Extras/tgm-plugin-activation/class-tgm-plugin-activation.php';
			require_once DOLLIE_PATH . 'core/Extras/tgm-plugin-activation/requirements.php';
		}

		// Load customizer framework.
		require_once DOLLIE_PATH . 'core/Extras/kirki/kirki.php';

		// Load logger.
		if ( ! class_exists( '\AF' ) && ! ( is_admin() && isset( $_GET['action'] ) && $_GET['action'] === 'activate' ) ) {
			require_once DOLLIE_PATH . 'core/Extras/advanced-forms/advanced-forms.php';
			require_once DOLLIE_PATH . 'core/Extras/acf-tooltip/acf-tooltip.php';
		}
	}

	/**
	 * Initialize modules and shortcodes
	 */
	public function initialize() {

		// Load modules
		Forms::instance();
		AccessControl::instance();
		Backups::instance();
		Blueprints::instance();
		CheckSubscription::instance();
		ContainerFields::instance();
		ContainerManagement::instance();
		ContainerRegistration::instance();
		Custom::instance();
		Hooks::instance();
		Options::instance();
		SecurityChecks::instance();
		WooCommerce::instance();
		Modules\Dashboard\Setup::instance();
		Upgrades::instance();

		// Shortcodes
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

		if ( ! get_option( 'options_wpd_enable_site_preview', 1 ) ) {
			return;
		}

		$router       = new Router( 'dollie_route_name' );
		$this->routes = [
			'dollie_preview' => new Route( '/' . dollie()->get_preview_url( 'path' ), '', DOLLIE_CORE_PATH . 'Extras/preview/index.php' ),
		];

		Processor::init( $router, $this->routes );
	}

	/**
	 * Register ACF fields
	 */
	public function acf_add_local_field_groups() {
		if ( defined( 'DOLLIE_DEV' ) ) {
			return;
		}

		require DOLLIE_PATH . 'core/Extras/AcfFields.php';
		require DOLLIE_PATH . 'core/Extras/AcfFormFields.php';
	}

	/**
	 * Add body timestamp
	 *
	 * @param $classes
	 *
	 * @return array
	 */
	public function add_timestamp_body( $classes ) {
		$timestamp = get_transient( 'dollie_site_screenshot_' . dollie()->get_container_url() );

		if ( empty( $timestamp ) ) {
			$classes[] = 'wf-site-screenshot-not-set';
		}

		return $classes;
	}

	/**
	 * Remove customer domain
	 */
	public function remove_customer_domain() {
		if ( isset( $_POST['remove_customer_domain'] ) ) {
			$currentQuery = dollie()->get_current_object();
			$post_id      = $currentQuery->id;
			$container_id = get_post_meta( $post_id, 'wpd_container_id', true );
			$route_id     = get_post_meta( $post_id, 'wpd_domain_id', true );
			$www_route_id = get_post_meta( $post_id, 'wpd_www_domain_id', true );

			Api::post( Api::ROUTE_DOMAIN_ROUTES_DELETE, [
				'container_id'  => $container_id,
				'route_id'      => $route_id,
				'dollie_domain' => DOLLIE_INSTALL,
				'dollie_token'  => Api::get_dollie_token(),
			] );

			Api::post( Api::ROUTE_DOMAIN_ROUTES_DELETE, [
				'container_id'  => $container_id,
				'route_id'      => $www_route_id,
				'dollie_domain' => DOLLIE_INSTALL,
				'dollie_token'  => Api::get_dollie_token(),
			] );

			dollie()->flush_container_details();

			delete_post_meta( $post_id, 'wpd_domain_migration_complete' );
			delete_post_meta( $post_id, 'wpd_cloudflare_zone_id' );
			delete_post_meta( $post_id, 'wpd_cloudflare_id' );
			delete_post_meta( $post_id, 'wpd_cloudflare_active' );
			delete_post_meta( $post_id, 'wpd_cloudflare_api' );
			delete_post_meta( $post_id, 'wpd_domain_id' );
			delete_post_meta( $post_id, 'wpd_letsencrypt_setup_complete' );
			delete_post_meta( $post_id, 'wpd_letsencrypt_enabled' );
			delete_post_meta( $post_id, 'wpd_domains' );
			delete_post_meta( $post_id, 'wpd_www_domain_id' );
			delete_post_meta( $post_id, 'wpd_cloudflare_email' );

			wp_redirect( get_site_url() . '/site/' . $currentQuery->slug . '/?get-details' );
			exit();
		}
	}

	/**
	 * Redirect to launch
	 */
	public function redirect_to_container_launch() {
		if ( ! dollie()->get_launch_page_id() ) {
			return;
		}

		if ( current_user_can( 'manage_options' ) && dollie()->count_total_containers() === 0 && ! is_page( dollie()->get_launch_page_id() ) ) {
			wp_redirect( dollie()->get_launch_page_url() );
			exit;
		}
	}

	/**
	 * Default view time total containers
	 */
	public function set_default_view_time_total_containers() {
		$query = new WP_Query( [
			'post_type'     => 'container',
			'post_status'   => 'publish',
			'post_per_page' => 9999999,
			'meta_query'    => [
				[
					'key'     => 'wpd_last_viewed',
					'compare' => 'NOT EXISTS'
				]
			]
		] );

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				update_post_meta( get_the_ID(), 'wpd_last_viewed', '1' );
			}
		}

		wp_reset_postdata();
		wp_reset_query();
	}

	/**
	 * Admin notice for token
	 */
	public function check_auth_admin_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( Api::get_auth_data( 'access_token' ) && Api::auth_token_is_active() ) {
			return;
		}

		?>
        <div class="notice dollie-notice">
            <div class="dollie-inner-message">
                <img width="60" src="<?php echo esc_url( DOLLIE_URL . 'assets/img/active.png' ); ?>">
                <div class="dollie-message-center">
					<?php if ( Api::get_auth_data( 'refresh_token' ) && ! Api::auth_token_is_active() ) : ?>
                        <p><?php _e( 'Your Dollie token has expired! Please reauthenticate this installation to continue using Dollie!', DOLLIE_DOMAIN ); ?></p>
					<?php else: ?>
                        <h3><?php esc_html_e( 'Dollie is almost ready...', 'dollie' ); ?> </h3>
                        <p><?php _e( 'Please authenticate this installation so that you can start launching your first (customer) sites using Dollie!', DOLLIE_DOMAIN ); ?></p>
					<?php endif; ?>
                </div>

				<?php if ( Api::get_auth_data( 'refresh_token' ) && ! Api::auth_token_is_active() ) : ?>
                    <div class="dollie-msg-button-right">
						<?php echo $this->get_api_refresh_link(); ?>
                    </div>
				<?php else: ?>
                    <div class="dollie-msg-button-right">
						<?php echo $this->get_api_access_link(); ?>
                    </div>
				<?php endif; ?>
            </div>
        </div>
		<?php
	}

	public function handle_token_response( $response ) {
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$code = wp_remote_retrieve_response_code( $response );

		if ( $code === 401 ) {
			$refresh_token = Api::get_auth_data( 'refresh_token' );
			Api::update_auth_token_status( 0 );

			if ( ! $refresh_token ) {
				wp_redirect( admin_url( 'admin.php?page=wpd_api&status=not_connected' ) );
				die();
			}

			$request = wp_remote_get( 'https://partners.getdollie.com/refresh-token-api?refresh_token=' . base64_encode( $refresh_token ) );

			if ( is_wp_error( $request ) ) {
				wp_redirect( admin_url( 'admin.php?page=wpd_api&err' ) );
				die();
			}

			$response = json_decode( wp_remote_retrieve_body( $request ), true );

			if ( $response['status'] === 500 ) {
				Api::delete_auth_data();
				wp_redirect( admin_url( 'admin.php?page=wpd_api&status=not_connected' ) );
				die();
			}

			if ( is_array( $response['body'] ) ) {
				Api::update_auth_data( $response['body'] );

				return true;
			}

			Api::delete_auth_data();
			wp_redirect( admin_url( 'admin.php?page=wpd_api&status=not_connected' ) );
			die();
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
	 * @param bool $button
	 *
	 * @return string
	 */
	public function get_api_access_link( $button = false ) {
		$url_data = [
			'scope'           => 'offline api.read',
			'response_type'   => 'code',
			'client_id'       => 'dollie-wp-plugin',
			'redirect_uri'    => 'https://partners.getdollie.com/callback',
			'state'           => hash( 'sha256', microtime( true ) . mt_rand() . $_SERVER['REMOTE_ADDR'] ),
			'redirect_origin' => admin_url( 'admin.php?page=wpd_api' )
		];

		$url = sprintf( '<a href="%s" class="%s">%s</a>',
			add_query_arg( $url_data, 'https://oauth2.stratus5.net/hydra-public/oauth2/auth' ),
			$button ? 'button' : '',
			__( 'Gain API Access', DOLLIE_DOMAIN ) );

		return $url;
	}

	/**
	 * @param bool $button
	 *
	 * @return string
	 */
	public function get_api_refresh_link( $button = false ) {
		$url_data = [
			'redirect_origin' => admin_url( 'admin.php?page=wpd_api' ),
			'refresh_token'   => base64_encode( Api::get_auth_data( 'refresh_token' ) )
		];

		$url = sprintf( '<a href="%s" class="%s">%s</a>',
			add_query_arg( $url_data, 'https://partners.getdollie.com/refresh-token' ),
			$button ? 'button' : '',
			__( 'Refresh API Token', DOLLIE_DOMAIN ) );

		return $url;
	}

}
