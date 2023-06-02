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
		$domain = $this->get_deployment_domain();
		if ( empty( $domain ) ) {
			return false;
		}

		return strpos( $this->get_deployment_domain(), 'dollie.io' ) === false;
	}

	/**
	 * Get deployment domain
	 *
	 * @return string
	 */
	public function get_deployment_domain() {
		$domain = get_option( 'options_wpd_api_domain', '' );
		if ( empty( $domain ) ) {
			return $this->get_primary_domain();
		}

		return $domain;
	}

	public function acf_populate_active_domains( $field ) {

		$domains = [];

		foreach ( $this->get_active_domains() as $domain ) {
			$domains[ $domain['name'] ] = $domain['name'];
		}

		if ( empty( $domains ) ) {
			$domains[] = $this->get_deployment_domain();
		}

		$field['choices'] = $domains;

		return $field;
	}

	private function get_active_domains() {
		$domains = get_transient( 'wpd_custom_domains' ) !== false ? get_transient( 'wpd_custom_domains' ) : [];

		if ( ! $domains ) {
			$response = $this->get_custom_domain();
			if ( ! is_wp_error( $response ) && isset( $response['all'] ) && ! empty( $response['all'] ) ) {
				$domains = $response['all'];
				set_transient( 'wpd_custom_domains', $domains, MINUTE_IN_SECONDS * 10 );
			}
		}

		return $domains;

	}

	/**
	 * Get primary HQ domain
	 * @return false|mixed
	 */
	private function get_primary_domain() {
		foreach ( $this->get_active_domains() as $active_domain ) {
			if ( $active_domain['primary'] ) {
				return $active_domain['name'];
			}
		}

		return false;
	}

}