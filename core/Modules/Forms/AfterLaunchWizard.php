<?php

namespace Dollie\Core\Modules\Forms;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Log;
use Dollie\Core\Modules\Backups;
use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;
use Dollie\Core\Utils\Tpl;

/**
 * Class AfterLaunchWizard
 * @package Dollie\Core\Modules\Forms
 */
class AfterLaunchWizard extends Singleton {

	private $form_key = 'form_dollie_after_launch';

	/**
	 * AfterLaunchWizard constructor.
	 */
	public function __construct() {
		parent::__construct();

		// Form submission action.
		add_action( 'acf/validate_save_post', array( $this, 'submission_callback' ), 10, 0 );

		add_action( 'acf/init', [ $this, 'acf_init' ] );
	}

	public function acf_init() {

		// After launch form data.
		add_action( 'af/form/before_fields/key=' . $this->form_key, [ $this, 'add_message_before_fields' ] );

		// Placeholders/Change values
		add_filter( 'acf/load_field', [ $this, 'migration_instructions_placeholder' ], 10 );

		// Form args
        add_filter( 'af/form/args/key=' . $this->form_key, [ $this, 'change_form_args' ] );

	}

	/**
	 * Submission callback hooked at acf/validate_save_post so we can remove the redirect in case of error
	 *
	 * @return void
	 */
	public function submission_callback() {

		// Make sure this is not an AJAX validation request
		if ( isset ( $_POST['action'] ) ) {
			return;
		}

		// Make sure it is a form submission
		if ( ! isset( AF()->submission ) ) {
			return;
		}

		// Make sure it is our form
		$form = AF()->submission['form'];
		if ( $form['key'] !== $this->form_key ) {
			return;
		}

		$what_to_do = af_get_field( 'what_to_do' );

		if ( is_array( $what_to_do ) && isset( $what_to_do['value'] ) ) {
			$what_to_do = $what_to_do['value'];
		}

		if ( $what_to_do === 'setup' ) {
			$currentQuery = dollie()->get_current_object();

			if ( $currentQuery->id !== 0 ) {

				$data = [
					'container_url' => $currentQuery->slug,
					'email'         => af_get_field( 'admin_email' ),
					'name'          => af_get_field( 'site_name' ),
					'description'   => af_get_field( 'site_description' ),
					'username'      => af_get_field( 'admin_username' ),
					'password'      => af_get_field( 'admin_password' )
				];

				$status = $this->update_site_details( $data );

				if ( is_wp_error( $status ) ) {
					af_add_submission_error( $status->get_error_message() );
				}

			} else {
				Log::add( 'Form After Launch Wizard error', 'Current query id was not defined' );
				af_add_submission_error( esc_html__( 'An unknown error occurred. Please contact site administrator.', 'dollie' ) );
			}

			// Remove the redirect on failure.
			if ( af_submission_failed() ) {
				AF()->submission['args']['redirect'] = '';
			}
		}
	}

	public function change_form_args( $args ) {

		$args['redirect']    = add_query_arg( 'site', 'new', $args['redirect'] );
		$args['submit_text'] = esc_html__( 'Complete Setup!', 'dollie' );

		return $args;

	}



	/**
	 * Welcome Wizard. Update WP site details.
	 *
	 * @param int|null $container_id
	 * @param array|null $data
	 *
	 * @return bool|\WP_Error
	 */
	private function update_site_details( $data = null, $container_id = null ) {

		if ( ! isset( $container_id ) ) {
			$current_query = dollie()->get_current_object();

			$container_id   = $current_query->id;
			$container_slug = $current_query->slug;
		} else {
			$container_slug = get_post( $container_id )->post_name;
		}

		do_action( 'dollie/launch_site/set_details/before', $container_id, $data );

		$demo = get_post_meta( $container_id, 'wpd_container_is_demo', true );

		if ( $demo !== 'yes' ) {
			$partner = get_user_by( 'login', get_post_meta( $container_id, 'wpd_partner_ref', true ) );

			$is_partner_lead = $partner_blueprint = $blueprint_deployed = '';

			if ( $partner instanceof \WP_User ) {
				$partner_blueprint  = get_post_meta( $partner->ID, 'wpd_partner_blueprint_created', true );
				$blueprint_deployed = get_post_meta( $container_id, 'wpd_blueprint_deployment_complete', true );
				$is_partner_lead    = get_post_meta( $container_id, 'wpd_is_partner_lead', true );
			}

			if ( $is_partner_lead === 'yes' && $partner_blueprint === 'yes' && $blueprint_deployed !== 'yes' ) {
				$partner_install = get_post_meta( $partner->ID, 'wpd_url', true );

				Api::post( Api::ROUTE_BLUEPRINT_DEPLOY_FOR_PARTNER, [
					'container_url' => get_post_meta( $container_id, 'wpd_container_uri', true ),
					'partner_url'   => $partner_install,
					'domain'        => $container_slug
				] );

				update_post_meta( $container_id, 'wpd_partner_blueprint_deployed', 'yes' );
				sleep( 5 );
			} else {

				if ( ! isset( $data ) || empty( $data ) ) {

					Log::add( $container_slug . ' has invalid site details data', json_encode( $data ), 'setup' );

					return new \WP_Error( 'dollie_invalid_data', esc_html__( 'Processed site data is invalid.', 'dollie' ) );

				}

				Api::post( Api::ROUTE_WIZARD_SETUP, $data );
			}
		}

		dollie()->flush_container_details();

		update_post_meta( $container_id, 'wpd_setup_complete', 'yes' );

		Log::add( $container_slug . ' has completed the initial site setup', '', 'setup' );

		Backups::instance()->trigger_backup();

		do_action( 'dollie/launch_site/set_details/after', $container_id, $data );

		return true;

	}


	public function add_message_before_fields() {
		?>
        <div class="blockquote-box blockquote-alert clearfix">
            <div class="square pull-left">
                <i class="fab fa-wordpress"></i>
            </div>
            <h4>
				<?php esc_html_e( 'Let\'s configure your new WordPress site', 'dollie' ); ?>
            </h4>
            <p class="bg-gray-lighter">
				<?php esc_html_e( 'To help you get started our one-time welcome wizard will guide you through setting up your new WordPress site.', 'dollie' ); ?>
            </p>
        </div>
		<?php
	}

	/**
	 * Add migration instruction to form
	 *
	 * @param $value
	 * @param $post_id
	 * @param $field
	 *
	 * @return string|string[]
	 */
	public function migration_instructions_placeholder( $field ) {

		if ( isset( $field['message'] ) && $field['message'] ) {

			$currentQuery = dollie()->get_current_object();

			$user    = wp_get_current_user();
			$request = get_transient( 'dollie_s5_container_details_' . $currentQuery->slug );

			if ( ! $request || ! is_object( $request ) ) {
				return $field;
			}

			$hostname = preg_replace( '#^https?://#', '', $request->uri );

			$tpl = Tpl::load( DOLLIE_MODULE_TPL_PATH . 'migration-instructions', [
				'post_slug' => $currentQuery->slug,
				'request'   => $request,
				'user'      => $user,
				'hostname'  => $hostname
			] );

			$field['message'] = str_replace( '{dollie_migration_instructions}', $tpl, $field['message'] );
		}

		return $field;
	}

}
