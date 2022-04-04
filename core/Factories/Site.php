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
	 * Get available blueprints
	 *
	 * @return \WP_Error|array
	 */
	public function get_available_blueprints(): \WP_Error|array {
		return [];
	}

	/**
	 * Perform action
	 *
	 * @param string $action
	 *
	 * @return \WP_Error|array
	 */
	public function perform_action( string $action ): \WP_Error|array {
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
	public function set_domain( string $domain ): \WP_Error|array {
		return $this->set_site_domain( $this->get_hash(), $domain );
	}

	/**
	 * Change role
	 *
	 * @param array $data
	 *
	 * @return \WP_Error|array
	 */
	public function set_role( array $data ): \WP_Error|array {
		return $this->set_site_role( $this->get_hash(), $data );
	}

	/**
	 * Undeploy
	 *
	 * @return \WP_Error|array
	 */
	public function undeploy(): \WP_Error|array {
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
	public function scan_domain( string $domain ): \WP_Error|array {
		return $this->scan_zone( $domain );
	}

	/**
	 * Get route
	 *
	 * @return \WP_Error|array
	 */
	public function get_routes(): \WP_Error|array {
		return $this->get_container_routes( $this->get_hash() );
	}

	/**
	 * Get route
	 *
	 * @param string $route_id
	 *
	 * @return \WP_Error|array
	 */
	public function get_route( string $route_id ): \WP_Error|array {
		return $this->get_container_route_by_id( $this->get_hash(), $route_id );
	}

	/**
	 * Create route
	 *
	 * @param string $name
	 *
	 * @return \WP_Error|array
	 */
	public function create_route( string $name ): \WP_Error|array {
		return $this->create_container_route( $this->get_hash(), [ 'name' => $name ] );
	}

	/**
	 * Delete route
	 *
	 * @param string $route_id
	 *
	 * @return \WP_Error|array
	 */
	public function delete_route( string $route_id ): \WP_Error|array {
		return $this->delete_container_route( $this->get_hash(), $route_id );
	}

	/**
	 * Get zone by ID
	 *
	 * @param string $zone_id
	 *
	 * @return boolean|array
	 */
	public function get_zone_by_id( string $zone_id ): bool|array {
		return $this->get_container_zone_by_id( $this->get_hash(), $zone_id );
	}

	/**
	 * Get zones
	 *
	 * @return boolean|array
	 */
	public function get_zones(): bool|array {
		return $this->get_container_zones( $this->get_hash() );
	}

	/**
	 * Create zone
	 *
	 * @param array $data
	 *
	 * @return boolean|array
	 */
	public function create_zone( array $data ): bool|array {
		return $this->create_container_zone( $this->get_hash(), $data );
	}

	/**
	 * Delete zone
	 *
	 * @param string $zone_id
	 *
	 * @return boolean|array
	 */
	public function delete_zone( string $zone_id ): bool|array {
		return $this->delete_container_zone( $this->get_hash(), $zone_id );
	}

	/**
	 * Get records by ID
	 *
	 * @param string $zone_id
	 * @param string $record_id
	 *
	 * @return boolean|array
	 */
	public function get_record_by_id( string $zone_id, string $record_id ): bool|array {
		return $this->get_container_record_by_id( $this->get_hash(), $zone_id, $record_id );
	}

	/**
	 * Get records
	 *
	 * @param string $zone_id
	 *
	 * @return boolean|array
	 */
	public function get_records( string $zone_id ): bool|array {
		return $this->get_container_records( $this->get_hash(), $zone_id );
	}

	/**
	 * Create records
	 *
	 * @param string $zone_id
	 * @param array  $data
	 *
	 * @return boolean|array
	 */
	public function create_record( string $zone_id, array $data ): bool|array {
		return $this->create_container_record( $this->get_hash(), $zone_id, $data );
	}

	/**
	 * Delete record
	 *
	 * @param string $zone_id
	 * @param string $record_id
	 *
	 * @return boolean|array
	 */
	public function delete_record( string $zone_id, string $record_id ): bool|array {
		return $this->delete_container_record( $this->get_hash(), $zone_id, $record_id );
	}
}
