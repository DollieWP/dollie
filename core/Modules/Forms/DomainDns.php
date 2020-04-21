<?php

namespace Dollie\Core\Modules\Forms;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Log;
use Dollie\Core\Modules\Backups;
use Dollie\Core\Modules\Forms;
use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;

/**
 * Class DomainDns
 * @package Dollie\Core\Modules\Forms
 */
class DomainDns extends Singleton {

	private $form_key = 'form_dollie_domain_dns_ssl';

	/**
	 * DomainDns constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'acf/init', [ $this, 'acf_init' ] );

	}

	public function acf_init() {

		// Form args
		add_filter( 'af/form/args/key=' . $this->form_key, [ $this, 'change_form_args' ] );
		add_filter( 'af/form/attributes/key=' . $this->form_key, [ $this, 'change_form_attributes' ] );

		// Restrictions
		add_filter( 'af/form/restriction/key=' . $this->form_key, [ $this, 'restrict_form' ], 10 );

		// Form submission/validation actions.
		add_action( 'af/form/validate/key=' . $this->form_key, [ $this, 'validate_form' ], 10, 2 );
		add_action( 'af/form/before_submission/key=' . $this->form_key, [ $this, 'submission_callback' ], 10, 3 );

	}


	public function submission_callback( $form, $fields, $args ) {

		$container = Forms::get_form_container();

		if ( $container === false ) {
			return;
		}

		do_action( 'dollie/domain/dns/submission/after', $container );

	}

	public function validate_form( $form, $args ) {

		$container = Forms::get_form_container();

		if ( $container === false ) {
			return;
		}

		$is_dns_changed  = af_get_field( 'is_dns_changed' );
		$ssl_certificate = af_get_field( 'ssl_certificate_type' );

		$current_page = 1;
		if ( $ssl_certificate !== false ) {
			$current_page = 2;
		}

		$domain = get_post_meta( $container->id, 'wpd_domains', true );
		$ip     = get_post_meta( $container->id, 'wpd_container_ip', true ) ?: '';


		// Validate DNS record.
		if ( $current_page === 1 ) {

			// Skip if we are using Cloudflare since it will give us a wrong IP address
			if ( dollie()->is_using_cloudflare( $domain ) ) {
				return;
			}

			$dns_valid = false;

			// Check IP record with Google DNS
			$dns_response = wp_remote_get( 'https://dns.google.com/resolve?name=' . $domain . '&type=A' );

			if ( ! is_wp_error( $dns_response ) ) {
				$dns_record = wp_remote_retrieve_body( $dns_response );
				$dns_record = @json_decode( $dns_record, true );

				if ( is_array( $dns_record ) && isset( $dns_record['Answer'][0]['data'] ) && $dns_record['Answer'][0]['data'] == $ip ) {
					$dns_valid = true;
				}
			}

			// Fallback for google DNS query
			if ( $dns_valid === false ) {
				$dns_record = dns_get_record( $domain, DNS_A );
				if ( isset( $dns_record[0]['ip'] ) && $dns_record[0]['ip'] == $ip ) {
					$dns_valid = true;
				}
			}

			if ( $dns_valid === false ) {
				af_add_error( 'is_dns_changed', esc_html__( 'Your domain DNS A record is not yet pointing to our IP address. Please wait a few minutes and try again.', 'dollie' ) );

				return;

			}
		} elseif ( $current_page === 2 ) {
			//Cloudflare setup || LetsEncrypt

			// Our form field ID + User meta fields
			$email   = af_get_field( 'cloudflare_email' );
			$api_key = af_get_field( 'cloudflare_api_key' );

			if ( $ssl_certificate === 'cloudflare' ) {

				// Set up the request to CloudFlare to verify
				$args   = [
					'method'  => 'GET',
					'timeout' => 45,
					'headers' => [
						'X-Auth-Email' => $email,
						'X-Auth-Key'   => $api_key,
						'Content-Type' => 'application/json',
					],
				];
				$update = wp_remote_post( 'https://api.cloudflare.com/client/v4/user', $args );

				// Parse the JSON request
				$answer   = wp_remote_retrieve_body( $update );
				$response = json_decode( $answer, true );

				// Throw an error if CloudFlare Details are incorrect.
				if ( $response['success'] === false ) {

					af_add_error( 'cloudflare_api_key', wp_kses_post( sprintf(
						__( 'Your CloudFlare Email or API key is incorrect. Please try again or <a href="%s">Contact Support</a>', 'dollie' ),
						'https://dollie.co/support-redirect'
					) ) );

					return;

				}

				if ( isset( $response['result']['id'] ) ) {

					$container_uri = get_post_meta( $container->id, 'wpd_container_uri', true );

					Api::post( Api::ROUTE_DOMAIN_INSTALL_CLOUDFLARE, [
						'container_url'  => $container_uri,
						'email'          => $email,
						'cloudflare_key' => $api_key,
						'dollie_domain'  => DOLLIE_INSTALL,
						'dollie_token'   => Api::get_dollie_token(),
					] );

					// All done, update user meta!
					update_post_meta( $container->id, 'wpd_cloudflare_email', $email );
					update_post_meta( $container->id, 'wpd_cloudflare_active', 'yes' );
					update_post_meta( $container->id, 'wpd_cloudflare_id', $response['result']['id'] );
					update_post_meta( $container->id, 'wpd_cloudflare_api', $api_key );

					Log::add( $container->slug . ' linked up CloudFlare account' );
				}

				//CloudFlare Zone ID
				$cloudflare_zone_id = af_get_field( 'cloudflare_zone_id' );

				// Set up the request to CloudFlare to verify.
				$update = wp_remote_post( 'https://api.cloudflare.com/client/v4/zones/' . $cloudflare_zone_id, [
					'method'  => 'GET',
					'timeout' => 45,
					'headers' => [
						'X-Auth-Email' => $email,
						'X-Auth-Key'   => $api_key,
						'Content-Type' => 'application/json',
					],
				] );

				// Parse the JSON request
				$answer   = wp_remote_retrieve_body( $update );
				$response = json_decode( $answer, true );

				// Throw an error if CloudFlare Details are incorrect.
				if ( $response['success'] === false ) {

					af_add_error( 'cloudflare_zone_id', wp_kses_post( sprintf(
						__( 'Your CloudFlare Zone ID is incorrect. Please make sure you copy and pasted the right ID without extra spaces. Need help? <a href="%s">Contact Support</a>', 'dollie' ),
						dollie()->get_support_link()
					) ) );

					return;

				}

				// Save our CloudFlare Zone ID to user meta.
				update_post_meta( $container->id, 'wpd_cloudflare_zone_id', $cloudflare_zone_id );
				Log::add( 'CloudFlare Zone ID ' . $cloudflare_zone_id . ' is used for analytics for ' . $container->slug );

			} else {

				// We use LetsEncrypt.
				update_post_meta( $container->id, 'wpd_letsencrypt_enabled', 'yes' );

				$request_le = Api::post( Api::ROUTE_DOMAIN_INSTALL_LETSENCRYPT, [
					'container_id'  => get_post_meta( $container->id, 'wpd_container_id', true ),
					'route_id'      => get_post_meta( $container->id, 'wpd_domain_id', true ),
					'dollie_domain' => DOLLIE_INSTALL,
					'dollie_token'  => Api::get_dollie_token(),
				] );

				$response_le = API::process_response( $request_le );

				// Show an error of S5 API can't add the Route.
				if ( $response_le === false ) {

					update_post_meta( $container->id, 'wpd_letsencrypt_setup_complete', 'yes' );

					af_add_error( 'domain_with_www', esc_html__( 'Sorry, We could not generate a SSL certificate for this domain. Please contact support so we can look into why this has happened.', 'dollie' ) );

					Log::add( 'Letsencrypt ssl wasn\'t generated for domain ' . $domain, print_r( $request_le, true ) );

					return;

				}

			}

		}

		do_action( 'dollie/domain/dns/validate/after', $domain, $ip );

	}

	/**
	 * If no updates, restrict the form and show a message
	 *
	 * @param bool $restriction
	 *
	 * @return bool|string
	 */
	public function restrict_form( $restriction = false ) {

		// Added in case another restriction already applies
		if ( $restriction ) {
			return $restriction;
		}

		if ( $this->is_form_restricted() ) {
			return '<div class="acf-hidden"></div>';
		}

		return $restriction;
	}

	public function change_form_args( $args ) {
		$args['submit_text'] = esc_html__( 'Complete Domain Setup', 'dollie' );

		return $args;
	}

	public function change_form_attributes( $atts ) {
		if ( $this->is_form_restricted() ) {
			$atts['class'] .= ' acf-hidden';
		}

		return $atts;
	}

	private function is_form_restricted() {

		$container      = dollie()->get_current_object();
		$has_domain     = get_post_meta( $container->id, 'wpd_domains', true );
		$has_cloudflare = get_post_meta( $container->id, 'wpd_cloudflare_email', true );
		$has_analytics  = get_post_meta( $container->id, 'wpd_cloudflare_zone_id', true );
		$has_le         = get_post_meta( $container->id, 'wpd_letsencrypt_enabled', true );

		// If it already has SSL return an empty space as message
		return ! $has_domain || $has_analytics || $has_le || $has_cloudflare;
	}

}
