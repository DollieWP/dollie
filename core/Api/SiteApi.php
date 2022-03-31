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
	public function get_site_by_id( string $container_hash ): \WP_Error|array {
		return $this->get_request( "sites/{$container_hash}" );
	}

	/**
	 * Get all sites
	 *
	 * @return \WP_Error|array
	 */
	public function get_sites(): \WP_Error|array {
		return $this->get_request( 'sites' );
	}

	/**
	 * Perform action
	 *
	 * @param string $container_hash
	 * @param string $action
	 *
	 * @return \WP_Error|array
	 */
	public function perform_site_action( string $container_hash, string $action ): \WP_Error|array {
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
	public function set_site_role( string $container_hash, array $data ): \WP_Error|array {
		return $this->post_request( "sites/{$container_hash}/role", $data );
	}

	/**
	 * Set domain
	 *
	 * @param string $container_hash
	 * @param string $domain
	 *
	 * @return \WP_Error|array
	 */
	public function set_site_domain( string $container_hash, string $domain ): \WP_Error|array {
		return $this->post_request( "sites/{$container_hash}/domain", [ 'domain' => $domain ] );
	}

	/**
	 * Delete
	 *
	 * @param string $container_hash
	 *
	 * @return \WP_Error|array
	 */
	public function delete_site( string $container_hash ): \WP_Error|array {
		return $this->delete_request( "sites/$container_hash" );
	}
}
