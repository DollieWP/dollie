<?php

namespace Dollie\Core\Forms;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Modules\Forms;
use Dollie\Core\Services\DnsService;

/**
 * Class DomainConnect
 *
 * @package Dollie\Core\Forms
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
		// Form args.
		add_filter( 'af/form/args/key=' . $this->form_key, [ $this, 'change_form_args' ] );
		add_filter( 'af/form/attributes/key=' . $this->form_key, [ $this, 'change_form_attributes' ] );

		// Form fields & attributes.
		add_filter( 'af/form/field_attributes/key=' . $this->form_key, [ $this, 'field_attributes_visibility' ], 10, 4 );
		add_filter( 'af/field/before_render/name=allow_dns', [ $this, 'field_visibility' ], 10, 3 );

		// Restrictions.
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

		$domain    = trim( af_get_field( 'domain_name' ) );
		$domain    = str_replace( [ 'http://', 'https://' ], '', $domain );
		$allow_dns = af_get_field( 'allow_dns' );

		// Force to classic domain connect if DNS manager is not enabled globally
		if ( ! get_field( 'wpd_enable_dns_manager', 'options' ) ) {
			$allow_dns = 'no';
		}

		$routes = $container->get_routes();

		if ( ! empty( $routes ) ) {
			return;
		}

		if ( 'yes' === $allow_dns ) {

		} else {
			$response = DnsService::instance()->add_route( $container, $domain );

			if ( false === $response ) {
				af_add_submission_error(
					wp_kses_post(
						sprintf(
							__( 'Sorry, we could not link this domain to your site. This could be because the domain is already being used by another site in our network.', 'dollie' )
						)
					)
				);
			}
		}

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

		$domain  = trim( af_get_field( 'domain_name' ) );
		$domain  = str_replace( [ 'http://', 'https://' ], '', $domain );
		$records = $container->scan_domain( $domain );

		if ( 'no' === af_get_field( 'allow_dns' ) ) {
			if ( empty( $records ) ) {
				af_add_error( 'domain_name', __( 'Cannot fetch domain\'s records. Make sure this is a valid domain name and try again.', 'dollie' ) );

				return;
			}

			$credentials  = $container->get_credentials();
			$record_found = false;

			foreach ( $records as $record ) {
				if ( 'A' === $record['type'] && $credentials['ip'] === $record['ip'] ) {
					$record_found = true;
				}
			}

			if ( ! $record_found ) {
				af_add_error( 'domain_name', __( 'Your domain DNS A record is not yet pointing to our IP address. Please make sure the A records are set correctly and try again.', 'dollie' ) );

				return;
			}
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
		// Added in case another restriction already applies.
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
	 * Field label visibility
	 *
	 * @param array $attributes
	 * @param array $field
	 * @param array $form
	 * @param array $args
	 * @return array
	 */
	public function field_attributes_visibility( $attributes, $field, $form, $args ) {
		$dns_manager_enabled = get_field( 'wpd_enable_dns_manager', 'options' );

		if ( ! $dns_manager_enabled && 'allow_dns' === $attributes['data-name'] ) {
			$attributes['class'] .= ' acf-hidden';
		}

		return $attributes;
	}

	/**
	 * Field visibility
	 *
	 * @param array $field
	 * @param array $form
	 * @param array $args
	 * @return array
	 */
	public function field_visibility( $field, $form, $args ) {
		$dns_manager_enabled = get_field( 'wpd_enable_dns_manager', 'options' );

		if ( ! $dns_manager_enabled ) {
			$field['class'] .= ' acf-hidden';
		}

		return $field;
	}

	/**
	 * Check if form is restricted
	 *
	 * @return bool
	 */
	public function is_form_restricted() {
		$container = dollie()->get_container();

		if ( is_wp_error( $container ) ) {
			return true;
		}

		// If submitted domain with dns manager on, restrict it.
		$dns_manager_enabled = get_field( 'wpd_enable_dns_manager', 'options' );

		if ( $dns_manager_enabled && get_post_meta( $container->get_id(), 'wpd_domain_dns_manager', true ) ) {
			return true;
		}

		if ( ! $container->is_site() ) {
			return true;
		}

		$routes = $container->get_routes();

		if ( ! is_wp_error( $routes ) && ! empty( $routes ) ) {
			return true;
		}

		return false;
	}

}
