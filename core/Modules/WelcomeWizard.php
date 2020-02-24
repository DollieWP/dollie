<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;
use Dollie\Core\Log;
use Dollie\Core\Utils\Tpl;

/**
 * Class WelcomeWizard
 * @package Dollie\Core\Modules
 */
class WelcomeWizard extends Singleton {

	/**
	 * WelcomeWizard constructor.
	 */
	public function __construct() {
		parent::__construct();
		
		$setup_forms = dollie()->helpers()->get_dollie_gravity_form_ids( 'dollie-wizard' );
		foreach ( $setup_forms as $form_id ) {
			add_action( 'gform_post_paging_' . $form_id, [ $this, 'update_site_details' ], 10, 3 );
			add_action( 'gform_after_submission_' . $form_id, [ $this, 'complete_setup_wizard' ], 10, 2 );
			add_filter( 'gform_field_input_' . $form_id, [ $this, 'inject_migration_instructions' ], 10, 5 );
		}
	}

	/**
	 * Update site details
	 *
	 * @param $form
	 * @param $source_page_number
	 * @param $current_page_number
	 */
	public function update_site_details( $form, $source_page_number, $current_page_number ) {
		if ( $current_page_number > 1 ) {
			$value = rgpost( 'input_1' );

			if ( $value === 'setup' ) {
				global $wp_query;
				$post_id = $wp_query->get_queried_object_id();

				$demo    = get_post_meta( $post_id, 'wpd_container_is_demo', true );
				$partner = get_userdatabylogin( get_post_meta( $post_id, 'wpd_partner_ref', true ) );

				$partner_blueprint  = get_post_meta( $partner->ID, 'wpd_partner_blueprint_created', true );
				$blueprint_deployed = get_post_meta( $post_id, 'wpd_blueprint_deployment_complete', true );

				if ( $demo !== 'yes' ) {
					$install         = get_queried_object()->post_name;
					$is_partner_lead = get_post_meta( $post_id, 'wpd_is_partner_lead', true );

					if ( $is_partner_lead === 'yes' && $partner_blueprint === 'yes' && $blueprint_deployed !== 'yes' ) {
						$partner_install = get_post_meta( $partner->ID, 'wpd_url', true );

						$post_body = [
							'filter'    => 'name: https://' . $install . DOLLIE_DOMAIN . '-' . DOLLIE_RUNDECK_KEY,
							'argString' => '-url ' . $partner_install . DOLLIE_DOMAIN . ' -domain ' . $install . DOLLIE_DOMAIN
						];

						Api::postRequestRundeck( '1/job/85783830-a89d-439f-b4db-4a5e0e0fd6a9/run/', $post_body );

						update_post_meta( $post_id, 'wpd_partner_blueprint_deployed', 'yes' );
						sleep( 5 );
					} else {
						$email       = rgpost( 'input_5' );
						$name        = rgpost( 'input_4' );
						$username    = rgpost( 'input_26' );
						$password    = rgpost( 'input_27' );
						$description = rgpost( 'input_11' );

						$post_body = [
							'filter'    => 'name: https://' . $install . DOLLIE_DOMAIN . '-' . DOLLIE_RUNDECK_KEY,
							'argString' => '-email ' . $email . ' -name ' . $name . ' -description ' . $description . ' -password ' . $password . ' -username ' . $username
						];

						Api::postRequestRundeck( '1/job/f0b8f078-fb6d-47e7-ac8b-2962fe8b0241/run/', $post_body );
					}
				}

				dollie()->helpers()->flush_container_details();
			}
		}
	}

	/**
	 * Complete setup wizard
	 *
	 * @param $entry
	 * @param $form
	 */
	public function complete_setup_wizard( $entry, $form ) {
		global $wp_query;

		update_post_meta( $wp_query->get_queried_object_id(), 'wpd_setup_complete', 'yes' );
		Log::add( get_queried_object()->post_name . ' has completed the initial site setup', '', 'setup' );
		Backups::instance()->trigger_backup();
	}

	/**
	 * Add migration instruction to form
	 *
	 * @param $input
	 * @param $field
	 * @param $value
	 * @param $lead_id
	 * @param $form_id
	 *
	 * @return string
	 */
	public function inject_migration_instructions( $input, $field, $value, $lead_id, $form_id ) {
		$post_slug = get_queried_object()->post_name;
		$user      = wp_get_current_user();
		$request   = get_transient( 'dollie_s5_container_details_' . $post_slug );
		$hostname  = preg_replace( '#^https?://#', '', $request->uri );

		if ( $form_id === dollie()->helpers()->get_dollie_gravity_form_ids( 'dollie-wizard' )[0] && $field->id === 7 ) {
			$input = Tpl::load( DOLLIE_MODULE_TPL_PATH . 'migration-instructions', [
				'post_slug' => $post_slug,
				'request'   => $request,
				'user'      => $user,
				'hostname'  => $hostname
			] );
		}

		return $input;
	}

}
