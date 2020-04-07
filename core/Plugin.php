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
use WP_Query;

/**
 * Class Plugin
 * @package Dollie\Core
 */
class Plugin extends Singleton {

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
	}

	/**
	 * Load Dollie dependencies. Make sure to call them on plugins_loaded
	 */
	public function load_dependencies() {

		// Load logger.
		if ( ! class_exists( '\WDS_Log_Post' ) ) {
			require_once DOLLIE_PATH . 'core/Extras/wds-log-post/wds-log-post.php';
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

		/**
		 * Allow developers to hook after dollie finished initialization
		 */
		do_action( 'dollie/initialized' );
	}

	/**
	 * Register ACF fields
	 */
	public function acf_add_local_field_groups() {
		require DOLLIE_PATH . 'core/Extras/AcfFields.php';
		require DOLLIE_PATH . 'core/Extras/AcfFormFields.php';
	}


	public function add_timestamp_body( $classes ) {
		$timestamp = get_transient( 'dollie_site_screenshot_' . dollie()->get_container_url() );

		if ( empty( $timestamp ) ) {
			$classes[] = 'wf-site-screenshot-not-set';
		}

		return $classes;
	}

	public function remove_customer_domain() {
		if ( isset( $_POST['remove_customer_domain'] ) ) {
			$currentQuery = dollie()->get_current_object();
			$post_id      = $currentQuery->id;
			$container_id = get_post_meta( $post_id, 'wpd_container_id', true );
			$route_id     = get_post_meta( $post_id, 'wpd_domain_id', true );
			$www_route_id = get_post_meta( $post_id, 'wpd_www_domain_id', true );

			Api::post( Api::ROUTE_DOMAIN_ROUTES_DELETE, [
				'container_id' => $container_id,
				'route_id'     => $route_id
			] );

			Api::post( Api::ROUTE_DOMAIN_ROUTES_DELETE, [
				'container_id' => $container_id,
				'route_id'     => $www_route_id
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

	public function redirect_to_container_launch() {

		if ( ! dollie()->get_launch_page_id() ) {
			return;
		}

		if ( current_user_can( 'manage_options' ) && dollie()->count_total_containers() === 0 && ! is_page( dollie()->get_launch_page_id() ) ) {
			wp_redirect( dollie()->get_launch_page_url() );
			exit;
		}
	}

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

	public function check_auth_admin_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( Api::get_auth_token() && Api::get_auth_token_status() ) {
			return;
		}

		// todo: set cron for token refresh

		?>
        <div class="notice dollie-notice">
            <div class="dollie-inner-message">
                <img width="60" src="<?php echo esc_url( DOLLIE_URL . 'assets/img/active.png' ); ?>">
                <div class="dollie-message-center">
					<?php if ( Api::get_auth_token() && ! Api::get_auth_token_status() ) : ?>
                        <p><?php _e( 'Your Dollie token has expired! Please reauthenticate this installation to continue using Dollie!', DOLLIE_DOMAIN ); ?></p>
					<?php else: ?>
						<h3>Dollie is almost ready...</h3>
                        <p><?php _e( 'Dollie is almost ready to go! Please authenticate this installation so that you can start launching your first (customer) sites using Dollie!', DOLLIE_DOMAIN ); ?></p>
					<?php endif; ?>
                </div>

                <div class="dollie-msg-button-right">
					<?php
					printf( '<a href="%s">%s</a>',
						'https://partners.getdollie.com/auth/?redirect_back=' . urlencode( get_admin_url() ),
						__( 'Click here', DOLLIE_DOMAIN ) );
					?>
                </div>
            </div>
        </div>
		<?php
	}

}
