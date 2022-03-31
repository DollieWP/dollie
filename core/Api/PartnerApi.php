<?php

namespace Dollie\Core\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

trait PartnerApi {
	use Api;

	/**
	 * Get subscription
	 *
	 * @param string $site_id
	 *
	 * @return \WP_Error|array
	 */
	public function get_partner_subscription(): \WP_Error|array {
		return $this->get_request( 'partner/subscription' );
	}

	/**
	 * Set option
	 *
	 * @param array $data
	 *
	 * @return \WP_Error|array
	 */
	public function set_partner_option( array $data ): \WP_Error|array {
		return $this->post_request( 'partner/option', $data );
	}
}
