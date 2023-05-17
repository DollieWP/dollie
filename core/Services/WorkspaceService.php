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
	 *
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

		$custom_domain_enabled = get_option( 'options_wpd_show_custom_domain_options' );
		$domain                = str_replace( [
			'https://',
			'http://',
			'www.'
		], '', get_option( 'options_wpd_api_domain_custom' ) );

		if ( ! $custom_domain_enabled ) {
			return;
		}

		// if the domain has been already set then it was already checked. we can stop here.
		if ( $domain && $domain === get_option( 'wpd_deployment_domain' ) ) {
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

	public function acf_populate_active_domains( $field ) {

		$domains = get_transient( 'wpd_custom_domains' ) !== false ? get_transient( 'wpd_custom_domains' ) : [];

		if ( ! $domains ) {
			$response = $this->get_custom_domain();
			if ( ! is_wp_error( $response ) ) {

				foreach ( $response['all'] as $domain ) {
					$domains[ $domain['name'] ] = $domain['name'];
				}
				set_transient( 'wpd_custom_domains', $domains, MINUTE_IN_SECONDS * 10 );
			}
		}

		$field['choices'] = $domains;

		return $field;
	}
}
