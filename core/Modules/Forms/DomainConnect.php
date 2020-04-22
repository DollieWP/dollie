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
 * Class DomainConnect
 * @package Dollie\Core\Modules\Forms
 */
class DomainConnect extends Singleton {

	private $form_key = 'form_dollie_domain_connect';

	/**
	 * DomainConnect constructor.
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

		$container = Forms::get_form_container();
		$domain    = af_get_field( 'domain_name' );

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
			'dollie_token'  => Api::get_dollie_token(),
		] );

		$response_data = API::process_response( $request_route_add );

		// Show an error if API can't add the Route.
		if ( ! $response_data || ! array_key_exists( 'path', $response_data ) ) {

			af_add_submission_error( wp_kses_post( sprintf(
				__( 'Sorry, We could not link this domain to your site. This could be because the domain is already registered for another site in our network. It could also be an issue on our end! Please try again or <a href="%s">Contact Support</a>', 'dollie' ),
				'https://dollie.co/support-redirect'
			) ) );

			Log::add( $container->slug . ' could not link domain ' . $domain, print_r( $request_route_add, true ) );

			return;
		}

		// Save the Domain Data and make another Request for the www domain.
		update_post_meta( $container->id, 'wpd_domain_id', $response_data['id'] );
		update_post_meta( $container->id, 'wpd_domains', $domain );

		$request_route_add_www = Api::post( Api::ROUTE_DOMAIN_ROUTES_ADD, [
			'container_id'  => $request->id,
			'domain'        => 'www.' . $domain,
			'dollie_domain' => DOLLIE_INSTALL,
			'dollie_token'  => Api::get_dollie_token(),
		] );

		$response_data_www = API::process_response( $request_route_add_www );

		if ( $request_route_add_www ) {
			// Also save the www Domain data.
			update_post_meta( $container->id, 'wpd_www_domain_id', $response_data_www['id'] );
			Log::add( $container->slug . ' linked up domain ' . $domain );
		} else {
			Log::add( $container->slug . ' could not link www domain ' . $domain, print_r( $request_route_add_www, true ) );
		}

		do_action( 'dollie/domain/connect/submission/after', $container, $domain );

	}

	public function validate_form( $form, $args ) {

		// Check if doamin inputs are visible
		if ( af_get_field( 'is_domain_registered' ) === 'no' && af_get_field( 'is_new_domain_registered' ) === false ) {
			af_add_error( 'is_new_domain_registered', esc_html__( 'You need to have a registered domain before you continue.', 'dollie' ) );
		}

		// Check if domain is confirmed
		if ( af_get_field( 'domain_name' ) !== af_get_field( 'confirm_domain_name' ) ) {
			af_add_error( 'confirm_domain_name', esc_html__( 'Your domain names do not match.', 'dollie' ) );
		}

		// Make sure we don't continue until data is migrated
		if ( af_get_field( 'has_active_site' ) === 'yes_live' && af_get_field( 'is_data_moved' ) !== 'yes' ) {
			af_add_error( 'is_data_moved', esc_html__( 'You need to migrate your data before continuing.', 'dollie' ) );
		}

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

	public function change_form_args( $args ) {
		$args['submit_text'] = esc_html__( 'Connect Domain', 'dollie' );

		return $args;
	}

	public function change_form_attributes( $atts ) {
		if ( $this->is_form_restricted() ) {
			$atts['class'] .= ' acf-hidden';
		}

		return $atts;
	}

	private function is_form_restricted() {
		$container = dollie()->get_current_object();

		// If it already has linked domain and return an empty space as message
		if ( get_post_meta( $container->id, 'wpd_domains', true ) ) {
			return true;
		}

		return false;
	}

}
