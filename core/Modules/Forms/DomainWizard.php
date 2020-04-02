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
 * Class DomainWizard
 * @package Dollie\Core\Modules\Forms
 */
class DomainWizard extends Singleton {

	private $form_key = 'form_dollie_domain_wizard';

	/**
	 * DomainWizard constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'acf/init', [ $this, 'acf_init' ] );

		// TODO hide pages
		add_filter( 'af/field/before_render', function ( $field, $form, $args ) {

			return $field;

			$container = Forms::get_form_container();

			$has_domain     = get_post_meta( $container->id, 'wpd_domains', true );
			$has_cloudflare = get_post_meta( $container->id, 'wpd_cloudflare_email', true );
			$has_analytics  = get_post_meta( $container->id, 'wpd_cloudflare_zone_id', true );
			$has_le         = get_post_meta( $container->id, 'wpd_letsencrypt_enabled', true );

			if ( $has_cloudflare && ! $has_analytics ) {
				//form_page=3
			}

			if ( $has_domain && $has_le ) {
				// form_page=4
			}

			if ( $has_domain && ! $has_analytics ) {

				// Force page 2
				if ( $field['name'] === '' ) {
					$field['conditional_logic'] = 1;
				}

			}

			if ( $has_analytics ) {
				//form_page=4
			}

			return $field;
		}, 10, 3 );

	}

	public function acf_init() {

		// Form args
		add_filter( 'af/form/args/key=' . $this->form_key, [ $this, 'change_form_args' ] );

		// Form submission action.
		add_action( 'af/form/validate/key=' . $this->form_key, [ $this, 'validate_form' ], 10, 2 );

		add_action( 'af/form/before_submission/key=' . $this->form_key, [ $this, 'submission_callback' ], 10, 3 );

	}


	public function submission_callback( $form, $fields, $args ) {

		$container = Forms::get_form_container();

		if ( $container === false ) {
			return;
		}

		// TODO Check if is ok to Remove this
		// update_post_meta( $container->id, 'wpd_cloudflare_active', 'yes' );

		// Update user meta used to show/hide specific Dashboard areas/tabs.
		update_post_meta( $container->id, 'wpd_domain_migration_complete', 'yes' );

		// Log our success
		Log::add( $container->slug . ' domain setup completed. Using live real domain from this point onwards.' );

		// Make a backup.
		Backups::instance()->trigger_backup();

		// Update our container details so that the new domain will be used to make container HTTP requests.
		dollie()->flush_container_details();

	}

	public function validate_form( $form, $args ) {

		$container          = Forms::get_form_container();
		$domain             = af_get_field( 'domain_name' );
		$ssl_certificate    = af_get_field( 'ssl_certificate' );
		$cloudflare_zone_id = af_get_field( 'cloudflare_zone_id' );
		$domain_with_www    = af_get_field( 'domain_with_www' );

		$current_page = 0;
		if ( $domain !== false ) {
			$current_page = 1;
		} elseif ( $ssl_certificate !== false ) {
			$current_page = 2;
		} elseif ( $cloudflare_zone_id !== false ) {
			$current_page = 3;
		} elseif ( $domain_with_www !== false ) {
			$current_page = 5;
		}

		// Check if doamin inputs are visible
		if ( af_get_field( 'is_domain_registered' ) === 'no' && af_get_field( 'is_new_domain_registered' ) === false ) {
			af_add_error( 'is_new_domain_registered', esc_html__( 'You need to have a registered domain before you continue.', 'dollie' ) );
		}

		// Check if domain is confirmed
		if ( af_get_field( 'domain_name' ) !== af_get_field( 'confirm_domain_name' ) ) {
			af_add_error( 'confirm_domain_name', esc_html__( 'Your domain names do not match.', 'dollie' ) );

		} elseif ( $current_page === 1 ) { // Add domain route if we are on first page

			// Extra check and see if the same domain is already linked - skip
			$saved_domain = get_post_meta( $container->id, 'wpd_domains', true );
			if ( $saved_domain === $domain ) {
				return;
			}

			$request = dollie()->get_customer_container_details( $container->id );

			$request_route_add = Api::post( Api::ROUTE_DOMAIN_ROUTES_ADD, [
				'container_id'  => $request->id,
				'domain'        => $domain,
				'dollie_domain' => DOLLIE_INSTALL,
				'dollie_token'  => Api::getDollieToken(),
			] );

			$response_data = API::process_response( $request_route_add );

			// Show an error if API can't add the Route.
			if ( ! $response_data || ! array_key_exists( 'path', $response_data ) ) {

				af_add_error( 'domain_name', wp_kses_post( sprintf(
					__( 'Sorry, We could not link this domain to your site. This could be because the domain is already registered for another site in our network. It could also be an issue on our end! Please try again or <a href="%s">Contact Support</a>', 'dollie' ),
					'https://dollie.co/support-redirect'
				) ) );

				Log::add( $container->slug . ' could not link domain ' . $domain, print_r( $request_route_add, true ) );

				return;
			}

			// Save the Domain Data and make another S5 Request for the WWW domain.
			update_post_meta( $container->id, 'wpd_domain_id', $response_data['id'] );
			update_post_meta( $container->id, 'wpd_domains', $domain );

			$request_route_add_www = Api::post( Api::ROUTE_DOMAIN_ROUTES_ADD, [
				'container_id'  => $request->id,
				'domain'        => 'www.' . $domain,
				'dollie_domain' => DOLLIE_INSTALL,
				'dollie_token'  => Api::getDollieToken(),
			] );

			$response_data_www = API::process_response( $request_route_add_www );

			if ( $request_route_add_www ) {
				// Also save the www Domain data.
				update_post_meta( $container->id, 'wpd_www_domain_id', $response_data_www['id'] );
				Log::add( $container->slug . ' linked up domain ' . $domain );
			} else {
				Log::add( $container->slug . ' could not link www domain ' . $domain, print_r( $request_route_add_www, true ) );
			}

			do_action( 'dollie/domain_wizard/link_domain/after', $container, $domain );

		} elseif ( $current_page === 2 ) { //CloudFlare check

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

				} elseif ( isset( $response['result']['id'] ) ) {

					$container_uri = get_post_meta( $container->id, 'wpd_container_uri', true );

					Api::post( Api::ROUTE_DOMAIN_INSTALL_CLOUDFLARE, [
						'container_url'  => $container_uri,
						'email'          => $email,
						'cloudflare_key' => $api_key,
						'dollie_domain'  => DOLLIE_INSTALL,
						'dollie_token'   => Api::getDollieToken(),
					] );

					// All done, update user meta!
					update_post_meta( $container->id, 'wpd_cloudflare_email', $email );
					update_post_meta( $container->id, 'wpd_cloudflare_active', 'yes' );
					update_post_meta( $container->id, 'wpd_cloudflare_id', $response['result']['id'] );
					update_post_meta( $container->id, 'wpd_cloudflare_api', $api_key );
					Log::add( $container->slug . ' linked up CloudFlare account' );
				}

			} else {
				update_post_meta( $container->id, 'wpd_letsencrypt_enabled', 'yes' );
			}
		} elseif ( $current_page === 3 && $ssl_certificate === 'cloudflare' ) { // Cloudflare zone

			// Our form field ID + User meta fields
			$email   = af_get_field( 'cloudflare_email' );
			$api_key = af_get_field( 'cloudflare_api_key' );

			// Set up the request to CloudFlare to verify
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
					'https://dollie.co/support-redirect'
				) ) );

				return;

			}

			// Save our CloudFlare Zone ID to user meta.
			update_post_meta( $container->id, 'wpd_cloudflare_zone_id', $cloudflare_zone_id );
			Log::add( 'CloudFlare Zone ID ' . $cloudflare_zone_id . ' is used for analytics for ' . $container->slug );

		} elseif ( $current_page === 5 ) { // Search and replace in database

			if ( $domain_with_www === 'yes' ) {
				$domain = 'www.' . get_post_meta( $container->id, 'wpd_domains', true );
			} else {
				$domain = get_post_meta( $container->id, 'wpd_domains', true );
			}

			$requestDomainUpdate = Api::post( Api::ROUTE_DOMAIN_UPDATE, [
				'container_uri' => get_post_meta( $container->id, 'wpd_container_uri', true ),
				'domain'        => $domain,
				'dollie_domain' => DOLLIE_INSTALL,
				'dollie_token'  => Api::getDollieToken(),
			] );

			$responseDomainUpdate = json_decode( wp_remote_retrieve_body( $requestDomainUpdate ), true );
			$responseData         = json_decode( $responseDomainUpdate, true );

			Log::add( 'Search and replace ' . $container->slug . ' to update URL to ' . $domain . ' has started', $responseData );

			$le = get_post_meta( $container->id, 'wpd_letsencrypt_enabled', true );
			if ( $le === 'yes' ) {

				$requestLetsEncrypt = Api::post( Api::ROUTE_DOMAIN_INSTALL_LETSENCRYPT, [
					'container_id'  => get_post_meta( $container->id, 'wpd_container_id', true ),
					'route_id'      => get_post_meta( $container->id, 'wpd_domain_id', true ),
					'dollie_domain' => DOLLIE_INSTALL,
					'dollie_token'  => Api::getDollieToken(),
				] );

				$responseLetsEncrypt = API::process_response( $requestLetsEncrypt );

				// Show an error of S5 API can't add the Route.
				if ( $responseLetsEncrypt === false ) {

					af_add_error( 'domain_with_www', esc_html__( 'Sorry, We could not generate a SSL certificate for this domain. Please contact support so we can look into why this has happened.', 'dollie' ) );

					return;

				}

				update_post_meta( $container->id, 'wpd_letsencrypt_setup_complete', 'yes' );

			}

			// We will add an artificial delay because if we're dealing with a big database it could take a bit of time to run the search and replace via the Worker/WP-CLI command.
			sleep( 20 );
		}
	}

	public function change_form_args( $args ) {
		$args['submit_text'] = esc_html__( 'Complete Domain Setup', 'dollie' );

		return $args;
	}

}
