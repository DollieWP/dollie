<?php

namespace Dollie\Core\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;

final class AuthService extends Singleton {
	/**
	 * Get auth url
	 *
	 * @param bool $button
	 *
	 * @return string
	 */
	public function get_auth_url() {
		return sprintf(
			'<a href="%s" class="button">%s</a>',
			add_query_arg(
				[ 'origin' => admin_url() ],
				DOLLIE_PARTNERS_URL . 'auth'
			),
			__( 'Connect with Dollie API', 'dollie' )
		);
	}

	/**
	 * Get
	 *
	 * @return string
	 */
	public function get_token(): string {
		return get_option( 'dollie_auth_token', '' );
	}

	/**
	 * Update
	 *
	 * @param string $token
	 *
	 * @return void
	 */
	public function update_token( string $token ): void {
		update_option( 'dollie_auth_token', $token );
	}

	/**
	 * Delete
	 *
	 * @return void
	 */
	public function delete_token(): void {
		delete_option( 'dollie_auth_token' );
	}
}
