<?php

namespace Dollie\Core\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

trait ResourceApi {
	use Api;

	/**
	 * Get plugins
	 *
	 * @param string $container_hash
	 *
	 * @return \WP_Error|array
	 */
	public function get_container_plugins( string $container_hash ): \WP_Error|array {
		return $this->get_request( "plugins/{$container_hash}" );
	}

	/**
	 * Update plugins
	 *
	 * @param string $container_hash
	 * @param array  $plugins
	 *
	 * @return \WP_Error|array
	 */
	public function update_container_plugins( string $container_hash, array $plugins ): \WP_Error|array {
		return $this->post_request( "plugins/{$container_hash}", [ 'plugins' => $plugins ] );
	}

	/**
	 * Get themes
	 *
	 * @param string $container_hash
	 *
	 * @return \WP_Error|array
	 */
	public function get_container_themes( string $container_hash ): \WP_Error|array {
		return $this->get_request( "themes/{$container_hash}" );
	}

	/**
	 * Update themes
	 *
	 * @param string $container_hash
	 * @param array  $themes
	 *
	 * @return \WP_Error|array
	 */
	public function update_container_themes( string $container_hash, array $themes ): \WP_Error|array {
		return $this->post_request( "themes/{$container_hash}", [ 'themes' => $themes ] );
	}
}
