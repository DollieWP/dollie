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

		do_action( 'dollie/domain/dns/validate/after' );
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

		$container = dollie()->get_current_object();
		$has_domain     = get_post_meta( $container->id, 'wpd_domains', true );
		$has_cloudflare = get_post_meta( $container->id, 'wpd_cloudflare_email', true );
		$has_analytics  = get_post_meta( $container->id, 'wpd_cloudflare_zone_id', true );
		$has_le         = get_post_meta( $container->id, 'wpd_letsencrypt_enabled', true );

		// If it already has SSL return an empty space as message
		if ( ! $has_domain || $has_analytics || $has_le || $has_cloudflare  ) {
			return ' ';
		}

		return $restriction;
	}

	public function change_form_args( $args ) {
		$args['submit_text'] = esc_html__( 'Complete DNS Setup', 'dollie' );

		return $args;
	}

}
