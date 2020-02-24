<?php

namespace Dollie\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

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
	}

	/**
	 * Access Helpers class instance
	 *
	 * @return Helpers
	 */
	public function helpers() {
		return Helpers::instance();
	}

	/**
	 * Access AccessControl class instance
	 *
	 * @return \Dollie\Core\Modules\AccessControl
	 */
	public function access_control() {
		return \Dollie\Core\Modules\AccessControl::instance();
	}

	/**
	 * Access Backups class instance
	 *
	 * @return \Dollie\Core\Modules\Backups
	 */
	public function backups() {
		return \Dollie\Core\Modules\Backups::instance();
	}

	/**
	 * Access Blueprints class instance
	 *
	 * @return \Dollie\Core\Modules\Blueprints
	 */
	public function blueprints() {
		return \Dollie\Core\Modules\Blueprints::instance();
	}

	/**
	 * Access CheckSubscription class instance
	 *
	 * @return \Dollie\Core\Modules\CheckSubscription
	 */
	public function subscription() {
		return \Dollie\Core\Modules\CheckSubscription::instance();
	}

	/**
	 * Access ContainerManagement class instance
	 *
	 * @return \Dollie\Core\Modules\ContainerManagement
	 */
	public function container() {
		return \Dollie\Core\Modules\ContainerManagement::instance();
	}

	/**
	 * Access Blueprints class instance
	 *
	 * @return \Dollie\Core\Modules\SiteInsights
	 */
	public function insights() {
		return \Dollie\Core\Modules\SiteInsights::instance();
	}


	public function add_timestamp_body( $classes ) {
		$timestamp = get_transient( 'dollie_site_screenshot_' . $this->helpers()->get_container_url() );

		if ( empty( $timestamp ) ) {
			$classes[] = 'wf-site-screenshot-not-set';
		}

		return $classes;
	}

	public function remove_customer_domain() {
		if ( isset( $_POST['remove_customer_domain'] ) ) {
			$currentQuery = $this->helpers()->currentQuery;
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

			$this->helpers()->flush_container_details();

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
			$url = $this->helpers()->get_latest_container_url();

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
