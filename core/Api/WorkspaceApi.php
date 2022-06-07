<?php

namespace Dollie\Core\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

trait WorkspaceApi {
	use Api;

	/**
	 * Add custom domain
	 *
	 * @param string $domain
	 *
	 * @return \WP_Error|array
	 */
	public function add_custom_domain( string $domain ) {
		return $this->post_request( 'workspace/domain', [ 'domain' => $domain ] );
	}

	/**
	 * Remove custom domain
	 *
	 * @return \WP_Error|array
	 */
	public function remove_custom_domain() {
		return $this->delete_request( 'workspace/domain' );
	}

	/**
	 * Get custom domain
	 *
	 * @return \WP_Error|array
	 */
	public function get_custom_domain() {
		return $this->get_request( 'workspace/domain' );
	}

}
