<?php

namespace Dollie\Core\Forms;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Modules\Forms;
use Dollie\Core\Singleton;

/**
 * Class DeleteSite
 *
 * @package Dollie\Core\Forms
 */
class DeleteSite extends Singleton {

	/**
	 * @var string
	 */
	private $form_key = 'form_dollie_delete_site';

	/**
	 * DeleteSite constructor.
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

		// Form submission action.
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

		$container->delete();

		wp_redirect( $container->get_permalink() );
		die();
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
			af_add_error( 'confirm_site_name', __( 'The targeted site couldn\'t be found.', 'dollie' ) );
		}

		if ( ! af_get_field( 'confirm_site_name' ) || af_get_field( 'confirm_site_name' ) !== $container->get_url() ) {
			af_add_error( 'confirm_site_name', __( 'Please type the unique name of your site. Your site name is shown in the sidebar and in your URL address bar.', 'dollie' ) );
		}
	}

	/**
	 * Change submit button text
	 *
	 * @param $args
	 *
	 * @return mixed
	 */
	public function change_form_args( $args ) {
		$args['submit_text'] = __( 'Delete', 'dollie' );
		$args['redirect']    = dollie()->page()->get_dashboard_url();

		return $args;
	}

}
