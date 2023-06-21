<?php

namespace Dollie\Core\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;

final class PageManager extends Singleton {

	public function __construct() {
		parent::__construct();

		add_filter( 'display_post_states', array( $this, 'add_hub_post_states' ), 10, 2 );
	}

	/**
	 * Get launch site ID
	 *
	 * @return integer
	 */
	public function get_launch_site_id(): int {
		if ( function_exists( 'pll_get_post' ) ) {
			return (int) pll_get_post( get_option( 'options_wpd_launch_page_id' ) );
		}

		return (int) get_option( 'options_wpd_launch_page_id' );
	}

	/**
	 * Get launch blueprint ID
	 *
	 * @return integer
	 */
	public function get_launch_blueprint_id(): int {
		if ( function_exists( 'pll_get_post' ) ) {
			return (int) pll_get_post( get_option( 'options_wpd_launch_blueprint_page_id' ) );
		}

		return (int) get_option( 'options_wpd_launch_blueprint_page_id' );
	}

	/**
	 * Get dashboard ID
	 *
	 * @return integer
	 */
	public function get_dashboard_id(): int {
		if ( function_exists( 'pll_get_post' ) ) {
			return (int) pll_get_post( get_option( 'options_wpd_dashboard_page_id' ) );
		}

		return (int) get_option( 'options_wpd_dashboard_page_id' );
	}

	/**
	 * Get login ID
	 *
	 * @return integer
	 */
	public function get_login_id(): int {
		if ( function_exists( 'pll_get_post' ) ) {
			return (int) pll_get_post( get_option( 'options_wpd_login_page_id' ) );
		}

		return (int) get_option( 'options_wpd_login_page_id' );
	}

	/**
	 * Get sites ID
	 *
	 * @return integer
	 */
	public function get_sites_id(): int {
		if ( function_exists( 'pll_get_post' ) ) {
			return (int) pll_get_post( get_option( 'options_wpd_sites_page_id' ) );
		}

		return (int) get_option( 'options_wpd_sites_page_id' );
	}

	/**
	 * Get sites ID
	 *
	 * @return integer
	 */
	public function get_customers_id(): int {
		if ( function_exists( 'pll_get_post' ) ) {
			return (int) pll_get_post( get_option( 'options_wpd_customers_page_id' ) );
		}

		return (int) get_option( 'options_wpd_customers_page_id' );
	}

	/**
	 * Get site tpl ID
	 *
	 * @return integer
	 */
	public function get_site_tpl_id(): int {
		if ( function_exists( 'pll_get_post' ) ) {
			return (int) pll_get_post( get_option( 'options_wpd_site_template_id' ) );
		}

		return (int) get_option( 'options_wpd_site_template_id' );
	}

	/**
	 * Get launch site URL
	 *
	 * @param string $append
	 * @param array  $query
	 *
	 * @return boolean|string
	 */
	public function get_launch_site_url( string $append = '', array $query = [] ) {
		return $this->build_permalink( $this->get_launch_site_id(), $append, $query );
	}

	/**
	 * Get launch blueprint URL
	 *
	 * @param string $append
	 * @param array  $query
	 *
	 * @return boolean|string
	 */
	public function get_launch_blueprint_url( string $append = '', array $query = [] ) {
		return $this->build_permalink( $this->get_launch_blueprint_id(), $append, $query );
	}

	/**
	 * Get dashboard URL
	 *
	 * @param string $append
	 * @param array  $query
	 *
	 * @return boolean|string
	 */
	public function get_dashboard_url( string $append = '', array $query = [] ) {
		return $this->build_permalink( $this->get_dashboard_id(), $append, $query );
	}

	/**
	 * Get login URL
	 *
	 * @param string $append
	 * @param array  $query
	 *
	 * @return boolean|string
	 */
	public function get_login_url( string $append = '', array $query = [] ) {
		return $this->build_permalink( $this->get_login_id(), $append, $query );
	}

	/**
	 * Get sites URL
	 *
	 * @param string $append
	 * @param array  $query
	 *
	 * @return boolean|string
	 */
	public function get_sites_url( string $append = '', array $query = [] ) {
		return $this->build_permalink( $this->get_sites_id(), $append, $query );
	}

