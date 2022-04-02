<?php

namespace Dollie\Core\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;

final class PageManager extends Singleton {
	/**
	 * Get launch ID
	 *
	 * @return integer
	 */
	public function get_launch_id(): int {
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
	 * Get launch URL
	 *
	 * @param string $append
	 * @param array  $query
	 *
	 * @return boolean|string
	 */
	public function get_launch_url( string $append = '', array $query = [] ): bool|string {
		return $this->build_permalink( $this->get_launch_id(), $append, $query );
	}

	/**
	 * Get launch blueprint URL
	 *
	 * @param string $append
	 * @param array  $query
	 *
	 * @return boolean|string
	 */
	public function get_launch_blueprint_url( string $append = '', array $query = [] ): bool|string {
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
	public function get_dashboard_url( string $append = '', array $query = [] ): bool|string {
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
	public function get_login_url( string $append = '', array $query = [] ): bool|string {
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
	public function get_sites_url( string $append = '', array $query = [] ): bool|string {
		return $this->build_permalink( $this->get_sites_id(), $append, $query );
	}

	/**
	 * Check if current object is launch page
	 *
	 * @return boolean
	 */
	public function is_launch(): bool {
		return $this->get_launch_id() === get_the_ID();
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
	 * Get launch TITLE
	 *
	 * @return string
	 */
	public function get_launch_title(): string {
		return get_the_title( $this->get_launch_id() );
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
	 * Build permalink
	 */
	private function build_permalink( int $id, string $append = '', array $query = [] ): bool|string {
		$link = get_permalink( $id );

		if ( false === $link ) {
			return $link;
		}

		return add_query_arg( $query, trailingslashit( $link ) . $append );
	}
}