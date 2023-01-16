<?php

namespace Dollie\Core\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

trait StatsApi {
	use Api;

	/**
	 * Get resource usage
	 *
	 * @param string $container_hash
	 *
	 * @return \WP_Error|array
	 */
	public function get_container_resource_usage( string $container_hash ) {
		return $this->get_request( "stats/{$container_hash}" );
	}

}
