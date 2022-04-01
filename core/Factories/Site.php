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

		if ( is_array( $data ) && isset( $data[0] ) ) {
			$this->update_meta( $data[0] );
		}

		return $this;
	}

	/**
	 * Get available blueprints
	 *
	 * @return boolean|array
	 */
	public function get_available_blueprints(): bool|array {
		return [];
	}

	/**
	 * Perform action
	 *
	 * @param string $action
	 *
	 * @return boolean|array
	 */
	public function perform_action( string $action ): bool|array {
		$action = $this->perform_site_action( $this->get_hash(), $action );

		$this->after_status_change_event();

		return $action;
	}

	/**
	 * Set site domain
	 *
	 * @param string $domain
	 *
	 * @return boolean|array
	 */
	public function set_domain( string $domain ): bool|array {
		return $this->set_site_domain( $this->get_hash(), $domain );
	}

	/**
	 * Change role
	 *
	 * @param array $data
	 *
	 * @return boolean|array
	 */
	public function set_role( array $data ): bool|array {
		return $this->set_site_role( $this->get_hash(), $data );
	}

	/**
	 * Undeploy
	 *
	 * @return boolean|array
	 */
	public function undeploy(): bool|array {
		return $this->delete_site( $this->get_hash() );
	}

	/**
	 * Scan domain
	 *
	 * @param string $domain
	 *
	 * @return boolean|array
	 */
	public function scan_domain( string $domain ): bool|array {
		return $this->scan_zone( $domain );
	}

	/**
	 * Get route
	 *
	 * @param string $route_id
	 *
	 * @return boolean|array
	 */
	public function get_route( string $route_id ): bool|array {
		return $this->get_container_route_by_id( $this->get_hash(), $route_id );
	}

	/**
	 * Create route
	 *
	 * @param string $name
	 *
	 * @return boolean|array
	 */
	public function create_route( string $name ): bool|array {
		return $this->create_container_route( $this->get_hash(), [ 'name' => $name ] );
	}

	/**
	 * Delete route
	 *
	 * @param string $route_id
	 *
	 * @return boolean|array
	 */
	public function delete_route( string $route_id ): bool|array {
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
