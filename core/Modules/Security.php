<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Modules\Sites\WP;
use Dollie\Core\Singleton;
use WP_Http;

/**
 * Class Security
 *
 * @package Dollie\Core\Modules
 */
class Security extends Singleton {

	/**
	 * Security constructor.
	 */
	public function __construct() {
		parent::__construct();

		if ( get_option( 'options_wpd_wpvulndb_token' ) ) {
			add_action( 'template_redirect', [ $this, 'plugin_scanner' ], 99 );
			add_action( 'template_redirect', [ $this, 'run' ], 1 );
		}
	}

	/**
	 * Get vulnerable plugins
	 *
	 * @return array
	 */
	public function get_vulnerable_plugins() {
		$token = get_option( 'options_wpd_wpvulndb_token' );

		// Now that we have our container details get our secret key
		$details_url          = dollie()->get_container_url() . WP::PLATFORM_PATH . 'container/details/stats.php';
		$details_transient_id = dollie()->get_current_object()->slug . '_get_container_site_info';
		$details_username     = 'container';

		// Make the request.
		$details_request = dollie()->container_api_request( $details_url, $details_transient_id, $details_username );

		// Encode to JSON (not used yet)
		$container_data = json_encode( $details_request );

		// Decode to PHP currently used
		$container_details = json_decode( $container_data, true );

		$all_plugins = $container_details['Plugin Details'];

		$vulnerabilities = [];

		$args['headers'] = [
			'Authorization' => 'Token token=' . $token,
			'Content-Type'  => 'application/json',
		];

		$request = new WP_Http();

		foreach ( $all_plugins as $name => $details ) {
			// get unique name
			if ( preg_match( '|(.+)/|', $name, $matches ) ) {
				$plugin_key = $matches[1];
				$result     = $request->request( 'https://wpvulndb.com/api/v3/plugins/' . $plugin_key, $args );

				if ( is_wp_error( $result ) ) {
					trigger_error( $result->get_error_message(), E_USER_ERROR );
				} elseif ( $result['body'] ) {
					$plugin = json_decode( $result['body'] );

					if ( isset( $plugin->$plugin_key->vulnerabilities ) ) {
						foreach ( $plugin->$plugin_key->vulnerabilities as $vuln ) {
							if ( ! isset( $vuln->fixed_in ) ||
							     version_compare( $details['Version'], $vuln->fixed_in, '<' ) ) {
								$vulnerabilities[ $name ][] = $vuln;
							}
						}
					}
				}
			}
		}

		return $vulnerabilities;
	}

	/**
	 * Plugin security scanner
	 */
	public function plugin_scanner() {
		if ( is_singular( 'container' ) ) {
			$current_query = dollie()->get_current_object();

			$transient = get_transient( 'dollie_security_check_' . $current_query->slug );

			if ( 'done' !== $transient ) {
				set_transient( 'dollie_security_check_' . $current_query->slug, 'done', MINUTE_IN_SECONDS * 3600 );
				$mail_body = '';

				// run scan
				$vulnerability_count = 0;
				$vulnerabilities     = $this->get_vulnerable_plugins();

				foreach ( $vulnerabilities as $plugin_name => $plugin_vulnerabilities ) {
					foreach ( $plugin_vulnerabilities as $vuln ) {
						$mail_body .= 'Update: ' . $vuln->title . "\n";
						$vulnerability_count ++;
					}
				}

				// if vulns, email admin
				if ( $vulnerability_count ) {
					set_transient( 'dollie_security_check_failed_' . $current_query->slug, 'failed', MINUTE_IN_SECONDS * 3600 );
					$mail_body .= '' . sprintf(
							_n(
								'%s vulnerability found.',
								'%s vulnerabilities found.',
								$vulnerability_count,
								'plugin-security-scanner'
							),
							$vulnerability_count
						) . "\n";
					set_transient( 'dollie_security_check_message_' . $current_query->slug, $mail_body, MINUTE_IN_SECONDS * 3600 );
				}
			}
		}
	}

	/**
	 * Run security check
	 */
	public function run() {
		if ( isset( $_GET['run-security-check'] ) ) {
			$current_query = dollie()->get_current_object();

			delete_transient( 'dollie_security_check_' . $current_query->slug );
			delete_transient( 'dollie_security_check_failed_' . $current_query->slug );
			delete_transient( 'dollie_security_check_failed_' . $current_query->slug );
			delete_transient( 'dollie_container_api_request_' . $current_query->slug . '_get_customer_site_info' );
			wp_redirect( trailingslashit( get_permalink() ) . 'plugins' );

			exit;
		}
	}

}
