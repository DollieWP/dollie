<?php

namespace Dollie\Core\Forms;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Modules\Backups;
use Dollie\Core\Modules\Domain;
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

		// Domain::instance()->remove_route( $container->id );
		// Backups::instance()->make( $container->id, false );

		wp_delete_post( $container->get_id(), true ); // also hooks into undeploy

	}

	/**
	 * Validate form
	 *
	 * @param $form
	 * @param $args
	 */
	public function validate_form( $form, $args ) {
		$container = Forms::get_form_container();

		if ( ! af_get_field( 'confirm_site_name' ) || af_get_field( 'confirm_site_name' ) !== $container->get_slug() ) {
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
		$args['redirect']    = get_site_url() . '/dashboard';

		return $args;
	}

}
