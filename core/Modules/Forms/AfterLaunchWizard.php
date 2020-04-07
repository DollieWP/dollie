<?php

namespace Dollie\Core\Modules\Forms;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Log;
use Dollie\Core\Modules\Backups;
use Dollie\Core\Modules\Sites\WP;
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

			$container_id = (int) $_POST['dollie_post_id'];

			if ( $container_id === 0 ) {
				$this->add_error();
			}

			$data = [
				'container_uri' => get_post_meta( $container_id, 'wpd_container_uri', true ),
				'email'         => af_get_field( 'admin_email' ),
				'name'          => af_get_field( 'site_name' ),
				'description'   => af_get_field( 'site_description' ),
				'username'      => af_get_field( 'admin_username' ),
				'password'      => af_get_field( 'admin_password' )
			];

			$status = WP::instance()->update_site_details( $data, $container_id );

			if ( is_wp_error( $status ) ) {
				af_add_submission_error( $status->get_error_message() );
			}

			// Remove the redirect on failure.
			if ( af_submission_failed() ) {
				AF()->submission['args']['redirect'] = '';
			}
		}
	}

	private function add_error() {
		Log::add( 'Form After Launch Wizard error', 'Current query id was not defined' );
		af_add_submission_error( esc_html__( 'An unknown error occurred. Please contact site administrator.', 'dollie' ) );
	}

	public function change_form_args( $args ) {

		$args['redirect']    = add_query_arg( 'site', 'new', $args['redirect'] );
		$args['submit_text'] = esc_html__( 'Complete Setup!', 'dollie' );

		return $args;

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

}
