<?php

namespace Dollie\Core\Modules\Forms;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Log;
use Dollie\Core\Modules\ContainerManagement;
use Dollie\Core\Modules\Forms;
use Dollie\Core\Singleton;

/**
 * Class DeleteSite
 * @package Dollie\Core\Modules\Forms
 */
class DeleteSite extends Singleton {

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
	 * Form callback
	 *
	 * @param $form
	 * @param $fields
	 * @param $args
	 */
	public function submission_callback( $form, $fields, $args ) {
		$container = Forms::get_form_container();

		Log::add( 'Customer manually deleted site' );

		$trigger_date = mktime( 0, 0, 0, date( 'm' ), date( 'd' ) + - 2, date( 'Y' ) );
		update_post_meta( $container->id, 'wpd_stop_container_at', $trigger_date, true );
		ContainerManagement::instance()->container_action( 'stop', $container->id );
	}

	/**
	 * Form validation
	 *
	 * @param $form
	 * @param $args
	 */
	public function validate_form( $form, $args ) {
		$container = Forms::get_form_container();

		if ( ! af_get_field( 'confirm_site_name' ) || af_get_field( 'confirm_site_name' ) !== $container->slug ) {
			af_add_error( 'confirm_site_name', esc_html__( 'Please type the unique name of your site. Your site name is shown in the sidebar and in your URL address bar.', 'dollie' ) );
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
		$args['submit_text'] = esc_html__( 'Delete', 'dollie' );
		$args['redirect']    = get_site_url() . '/dashboard';

		return $args;
	}

}
