<?php

namespace Dollie\Core\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

trait StagingApi {
	use Api;

	/**
	 * Get staging by ID
	 *
	 * @param string $container_hash
	 *
	 * @return \WP_Error|array
	 */
	public function get_staging_by_id( string $container_hash ) {
		return $this->get_request( "stagings/{$container_hash}" );
	}

	/**
	 * Get all stagings
	 *
	 * @return \WP_Error|array
	 */
	public function get_stagings() {
		return $this->get_request( 'stagings' );
	}

	/**
	 * Perform action
	 *
	 * @param string $container_hash
	 * @param string $action
	 *
	 * @return \WP_Error|array
	 */
	public function perform_staging_action( string $container_hash, string $action ) {
		return $this->post_request( "stagings/{$container_hash}/action", [ 'action' => $action ] );
	}

	/**
	 * Push to live
	 *
	 * @param string $container_hash
	 *
	 * @return \WP_Error|array
	 */
	public function sync_staging( string $container_hash ) {
		return $this->post_request( "stagings/{$container_hash}/sync" );
	}

	/**
	 * Delete
	 *
	 * @param string $container_hash
	 *
	 * @return \WP_Error|array
	 */
	public function delete_staging( string $container_hash ) {
		return $this->delete_request( "stagings/$container_hash" );
	}
}
