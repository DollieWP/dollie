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
	public function get_container_route_by_id( string $container_hash, string $route_id ) {
		return $this->get_request( "route/{$container_hash}/{$route_id}" );
	}

	/**
	 * Get all
	 *
	 * @param string $container_hash
	 *
	 * @return \WP_Error|array
	 */
	public function get_container_routes( string $container_hash ) {
		return $this->get_request( "route/{$container_hash}" );
	}

	/**
	 * Create
	 *
	 * @param string $container_hash
	 * @param array  $data
	 *
	 * @return \WP_Error|array
	 */
	public function create_container_route( string $container_hash, array $data ) {
		return $this->post_request( "route/{$container_hash}", $data );
	}

	/**
	 * Delete
	 *
	 * @param string $container_hash
	 * @param string $route_id
	 *
	 * @return \WP_Error|array
	 */
	public function delete_container_route( string $container_hash, string $route_id ) {
		return $this->delete_request( "route/{$container_hash}/{$route_id}" );
	}
}
