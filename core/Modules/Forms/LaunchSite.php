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
		add_filter( 'af/form/after_fields/key=' . $this->form_key, [ $this, 'add_modal_data' ], 10, 2 );
		add_filter( 'af/form/args/key=' . $this->form_key, [ $this, 'change_form_args' ] );

		// Form submission action.
		add_action( 'af/form/before_submission/key=' . $this->form_key, [ $this, 'submission_callback' ], 10, 3 );

	}

	public function submission_callback( $form, $fields, $args ) {
		$domain            = af_get_field( 'site_url' );
		$email             = af_get_field( 'site_admin_email' );
		$default_blueprint = af_get_field( 'site_blueprint' ) ?: Forms::instance()->get_form_arg( 'site_blueprint', $form, $args );
		$blueprint         = isset( $_COOKIE[ Blueprints::COOKIE_NAME ] ) ? $_COOKIE[ Blueprints::COOKIE_NAME ] : $default_blueprint;
		$user_id           = get_current_user_id();

		$deploy_data = WP::instance()->deploy_site( $email, $domain, $user_id, $blueprint );

		if ( is_wp_error( $deploy_data ) ) {
			af_add_submission_error( $deploy_data->get_error_message() );
		}
	}

	public function append_site_url( $field ) {
		$field['append'] = DOLLIE_DOMAIN;

		return $field;
	}

	public function add_modal_data() {
		Tpl::load( 'launch-splash', [], true );
	}

	public function change_form_args( $args ) {
		$args['submit_text'] = esc_html__( 'Launch New Site', 'dollie' );

		return $args;
	}

	public function populate_blueprints( $field ) {

		$blueprints = Blueprints::instance()->get_all_blueprints( 'image' );

		if ( ! empty( $blueprints ) ) {
			$default_option = [
				0 =>  '<img data-toggle="tooltip" data-placement="bottom" ' .
				      ' title="Default Wordpress Site"' .
				      ' class="fw-blueprint-screenshot" src="' . DOLLIE_ASSETS_URL . 'img/default-blueprint.jpg">' .
				      'No Blueprint'
			];
			$field['choices'] = $default_option + $blueprints;
		}

		// Hide the blueprints field
		if ( isset( $_COOKIE[ Blueprints::COOKIE_NAME ] ) || empty( $blueprints ) ) {
			$field['class'] = 'acf-hidden';
		}

		// return the field
		return $field;

	}

}
