<?php

namespace Dollie\Core\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Utils\ConstInterface;

final class AuthService extends Singleton implements ConstInterface {
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
				[
					'origin' => admin_url(),
					'site_data' => base64_encode(
						json_encode(
							[
								'username' => wp_get_current_user()->user_login,
								'email' => wp_get_current_user()->user_email,
							]
						)
					),
				],
				DOLLIE_CONTROL_URL . 'auth'
			),
			__( 'Connect to Dollie Control HQ', 'dollie' )
		);
	}

	/**
	 * Get auth link
	 *
	 * @return string
	 */
	public function get_auth_link() {
		return sprintf(
			'%s',
			add_query_arg(
				[
					'origin' => admin_url()
				],
				DOLLIE_CONTROL_URL . 'auth'
			)
		);
	}

	/**
	 * Process token
	 *
	 * @return void
	 */
	public function process_token() {
		if ( ! isset( $_GET['dollie_data'] ) ) {
			return;
		}

		$data = @base64_decode( $_GET['dollie_data'] );

		$data = @json_decode( $data, true );

		if ( ! is_array( $data ) || ! isset( $data['token'], $data['domain'] ) || ! $data['token'] || ! $data['domain'] ) {
			return;
		}

		delete_transient( 'wpd_partner_subscription' );

		$this->update_token( $data['token'] );

		update_option( 'options_wpd_api_domain', sanitize_text_field( $data['domain'] ) );

		wp_redirect( admin_url( 'admin.php?page=dollie_setup' ) );
		die();
	}

	/**
	 * Check if token is saved.
	 *
	 * @return boolean
	 */
	public function is_connected(): bool {
		return (bool) $this->get_token();
	}


	/**
	 * If token is saved and subscription check went fine.
	 *
	 * @return false|mixed|null
	 */
	public function is_connected_and_token_valid() {
		return get_option( 'wpd_connected' );
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
