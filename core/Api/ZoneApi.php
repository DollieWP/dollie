<?php

namespace Dollie\Core\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

trait ZoneApi {
	use Api;

	/**
	 * Scan domain's records
	 *
	 * @param string $domain
	 *
	 * @return \WP_Error|array
	 */
	public function scan_zone( string $domain ): \WP_Error|array {
		return $this->post_request( 'zone/scan' );
	}

	/**
	 * Get zone by ID
	 *
	 * @param string $container_hash
	 * @param string $zone_id
	 *
	 * @return \WP_Error|array
	 */
	public function get_container_zone_by_id( string $container_hash, string $zone_id ): \WP_Error|array {
		return $this->get_request( "zone/{$container_hash}/{$zone_id}" );
	}

	/**
	 * Get all zones
	 *
	 * @param string $container_hash
	 *
	 * @return \WP_Error|array
	 */
	public function get_container_zones( string $container_hash ): \WP_Error|array {
		return $this->get_request( "zone/{$container_hash}" );
	}

	/**
	 * Create zone
	 *
	 * @param string $container_hash
	 * @param array  $data
	 *
	 * @return \WP_Error|array
	 */
	public function create_container_zone( string $container_hash, array $data ): \WP_Error|array {
		return $this->post_request( "zone/{$container_hash}", $data );
	}

	/**
	 * Delete zone
	 *
	 * @param string $container_hash
	 * @param string $zone_id
	 *
	 * @return \WP_Error|array
	 */
	public function delete_container_zone( string $container_hash, string $zone_id ): \WP_Error|array {
		return $this->delete_request( "zone/{$container_hash}/{$zone_id}" );
	}

	/**
	 * Get records by id
	 *
	 * @param string $container_hash
	 * @param string $zone_id
	 * @param string $record_id
	 *
	 * @return \WP_Error|array
	 */
	public function get_container_record_by_id( string $container_hash, string $zone_id, string $record_id ): \WP_Error|array {
		return $this->get_request( "zone/{$container_hash}/{$zone_id}/records/{$record_id}" );
	}

	/**
	 * Get all records
	 *
	 * @param string $container_hash
	 * @param string $zone_id
	 *
	 * @return \WP_Error|array
	 */
	public function get_container_records( string $container_hash, string $zone_id ): \WP_Error|array {
		return $this->get_request( "zone/{$container_hash}/{$zone_id}/records" );
	}

	/**
	 * Create record
	 *
	 * @param string $container_hash
	 * @param string $zone_id
	 * @param array  $data
	 *
	 * @return \WP_Error|array
	 */
	public function create_container_record( string $container_hash, string $zone_id, array $data ): \WP_Error|array {
		return $this->post_request( "zone/{$container_hash}/{$zone_id}/records", $data );
	}

	/**
	 * Delete record
	 *
	 * @param string $container_hash
	 * @param string $zone_id
	 * @param string $record_id
	 *
	 * @return \WP_Error|array
	 */
	public function delete_container_record( string $container_hash, string $zone_id, string $record_id ): \WP_Error|array {
		return $this->delete_request( "zone/{$container_hash}/{$zone_id}/records/{$record_id}" );
	}
}
