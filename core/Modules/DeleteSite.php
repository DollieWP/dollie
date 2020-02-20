<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Helpers;
use Dollie\Core\Log;

/**
 * Class DeleteSite
 * @package Dollie\Core\Modules
 */
class DeleteSite extends Singleton {

	/**
	 * @var mixed
	 */
	private $helpers;

	/**
	 * DeleteSite constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->helpers = Helpers::instance();

		$delete_form = $this->helpers->get_dollie_gravity_form_ids( 'dollie-delete' );
		add_action( 'gform_after_submission_' . $delete_form[0], [ $this, 'delete_site' ], 10, 2 );
		add_filter( 'gform_validation_' . $delete_form[0], [ $this, 'confirm_site_delete' ] );
	}

	public function delete_site( $entry, $form ) {
		global $wp_query;
		$post_id = $wp_query->get_queried_object_id();
		Log::add( 'Customer manually deleted site' );
		$triggerdate = mktime( 0, 0, 0, date( 'm' ), date( 'd' ) + - 2, date( 'Y' ) );
		update_post_meta( $post_id, 'wpd_stop_container_at', $triggerdate, true );
		ContainerManagement::instance()->container_action( 'stop', $post_id );
	}

	public function confirm_site_delete( $validation_result ) {
		$post_slug = get_queried_object()->post_name;
		$form      = $validation_result['form'];

		//supposing we don't want input 1 to be a value of 86
		if ( rgpost( 'input_1' ) !== $post_slug ) {

			// set the form validation to false
			$validation_result['is_valid'] = false;

			//finding Field with ID of 1 and marking it as failed validation
			foreach ( $form['fields'] as &$field ) {

				//NOTE: replace 1 with the field you would like to validate
				if ( $field->id == '1' ) {
					$field->failed_validation  = true;
					$field->validation_message = 'Please type the unique name of your site. Your site name is shown in the sidebar and in your URL address bar.';
					break;
				}
			}
		}

		//Assign modified $form object back to the validation result
		$validation_result['form'] = $form;

		return $validation_result;
	}

}
