<?php

namespace Dollie\Core\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

trait ActionApi {
	use Api;

	/**
	 * Get bulk
	 *
	 * @param array $container_hashes
	 *
	 * @return \WP_Error|array
	 */
	public function get_bulk_actions( array $container_hashes ) {
		$query_string = '';

		foreach ( $container_hashes as $hash ) {
			$query_string .= "container_hash[]={$hash}";
		}

		return $this->get_request( "actions/bulk?{$query_string}" );
	}

	/**
	 * Create bulk
	 *
	 * @param array $data
	 *
	 * @return \WP_Error|array
	 */
	public function create_bulk_action( array $data ) {
		return $this->post_request( 'actions/bulk', $data );
	}

	/**
	 * Get recurring
	 *
	 * @return \WP_Error|array
	 */
	public function get_recurring_actions() {
		return $this->get_request( 'actions/recurring' );
	}

	/**
	 * Create recurring
	 *
	 * @param array $data
	 *
	 * @return \WP_Error|array
	 */
	public function create_recurring_actions( array $data ) {
		return $this->post_request( 'actions/recurring', $data );
	}

	/**
	 * Delete recurring
	 *
	 * @param string $container_hash
	 * @param string $action_id
	 *
	 * @return \WP_Error|array
	 */
	public function delete_recurring_action( string $container_hash, string $action_id ) {
		return $this->delete_request( "actions/recurring/{$container_hash}/{$action_id}" );
	}
}
