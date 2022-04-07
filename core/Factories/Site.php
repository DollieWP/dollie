<?php

namespace Dollie\Core\Factories;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use WP_Post;
use Dollie\Core\Api\SiteApi;
use Dollie\Core\Api\RouteApi;
use Dollie\Core\Api\ZoneApi;

final class Site extends BaseContainer {
	use SiteApi;
	use RouteApi;
	use ZoneApi;

	/**
	 * Site constructor
	 *
	 * @param WP_Post $post
	 */
	public function __construct( WP_Post $post ) {
		parent::__construct( $post );
	}

	/**
	 * Refresh site details
	 *
	 * @return self
	 */
	public function fetch_details(): self {
		$data = $this->get_site_by_id( $this->get_hash() );

		if ( ! is_wp_error( $data ) && isset( $data[0] ) ) {
			$this->set_details( $data[0] );
		}

		return $this;
	}

	/**
	 * Get login URL
	 *
	 * @param string $location
	 *
	 * @return string
	 */
	public function get_login_url( string $location = '' ): string {
		$location = $location ? "&location={$location}" : '';

		$role = get_user_meta( $this->get_author_id(), 'wpd_client_site_permissions', true );

		if ( empty( $role ) || ! is_string( $role ) ) {
			if ( user_can( $this->get_author_id(), 'manage_options' ) ) {
				$role = 'administrator';
			} else {
				$role = get_field( 'wpd_client_site_permission', 'options' );
			}
		}

		$username = $this->get_details( 'site.admin.username' );

		if ( is_wp_error( $username ) ) {
			$username = '';
		}

		if ( 'administrator' !== $role && current_user_can( 'manage_options' ) ) {
			// $username = get_option( 'options_wpd_admin_user_name', $username );
		}

		if ( ! $username ) {
			return '';
		}

		$login_data = $this->get_site_login_url( $this->get_hash(), $username );

		if ( is_wp_error( $login_data ) || ! isset( $login_data['token'] ) || ! $login_data['token'] ) {
			return '';
		}

		return "https://{$this->get_url()}/wp-login.php?s5token={$login_data['token']}{$location}";
	}

	/**
	 * Get available blueprints
	 *
	 * @return \WP_Error|array
	 */
	public function get_available_blueprints() {
		return [];
	}

	/**
	 * Perform action
	 *
	 * @param string $action
	 *
	 * @return \WP_Error|array
	 */
	public function perform_action( string $action ) {
		$action = $this->perform_site_action( $this->get_hash(), $action );

		$this->after_status_change_event();

		return $action;
	}

	/**
	 * Set site domain
	 *
	 * @param string $domain
	 *
	 * @return \WP_Error|array
	 */
	public function set_domain( string $domain ) {
		return $this->set_site_domain( $this->get_hash(), $domain );
	}

	/**
	 * Change role
	 *
	 * @param array $data
	 *
	 * @return \WP_Error|array
	 */
	public function set_role( array $data ) {
		return $this->set_site_role( $this->get_hash(), $data );
	}

	/**
	 * Undeploy
	 *
	 * @return \WP_Error|array
	 */
	public function undeploy() {
		$deleted = $this->delete_site( $this->get_hash() );

		if ( ! is_wp_error( $deleted ) ) {
			$this->delete();
		}

		return $deleted;
	}

	/**
	 * Scan domain
	 *
	 * @param string $domain
	 *
	 * @return \WP_Error|array
	 */
	public function scan_domain( string $domain ) {
		return $this->scan_zone( $domain );
	}

	/**
	 * Get routes
	 *
	 * @return \WP_Error|array
	 */
	public function get_routes() {
		return $this->get_container_routes( $this->get_hash() );
	}

	/**
	 * Create route
	 *
	 * @param string $route
	 *
	 * @return \WP_Error|array
	 */
	public function create_route( string $route ) {
		return $this->create_container_route( $this->get_hash(), [ 'route' => $route ] );
	}

	/**
	 * Delete routes
	 *
	 * @return \WP_Error|array
	 */
	public function delete_routes() {
		return $this->delete_container_routes( $this->get_hash() );
	}

	/**
	 * Get zone by ID
	 *
	 * @param string $zone_id
	 *
	 * @return \WP_Error|array
	 */
	public function get_zone_by_id( string $zone_id ) {
		return $this->get_container_zone_by_id( $this->get_hash(), $zone_id );
	}

	/**
	 * Get zones
	 *
	 * @return \WP_Error|array
	 */
	public function get_zones() {
		return $this->get_container_zones( $this->get_hash() );
	}

	/**
	 * Create zone
	 *
	 * @param array $data
	 *
	 * @return \WP_Error|array
	 */
	public function create_zone( array $data ) {
		return $this->create_container_zone( $this->get_hash(), $data );
	}

	/**
	 * Delete zone
	 *
	 * @param string $zone_id
	 *
	 * @return \WP_Error|array
	 */
	public function delete_zone( string $zone_id ) {
		return $this->delete_container_zone( $this->get_hash(), $zone_id );
	}

	/**
	 * Get records by ID
	 *
	 * @param string $zone_id
	 * @param string $record_id
	 *
	 * @return \WP_Error|array
	 */
	public function get_record_by_id( string $zone_id, string $record_id ) {
		return $this->get_container_record_by_id( $this->get_hash(), $zone_id, $record_id );
	}

	/**
	 * Get records
	 *
	 * @param string $zone_id
	 *
	 * @return \WP_Error|array
	 */
	public function get_records( string $zone_id ) {
		return $this->get_container_records( $this->get_hash(), $zone_id );
	}

	/**
	 * Create records
	 *
	 * @param string $zone_id
	 * @param array  $data
	 *
	 * @return \WP_Error|array
	 */
	public function create_record( string $zone_id, array $data ) {
		return $this->create_container_record( $this->get_hash(), $zone_id, $data );
	}

	/**
	 * Delete record
	 *
	 * @param string $zone_id
	 * @param string $record_id
	 *
	 * @return \WP_Error|array
	 */
	public function delete_record( string $zone_id, string $record_id ) {
		return $this->delete_container_record( $this->get_hash(), $zone_id, $record_id );
	}
}
