<?php

namespace Dollie\Core\Modules\Forms;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Modules\LaunchSite;
use Dollie\Core\Singleton;
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

		add_action( 'template_redirect', [ $this, 'redirect_to_new_container' ] );

		add_action( 'acf/init', [ $this, 'acf_init' ] );
	}

	public function acf_init() {

		// After launch form data.
		add_action( 'af/form/before_fields/key=' . $this->form_key, [ $this, 'add_message_before_fields' ] );

		// Placeholders/Change values
		add_filter( 'acf/load_field', [ $this, 'migration_instructions_placeholder' ], 10 );

		// Validation.
		//add_action( 'af/form/validate/key=' . $this->form_key, [ $this, 'validate_form' ], 10, 2 );

        // Form args
        add_filter('af/form/args/key=' . $this->form_key, [ $this, 'change_form_args' ] );

		// Form submission action.
		add_action( 'af/form/before_submission/key=' . $this->form_key, [ $this, 'submission_callback' ], 10, 3 );

	}

	public function submission_callback( $form, $fields, $args ) {

		$what_to_do = af_get_field( 'what_to_do' );
		if ( is_array( $what_to_do ) && isset( $what_to_do['value'] ) ) {
			$what_to_do = $what_to_do['value'];
		}
		if ( $what_to_do === 'setup' ) {
			$currentQuery = dollie()->get_current_object();

			$data = [
				'container_url' => $currentQuery->slug,
				'email'         => af_get_field( 'admin_email' ),
				'name'          => af_get_field( 'site_name' ),
				'description'   => af_get_field( 'site_description' ),
				'username'      => af_get_field( 'admin_username' ),
				'password'      => af_get_field( 'admin_password' )
			];

			$status = LaunchSite::instance()->update_site_details( $data );

			if ( is_wp_error( $status ) ) {
				af_add_error( 'what_to_do', $status->get_error_message() );
			}

		}

	}

	public function change_form_args( $args ) {

	    $args['redirect'] = add_query_arg( 'site', 'new', $args['redirect'] );
	    $args['submit_text'] = esc_html__( 'Complete Setup!', 'dollie' );

	    return $args;

    }

	public function redirect_to_new_container() {
		if ( isset( $_GET['site'] ) && $_GET['site'] === 'new' ) {
			$url = dollie()->get_latest_container_url();

			if ( $url ) {
				wp_redirect( $url );
				exit();
			}
		}
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