	/**
	 * Get blueprints URL
	 *
	 * @param string $append
	 * @param array  $query
	 *
	 * @return boolean|string
	 */
	public function get_blueprints_url( string $append = '', array $query = [] ) {
		return $this->build_permalink( $this->get_sites_id(), $append, array_merge( $query, [ 'blueprints' => 'yes' ] ) );
	}

	/**
	 * Get sites URL
	 *
	 * @param string $append
	 * @param array  $query
	 *
	 * @return boolean|string
	 */
	public function get_customers_url( string $append = '', array $query = [] ) {
		return $this->build_permalink( $this->get_customers_id(), $append, $query );
	}

	/**
	 * Check if current object is launch page
	 *
	 * @return boolean
	 */
	public function is_launch_site(): bool {
		return $this->get_launch_site_id() === get_the_ID();
	}

	/**
	 * Check if current object is blueprint page
	 *
	 * @return boolean
	 */
	public function is_launch_blueprint(): bool {
		return $this->get_launch_blueprint_id() === get_the_ID();
	}

	/**
	 * Check if current object is dashboard page
	 *
	 * @return boolean
	 */
	public function is_dashboard(): bool {
		return $this->get_dashboard_id() === get_the_ID();
	}

	/**
	 * Check if current object is sites page
	 *
	 * @return boolean
	 */
	public function is_sites(): bool {
		return $this->get_sites_id() === get_the_ID();
	}

	/**
	 * Check if current object is customers page
	 *
	 * @return boolean
	 */
	public function is_customers(): bool {
		return $this->get_customers_id() === get_the_ID();
	}


	/**
	 * Get launch TITLE
	 *
	 * @return string
	 */
	public function get_launch_title(): string {
		return get_the_title( $this->get_launch_site_id() );
	}

	/**
	 * Get launch blueprint TITLE
	 *
	 * @return string
	 */
	public function get_launch_blueprint_title(): string {
		 return get_the_title( $this->get_launch_blueprint_id() );
	}

	/**
	 * Get dashboard TITLE
	 *
	 * @return string
	 */
	public function get_dashboard_title(): string {
		return get_the_title( $this->get_dashboard_id() );
	}

	/**
	 * Get login TITLE
	 *
	 * @return string
	 */
	public function get_login_title(): string {
		return get_the_title( $this->get_login_id() );
	}

	/**
	 * Get sites TITLE
	 *
	 * @return string
	 */
	public function get_sites_title(): string {
		return get_the_title( $this->get_sites_id() );
	}

	/**
	 * Get sites TITLE
	 *
	 * @return string
	 */
	public function get_customers_title(): string {
		return get_the_title( $this->get_customers_id() );
	}

	/**
	 * Add a post display state for special WC pages in the page list table.
	 *
	 * @param array   $post_states An array of post display states.
	 * @param \WP_Post $post        The current post object.
	 */
	public function add_hub_post_states( $post_states, $post ) {
		if ( $this->get_launch_site_id() === $post->ID ) {
			$post_states['dollie_hub_launch_site'] = __( 'Dollie Hub - Launch Site', 'dollie' );
		}

//		if ( $this->get_launch_blueprint_id() === $post->ID ) {
//			$post_states['dollie_hub_launch_blueprint'] = __( 'Dollie Hub - Launch Blueprint', 'dollie' );
//		}

		if ( $this->get_sites_id() === $post->ID ) {
			$post_states['dollie_hub_sites'] = __( 'Dollie Hub - Sites', 'dollie' );
		}

		if ( $this->get_site_tpl_id() === $post->ID ) {
			$post_states['dollie_hub_site'] = __( 'Dollie Hub - Site Dashboard Template', 'dollie' );
		}

		if ( $this->get_login_id() === $post->ID ) {
			$post_states['dollie_hub_customer_login'] = __( 'Dollie Hub - Customer Login', 'dollie' );
		}

		if ( $this->get_customers_id() === $post->ID ) {
			$post_states['dollie_hub_customers'] = __( 'Dollie Hub - Customers', 'dollie' );
		}

		if ( $this->get_dashboard_id() === $post->ID ) {
			$post_states['dollie_hub_dashboard'] = __( 'Dollie Hub - Dashboard', 'dollie' );
		}

		return $post_states;
	}


	/**
	 * Build permalink
	 */
	private function build_permalink( int $id, string $append = '', array $query = [] ) {
		$link = get_permalink( $id );

		if ( false === $link ) {
			return $link;
		}

		return add_query_arg( $query, trailingslashit( $link ) . $append );
	}
}
