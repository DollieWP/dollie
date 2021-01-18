<?php

namespace Dollie\Core\Modules\Forms;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Log;
use Dollie\Core\Modules\Backups;
use Dollie\Core\Modules\Container;
use Dollie\Core\Modules\Forms;
use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;

/**
 * Class DomainConnect
 *
 * @package Dollie\Core\Modules\Forms
 */
class DomainConnect extends Singleton {

	/**
	 * @var string
	 */
	private $form_key = 'form_dollie_domain_connect';

	/**
	 * DomainConnect constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'acf/init', [ $this, 'acf_init' ] );
	}

	/**
	 * Init ACF
	 */
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

	/**
	 * Callback
	 *
	 * @param $form
	 * @param $fields
	 * @param $args
	 */
	public function submission_callback( $form, $fields, $args ) {
		$container = Forms::get_form_container();

		if ( false === $container ) {
			return;
		}

		$container = Forms::get_form_container();
		$domain    = af_get_field( 'domain_name' );

		// Extra check and see if the same domain is already linked - skip
		$saved_domain = get_post_meta( $container->id, 'wpd_domains', true );

		if ( $saved_domain === $domain ) {
			return;
		}

		$request = dollie()->get_customer_container_details( $container->id );

		$request_route_add = Api::post(
			Api::ROUTE_DOMAIN_ROUTES_ADD,
			[
				'container_id' => $request->id,
				'route'        => $domain,
			]
		);

		$response_data = Api::process_response( $request_route_add );

		// Show an error if API can't add the Route.
		if ( ! $response_data || ! isset( $response_data['id'] ) ) {

			af_add_submission_error(
				wp_kses_post(
					sprintf(
						__( 'Sorry, we could not link this domain to your site. This could be because the domain is already registered for another site in our network. It could also be an issue on our end! Please try again or <a href="%s">Contact Support</a>', 'dollie' ),
						get_option('options_wpd_support_link')
					)
				)
			);

			Log::add_front(
				Log::WP_SITE_DOMAIN_LINK_ERROR,
				$container,
				[
					$domain,
					$container->slug,
				],
				print_r( $request_route_add, true )
			);

			return;
		}

		// Save the Domain Data and make another Request for the www domain.
		update_post_meta( $container->id, 'wpd_domain_id', $response_data['id'] );
		update_post_meta( $container->id, 'wpd_domains', $domain );

		$request_route_add_www = Api::post(
			Api::ROUTE_DOMAIN_ROUTES_ADD,
			[
				'container_id' => $request->id,
				'route'        => 'www.' . $domain,
			]
		);

		$response_data_www = Api::process_response( $request_route_add_www );

		if ( $request_route_add_www && isset( $response_data_www['id'] ) ) {

			// Also save the www Domain data.
			update_post_meta( $container->id, 'wpd_www_domain_id', $response_data_www['id'] );

		} else {
			Log::add_front(
				Log::WP_SITE_DOMAIN_LINK_ERROR,
				$container,
				[
					'www. ' . $domain,
					$container->slug,
				],
				print_r( $request_route_add_www, true )
			);
		}

		// We use LetsEncrypt by default.
		update_post_meta( $container->id, 'wpd_letsencrypt_enabled', 'yes' );

		// Search/replace site url
		$replace_url_action = Container::instance()->update_url_with_domain( $domain, $container->id );

		if ( false === $replace_url_action ) {
			af_add_submission_error( __( 'There was a problem when performing the request. Please contact support so we can look into why this has happened.', 'dollie' ) );

			return;
		}

		// Make a backup.
		Backups::instance()->make();

		Log::add_front( Log::WP_SITE_DOMAIN_LINKED, $container, [ $domain, $container->slug ] );

		// Update our container details so that the new domain will be used to make container HTTP requests.
		dollie()->flush_container_details();

		do_action( 'dollie/domain/connect/submission/after', $container, $domain );

	}

	/**
	 * Validate form
	 *
	 * @param $form
	 * @param $args
	 */
	public function validate_form( $form, $args ) {
		$container = Forms::get_form_container();

		if ( false === $container ) {
			af_add_error( 'domain_name', __( 'We are sorry but an error occurred. Please try again or contact support!', 'dollie' ) );

			return;
		}

		$domain = af_get_field( 'domain_name' );
		$ip     = dollie()->get_wp_site_data( 'ip', $container->id );

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
		if ( false === $dns_valid ) {
			$dns_record = dns_get_record( $domain, DNS_A );
			if ( isset( $dns_record[0]['ip'] ) && $dns_record[0]['ip'] == $ip ) {
				$dns_valid = true;
			}
		}

		if ( false === $dns_valid ) {
			af_add_error( 'domain_name', __( 'Your domain DNS A record is not yet pointing to our IP address. Please wait a few minutes and try again.', 'dollie' ) );

			return;
		}

		do_action( 'dollie/domain/dns/validate/after', $domain, $ip );

		do_action( 'dollie/domain/connect/validate/after' );
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

	/**
	 * Change form args
	 *
	 * @param $args
	 *
	 * @return mixed
	 */
	public function change_form_args( $args ) {
		$args['submit_text'] = __( 'Connect Domain', 'dollie' );

		return $args;
	}

	/**
	 * Change form attributes
	 *
	 * @param $atts
	 *
	 * @return mixed
	 */
	public function change_form_attributes( $atts ) {
		if ( $this->is_form_restricted() ) {
			$atts['class'] .= ' acf-hidden';
		}

		return $atts;
	}

	/**
	 * Check if form is restricted
	 *
	 * @return bool
	 */
	private function is_form_restricted() {
		$container = dollie()->get_current_object();

		// If it already has linked domain and return an empty space as message
		if ( get_post_meta( $container->id, 'wpd_domains', true ) ) {
			return true;
		}

		$has_domain     = get_post_meta( $container->id, 'wpd_domains', true );
		$has_cloudflare = get_post_meta( $container->id, 'wpd_cloudflare_email', true );
		$has_analytics  = get_post_meta( $container->id, 'wpd_cloudflare_zone_id', true );
		$has_le         = get_post_meta( $container->id, 'wpd_letsencrypt_enabled', true );

		// If it already has SSL return an empty space as message
		return $has_analytics || $has_le || $has_cloudflare;

	}

}
