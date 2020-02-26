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
use Dollie\Core\Modules\DeleteSite;
use Dollie\Core\Modules\DomainWizard;
use Dollie\Core\Modules\Hooks;
use Dollie\Core\Modules\ImportGravityForms;
use Dollie\Core\Modules\LaunchSite;
use Dollie\Core\Modules\Options;
use Dollie\Core\Modules\PluginUpdates;
use Dollie\Core\Modules\Scripts;
use Dollie\Core\Modules\SecurityChecks;
use Dollie\Core\Modules\Tools;
use Dollie\Core\Modules\WelcomeWizard;
use Dollie\Core\Modules\WooCommerce;
use Dollie\Core\Utils\Helpers;
use WP_Query;

/**
 * Class Plugin
 * @package Dollie\Core
 */
class Plugin extends Singleton {

	public function __construct() {
		parent::__construct();

		add_filter( 'body_class', [ $this, 'add_timestamp_body' ] );
		add_action( 'template_redirect', [ $this, 'remove_customer_domain' ] );
		add_action( 'template_redirect', [ $this, 'redirect_to_new_container' ] );
		add_action( 'init', [ $this, 'set_default_view_time_total_containers' ] );

		add_action( 'plugins_loaded', [ $this, 'plugins_loaded' ] );

		add_action( 'acf/init', [ $this, 'acf_add_local_field_groups' ] );
	}


	/**
	 * Access ContainerManagement class instance
	 *
	 * @return \Dollie\Core\Modules\ContainerManagement
	 */
	public function container() {
		return \Dollie\Core\Modules\ContainerManagement::instance();
	}

	public function plugins_loaded() {
		//Load extras
		require_once DOLLIE_PATH . 'core/Extras/wds-log-post/wds-log-post.php';

		// Load modules
		AccessControl::instance();
		Backups::instance();
		Blueprints::instance();
		CheckSubscription::instance();
		ContainerFields::instance();
		ContainerManagement::instance();
		ContainerRegistration::instance();
		Custom::instance();
		DeleteSite::instance();
		DomainWizard::instance();
		Hooks::instance();
		ImportGravityForms::instance();
		LaunchSite::instance();
		Options::instance();
		PluginUpdates::instance();
		SecurityChecks::instance();
		Tools::instance();
		WelcomeWizard::instance();
		WooCommerce::instance();
		Modules\Dashboard\Setup::instance();

		// Shortcodes
		Shortcodes\Blueprints::instance();
		Shortcodes\Orders::instance();
		Shortcodes\Sites::instance();

	}

	public function acf_add_local_field_groups() {
		require DOLLIE_PATH . 'core/Extras/ACF-fields.php';
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

			// Take output buffer for our body in our POST request
			$url     = DOLLIE_INSTALL . '/s5Api/v1/sites/' . $container_id . '/routes/' . $route_id;
			$www_url = DOLLIE_INSTALL . '/s5Api/v1/sites/' . $container_id . '/routes/' . $www_route_id;

			// Set up the request
			wp_remote_post(
				$url,
				array(
					'method'  => 'DELETE',
					'headers' => array(
						'Authorization' => 'Basic ' . base64_encode( DOLLIE_S5_USER . ':' . DOLLIE_S5_PASSWORD ),
						'Content-Type'  => 'application/json',
					),
				)
			);

			// Set up the request
			wp_remote_post(
				$www_url,
				array(
					'method'  => 'DELETE',
					'headers' => array(
						'Authorization' => 'Basic ' . base64_encode( DOLLIE_S5_USER . ':' . DOLLIE_S5_PASSWORD ),
						'Content-Type'  => 'application/json',
					),
				)
			);

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

	public function redirect_to_new_container() {
		if ( isset( $_GET['site'] ) && $_GET['site'] === 'new' ) {
			$url = dollie()->get_latest_container_url();

			if ( $url ) {
				wp_redirect( $url );
				exit();
			}
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

}
