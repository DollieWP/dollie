<?php

namespace Dollie\Core\Modules\Forms;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Log;
use Dollie\Core\Modules\Forms;
use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;

/**
 * Class DomainWizard
 * @package Dollie\Core\Modules\Forms
 */
class DomainWizard extends Singleton {

	private $form_key = 'form_dollie_domain_wizard';

	/**
	 * DomainWizard constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'acf/init', [ $this, 'acf_init' ] );
	}

	public function acf_init() {

		// Merge tags
		add_filter( 'af/merge_tags/resolve', [ $this, 'add_merge_tags' ], 10, 2 );
		add_filter( 'af/merge_tags/custom', [ $this, 'register_container_tags' ], 10, 2 );

		// After launch form data.
		add_action( 'af/form/before_fields/key=' . $this->form_key, [ $this, 'add_message_before_fields' ] );

		// Form args
		add_filter( 'af/form/args/key=' . $this->form_key, [ $this, 'change_form_args' ] );

		// Form submission action.
		add_action( 'af/form/validate/key=' . $this->form_key, [ $this, 'validate_form' ], 10, 2 );

		add_action( 'af/form/before_submission/key=' . $this->form_key, [ $this, 'submission_callback' ], 10, 3 );


	}


	public function add_merge_tags( $output, $tag ) {
		if ( 'dollie_tpl_is_migrate_completed' === $tag ) {
			return '';
		}

		return $output;
	}

	function register_container_tags( $tags, $form ) {
		$tags[] = array(
			'value' => 'dollie_tpl_is_migrate_completed',
			'label' => esc_html__( 'Dollie TPL is ', 'dollie' ),
		);

		return $tags;
	}


	public function submission_callback( $form, $fields, $args ) {

		$container = Forms::get_form_container();

		if ( $container === false ) {
			return;
		}

		$container_uri = get_post_meta( $container->id, 'wpd_container_uri', true );

		Api::post( Api::ROUTE_BLUEPRINT_CREATE_OR_UPDATE, [ 'container_uri' => $container_uri ] );

		update_post_meta( $container->id, 'wpd_blueprint_created', 'yes' );
		update_post_meta( $container->id, 'wpd_blueprint_time', @date( 'd/M/Y:H:i' ) );

		Log::add( $container->slug . ' updated/deployed a new Blueprint', '', 'blueprint' );

	}

	public function validate_form( $form, $args ) {
		if ( ! af_get_field( 'confirmation' ) ) {
			af_add_error( 'confirmation', esc_html__( 'Please confirm blueprint creation.', 'dollie' ) );

		}

	}

	public function change_form_args( $args ) {
		$args['submit_text'] = esc_html__( 'Complete Domain Setup', 'dollie' );

		return $args;
	}

	public function add_message_before_fields() {

		if ( ! is_user_logged_in() ) {
			return;
		}

		$user = wp_get_current_user();

		?>
        <div class="blockquote-box blockquote-success clearfix">
            <div class="square pull-left">
                <i class="fa fa-globe"></i>
            </div>
            <h4>
				<?php printf( __( 'Let\'s link up your custom domain %s!', 'dollie' ), $user->display_name ); ?>
            </h4>
            <p>
				<?php esc_html_e( ' We\'ll walk you through all the steps required to link your own domain to your site. Let\'s get started shall we?', 'dollie' ); ?>
            </p>
        </div>
		<?php
	}

}
