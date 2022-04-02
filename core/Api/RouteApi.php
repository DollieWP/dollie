<?php

namespace Dollie\Core\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

trait RouteApi {
	use Api;

	/**
	 * Get by ID
	 *
	 * @param string $container_hash
	 * @param string $route_id
	 *
	 * @return \WP_Error|array
	 */
	public function get_container_route_by_id( string $container_hash, string $route_id ): \WP_Error|array {
		return $this->get_request( "route/{$container_hash}/{$route_id}" );
	}

	/**
	 * Create
	 *
	 * @param string $container_hash
	 * @param array  $data
	 *
	 * @return \WP_Error|array
	 */
	public function create_container_route( string $container_hash, array $data ): \WP_Error|array {
		return $this->post_request( "route/$container_hash", $data );
	}

	/**
	 * Delete
	 *
	 * @param string $container_hash
	 * @param string $route_id
	 *
	 * @return \WP_Error|array
	 */
	public function delete_container_route( string $container_hash, string $route_id ): \WP_Error|array {
		return $this->delete_request( "route/{$container_hash}/{$route_id}" );
	}
}