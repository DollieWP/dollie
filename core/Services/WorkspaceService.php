<?php

namespace Dollie\Core\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Api\WorkspaceApi;

final class WorkspaceService extends Singleton {
	use WorkspaceApi;

	/**
	 * Check if custom deployment domain is active and set
	 *
	 * @return boolean
	 */
	public function has_custom_deployment_domain() {
		if ( ! get_option( 'wpd_deployment_domain_status' ) ) {
			return false;
		}

		return (bool) get_option( 'wpd_deployment_domain', '' );
	}

	/**
	 * Get deployment domain
	 *
	 * @return string
	 */
	public function get_deployment_domain() {
		$default_domain = get_option( 'options_wpd_api_domain' );
		$domain         = get_option( 'wpd_deployment_domain', $default_domain );

		if ( ! $domain || ! get_option( 'wpd_deployment_domain_status' ) ) {
			return $default_domain;
		}

		return str_replace( [ 'http://', 'https://', 'www.' ], '', rtrim( $domain, '/' ) );
	}

	/**
	 * Add custom deployment domain
	 *
	 * @param string $domain
	 * @return boolean
	 */
	public function add_deployment_domain( string $domain = '' ) {
		$response = $this->add_custom_domain( $domain );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		if ( $domain ) {
			delete_option( 'wpd_deployment_domain_notice' );
		} else {
			delete_option( 'wpd_deployment_domain' );
			delete_option( 'wpd_deployment_domain_status' );
			delete_option( 'wpd_deployment_domain_notice' );
		}

		return true;
	}

	/**
	 * Check deployment domain
	 *
	 * @return void
	 */
	public function check_deployment_domain() {
		if ( ! dollie()->auth()->is_connected() ) {
			return;
		}

		$response = get_transient( 'wpd_workspace_domain_check' );

		if ( ! $response || is_wp_error( $response ) ) {
			$response = $this->get_custom_domain();

			set_transient( 'wpd_workspace_domain_check', $response, MINUTE_IN_SECONDS * 10 );
		}

		if ( is_wp_error( $response ) ) {
			return;
		}

		if ( $response['domain'] ) {
			update_option( 'wpd_deployment_domain', $response['domain'] );
		}

		update_option( 'wpd_deployment_domain_status', $response['status'] );
	}
}
