<?php

namespace Dollie\Core\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

trait SiteApi {
	use Api;

	/**
	 * Get site by ID
	 *
	 * @param string $container_hash
	 *
	 * @return \WP_Error|array
	 */
	public function get_site_by_id( string $container_hash ) {
		return $this->get_request( "sites/{$container_hash}" );
	}

	/**
	 * Get all sites
	 *
	 * @return \WP_Error|array
	 */
	public function get_sites() {
		return $this->get_request( 'sites' );
	}

	/**
	 * Get login URL
	 *
	 * @return \WP_Error|array
	 */
	public function get_site_login_url( string $container_hash, string $username ) {
		return $this->post_request( "sites/{$container_hash}/login", [ 'username' => $username ] );
	}

	/**
	 * Perform action
	 *
	 * @param string $container_hash
	 * @param string $action
	 *
	 * @return \WP_Error|array
	 */
	public function perform_site_action( string $container_hash, string $action ) {
		return $this->post_request( "sites/{$container_hash}/action", [ 'action' => $action ] );
	}

	/**
	 * Set role
	 *
	 * @param string $container_hash
	 * @param array  $data
	 *
	 * @return \WP_Error|array
	 */
	public function set_user_role( string $container_hash, array $data ) {
		return $this->post_request( "sites/{$container_hash}/role", $data );
	}

	/**
	 * Delete
	 *
	 * @param string $container_hash
	 *
	 * @return \WP_Error|array
	 */
	public function delete_site( string $container_hash ) {
		return $this->delete_request( "sites/$container_hash" );
	}
}
