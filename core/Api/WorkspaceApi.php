<?php

namespace Dollie\Core\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

trait WorkspaceApi {
	use Api;

	/**
	 * Get custom domain
	 *
	 * @return \WP_Error|array
	 */
	public function get_custom_domain() {
		return $this->get_request( 'workspace/domain' );
	}

}
