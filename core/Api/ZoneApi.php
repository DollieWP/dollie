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
	public function scan_zone( string $domain ) {
		return $this->post_request( 'zone/scan', [ 'domain' => $domain ] );
	}

	/**
	 * Get zone by ID
	 *
	 * @param string $container_hash
	 * @param string $zone_id
	 *
	 * @return \WP_Error|array
	 */
	public function get_container_zone_by_id( string $container_hash, string $zone_id ) {
		return $this->get_request( "zone/{$container_hash}/{$zone_id}" );
	}

	/**
	 * Get all zones
	 *
	 * @param string $container_hash
	 *
	 * @return \WP_Error|array
	 */
	public function get_container_zones( string $container_hash ) {
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
	public function create_container_zone( string $container_hash, array $data ) {
		return $this->post_request( "zone/{$container_hash}", $data );
	}

	/**
	 * Delete zone
	 *
	 * @param string $container_hash
	 *
	 * @return \WP_Error|array
	 */
	public function delete_container_zone( string $container_hash ) {
		return $this->delete_request( "zone/{$container_hash}" );
	}

	/**
	 * Get records by id
	 *
	 * @param string $container_hash
	 * @param string $record_id
	 *
	 * @return \WP_Error|array
	 */
	public function get_container_record_by_id( string $container_hash, string $record_id ) {
		return $this->get_request( "zone/{$container_hash}/records/{$record_id}" );
	}

	/**
	 * Get all records
	 *
	 * @param string $container_hash
	 *
	 * @return \WP_Error|array
	 */
	public function get_container_records( string $container_hash ) {
		return $this->get_request( "zone/{$container_hash}/records" );
	}

	/**
	 * Create record
	 *
	 * @param string $container_hash
	 * @param array  $data
	 *
	 * @return \WP_Error|array
	 */
	public function create_container_record( string $container_hash, array $data ) {
		return $this->post_request( "zone/{$container_hash}/records", $data );
	}

	/**
	 * Delete record
	 *
	 * @param string $container_hash
	 * @param string $record_id
	 *
	 * @return \WP_Error|array
	 */
	public function delete_container_record( string $container_hash, string $record_id ) {
		return $this->delete_request( "zone/{$container_hash}/records/{$record_id}" );
	}
}
