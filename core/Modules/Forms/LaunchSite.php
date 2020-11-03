<?php

namespace Dollie\Core\Modules\Forms;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Modules\Blueprints;
use Dollie\Core\Modules\Forms;
use Dollie\Core\Modules\Sites\WP;
use Dollie\Core\Singleton;
use Dollie\Core\Utils\Tpl;

/**
 * Class LaunchSite
 * @package Dollie\Core\Modules\Forms
 */
class LaunchSite extends Singleton {

	private $form_key = 'form_dollie_launch_site';

	/**
	 * LaunchSite constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'acf/init', [ $this, 'acf_init' ] );

	}

	public function acf_init() {

		// Placeholders/Change values
		add_filter( 'acf/prepare_field/name=site_blueprint', [ $this, 'populate_blueprints' ] );
		add_filter( 'acf/prepare_field/name=site_url', [ $this, 'append_site_url' ] );

		// Form args
		add_filter( 'af/form/args/key=' . $this->form_key, [ $this, 'change_form_args' ] );

		// Form submission action.
		add_action( 'af/form/validate/key=' . $this->form_key, [ $this, 'validate_form' ], 10, 2 );
		add_action( 'af/form/before_submission/key=' . $this->form_key, [ $this, 'submission_callback' ], 10, 3 );

	}

	public function validate_form( $form, $args ) {

		$domain = af_get_field( 'site_url' );

		if ( strpos( $domain, '-' ) !== false ) {
			af_add_error( 'site_url', esc_html__( 'Site URL cannot contain hyphens. Please remove them and try again.', 'dollie' ) );
		}

		// Check if domain does not already exists.
		$query_args = array(
			'post_name__in' => [ $domain, $domain . '-failed' ],
			'post_type'     => 'container',
			'numberposts'   => 1
		);
		$my_posts   = get_posts( $query_args );

		if ( ! empty( $my_posts ) ) {
			af_add_error( 'site_url', esc_html__( 'This site is already registered. Please try another name.', 'dollie' ) );
		}

		do_action( 'dollie/launch/validate/after' );
	}

	public function submission_callback( $form, $fields, $args ) {
		$domain    = af_get_field( 'site_url' );
		$email     = af_get_field( 'site_admin_email' );
		$blueprint = Forms::instance()->get_form_blueprint( $form, $args );
		$user_id   = get_current_user_id();

		$deploy_data = WP::instance()->deploy_site( $email, $domain, $user_id, $blueprint );

		if ( is_wp_error( $deploy_data ) ) {
			af_add_submission_error( $deploy_data->get_error_message() );
		}
	}

	public function append_site_url( $field ) {
		$field['append'] = DOLLIE_DOMAIN;

		return $field;
	}

	public function change_form_args( $args ) {
		$args['submit_text'] = esc_html__( 'Launch New Site', 'dollie' );

		return $args;
	}

	public function populate_blueprints( $field ) {

		$default_option = [];
		$blueprints     = Blueprints::instance()->get_all_blueprints( 'image' );

		if ( ! empty( $blueprints ) && Blueprints::show_default_blueprint() ) {
			$default_option = [
				0 => '<img data-toggle="tooltip" data-placement="bottom" ' .
				     ' title="' . esc_attr__( 'Default Wordpress Site', 'dollie' ) . '"' .
				     ' class="fw-blueprint-screenshot" src="' . DOLLIE_ASSETS_URL . 'img/default-blueprint.jpg">' .
				     esc_html__( 'No Blueprint', 'dollie' )
			];
		}
		$field['choices'] = $default_option + $blueprints;

		// Hide the blueprints field or check the value from cookie
		if ( empty( $blueprints ) ) {
			$field['class'] = 'acf-hidden';
		} elseif ( ! is_admin() && isset( $_COOKIE[ Blueprints::COOKIE_NAME ] ) ) {
			$field['value'] = (int) sanitize_text_field( $_COOKIE[ Blueprints::COOKIE_NAME ] );
		}

		// return the field
		return $field;

	}

}
