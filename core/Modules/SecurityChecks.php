<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Utils\Helpers;
use WP_Http;

/**
 * Class SecurityChecks
 * @package Dollie\Core\Modules
 */
class SecurityChecks extends Singleton {

	/**
	 * @var \stdClass
	 */
	protected $currentQuery;

	/**
	 * SecurityChecks constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->currentQuery = dollie()->helpers()->currentQuery;

		if ( get_option( 'options_wpd_wpvulndb_token' ) ) {
			add_action( 'template_redirect', [ $this, 'plugin_security_scanner_do_this_daily' ], 99 );
			add_action( 'template_redirect', [ $this, 'run_security_check' ], 1 );
		}
	}

	public function get_vulnerable_plugins() {
		$token = get_option( 'options_wpd_wpvulndb_token' );

		// Now that we have our container details get our secret key
		$details_url          = dollie()->helpers()->get_container_url() . '/wp-content/mu-plugins/platform/container/details/stats.php';
		$details_transient_id = 'get_container_site_info';
		$details_username     = 'container';
		//Make the request
		$details_request = dollie()->helpers()->container_api_request( $details_url, $details_transient_id, $details_username, $details_pass );
		//Encode to JSON (not used yet)
		$container_data = json_encode( $details_request );

		//Decode to PHP currently used
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
				} else {
					if ( $result['body'] ) {
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
		}

		return $vulnerabilities;
	}

	public function plugin_security_scanner_do_this_daily() {
		if ( is_singular( 'container' ) ) {
			$transient = get_transient( 'dollie_security_check_' . $this->currentQuery->slug );

			if ( $transient !== 'done' ) {
				set_transient( 'dollie_security_check_' . $this->currentQuery->slug, 'done', MINUTE_IN_SECONDS * 3600 );
				$mail_body = '';

				// run scan
				$vulnerability_count = 0;
				$vulnerabilities     = $this->get_vulnerable_plugins();

				foreach ( $vulnerabilities as $plugin_name => $plugin_vulnerabilities ) {
					foreach ( $plugin_vulnerabilities as $vuln ) {
						$mail_body .= __( '', 'plugin-security-scanner' ) . 'Update: ' . $vuln->title . "\n";
						$vulnerability_count ++;
					}
				}

				// if vulns, email admin
				if ( $vulnerability_count ) {
					set_transient( 'dollie_security_check_failed_' . $this->currentQuery->slug, 'failed', MINUTE_IN_SECONDS * 3600 );
					$mail_body .= '' . sprintf(
							_n(
								'%s vulnerability found.',
								'%s vulnerabilities found.',
								$vulnerability_count, 'plugin-security-scanner'
							), $vulnerability_count
						) . "\n";
					set_transient( 'dollie_security_check_message_' . $this->currentQuery->slug, $mail_body, MINUTE_IN_SECONDS * 3600 );
				}
			}
		}
	}

	public function run_security_check() {
		if ( isset( $_GET['run-security-check'] ) ) {
			delete_transient( 'dollie_security_check_' . $this->currentQuery->slug );
			delete_transient( 'dollie_security_check_failed_' . $this->currentQuery->slug );
			delete_transient( 'dollie_security_check_failed_' . $this->currentQuery->slug );
			delete_transient( 'dollie_container_api_request_' . $this->currentQuery->slug . '_get_customer_site_info' );
			wp_redirect( get_permalink() . '#plugins' );

			exit;
		}
	}

}
