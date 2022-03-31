<?php

namespace Dollie\Core\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

trait DeployApi {
	use Api;

	/**
	 * Start deploy
	 *
	 * @param string $type
	 * @param array  $data
	 *
	 * @return \WP_Error|array
	 */
	public function start_deploy( string $type, array $data ): \WP_Error|array {
		return $this->post_request( "{$type}/deploy", $data );
	}

	/**
	 * Get deploy
	 *
	 * @param string $type
	 * @param string $route
	 *
	 * @return \WP_Error|array
	 */
	public function get_deploy( string $type, string $route ): \WP_Error|array {
		return $this->get_request( "{$type}/deploy/{$route}" );
	}
}
