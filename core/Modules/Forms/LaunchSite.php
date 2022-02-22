<?php

namespace Dollie\Core\Modules\Forms;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Modules\Blueprints;
use Dollie\Core\Modules\Forms;
use Dollie\Core\Modules\Sites\WP;
use Dollie\Core\Utils\Api;
use Dollie\Core\Singleton;

/**
 * Class LaunchSite
 *
 * @package Dollie\Core\Modules\Forms
 */
class LaunchSite extends Singleton {

	/**
	 * @var string
	 */
	private $form_key = 'form_dollie_launch_site';

	/**
	 * LaunchSite constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'acf/init', [ $this, 'acf_init' ] );
	}

	/**
	 * Init ACF
	 */
	public function acf_init() {
		// Placeholders/Change values
		add_filter( 'acf/load_field/name=site_blueprint', [ $this, 'populate_blueprints' ] );
		add_filter( 'acf/prepare_field/name=site_url', [ $this, 'append_site_url' ] );

		// Form args
		add_filter( 'af/form/args/key=' . $this->form_key, [ $this, 'change_form_args' ] );

		// Form submission action.
		add_action( 'af/form/validate/key=' . $this->form_key, [ $this, 'validate_form' ], 10, 2 );
		add_action( 'af/form/before_submission/key=' . $this->form_key, [ $this, 'submission_callback' ], 10, 3 );
	}

	/**
	 * Validate form
	 *
	 * @param $form
	 * @param $args
	 */
	public function validate_form( $form, $args ) {
		$domain    = af_get_field( 'site_url' );
		$site_type = af_get_field( 'site_type' );

		if ( ! preg_match( '/^[a-zA-Z0-9-]+$/', $domain ) ) {
			af_add_error( 'site_url', esc_html__( 'Site URL can only contain letters, numbers and dash.', 'dollie' ) );
		}

		// Check if domain does not already exists.
		$query_args = [
			'post_name__in' => [ $domain, $domain . WP::POST_SUFFIX ],
			'post_type'     => 'container',
			'numberposts'   => 1,
			'post_status'   => 'any',
		];
		$containers = get_posts( $query_args );

		if ( ! empty( $containers ) ) {
			af_add_error( 'site_url', esc_html__( 'This site is already registered. Please try another name.', 'dollie' ) );
		}

		if ( 'blueprint' === $site_type ) {
			$blueprint_availability = Api::process_response( Api::post( Api::ROUTE_BLUEPRINT_AVAILABILITY, [ 'route' => $domain . '.wp-site.xyz' ] ) );

			if ( ! $blueprint_availability ['status'] ) {
				af_add_error( 'site_url', esc_html__( 'This blueprint name is not available. Please try again using another name.', 'dollie' ) );
			}
		}

		do_action( 'dollie/launch/validate/after' );
	}

	/**
	 * Process submitted data
	 *
	 * @param [type] $form
	 * @param [type] $fields
	 * @param [type] $args
	 * @return void
	 */
	public function submission_callback( $form, $fields, $args ) {
		$blueprint   = Forms::instance()->get_form_blueprint( $form, $args );

		if ( af_get_field( 'assign_to_customer' )  ) {
			$assigned_user_id = af_get_field( 'assign_to_customer' );
		} else {
			$assigned_user_id = get_current_user_id();
		}

		$domain      = af_get_field( 'site_url' );
		$deploy_data = [
			'email'     => af_get_field( 'site_admin_email' ),
			'domain'    => $domain,
			'user_id'   => $assigned_user_id,
			'blueprint' => $blueprint,
			'site_type' => af_get_field( 'site_type' ),
		];

		$deploy_data = apply_filters( 'dollie/launch_site/form_deploy_data', $deploy_data, $domain, $blueprint );

		// add WP site details.
		$setup_data = [
			'email'       => af_get_field( 'site_admin_email' ),
			'username'    => af_get_field( 'admin_username' ),
			'password'    => af_get_field( 'admin_password' ),
			'name'        => af_get_field( 'site_name' ),
			'description' => af_get_field( 'site_description' ),
		];

		WP::instance()->deploy_site(
			$deploy_data,
			$setup_data
		);
	}

	/**
	 * Append site URL
	 *
	 * @param $field
	 *
	 * @return mixed
	 */
	public function append_site_url( $field ) {
		$field['append'] = DOLLIE_DOMAIN;

		return $field;
	}

	/**
	 * Change form args
	 *
	 * @param $args
	 *
	 * @return mixed
	 */
	public function change_form_args( $args ) {
		if ( isset( $args['values'], $args['values']['site_type'] ) && 'blueprint' === $args['values']['site_type'] ) {
			add_filter(
				'acf/prepare_field/name=site_url',
				function ( $field ) {
					$field['append'] = '.wp-site.xyz';

					return $field;
				}
			);
		}

		return $args;
	}

	/**
	 * Populate blueprints
	 *
	 * @param $field
	 *
	 * @return mixed
	 */
	public function populate_blueprints( $field ) {
		$default_option = [];
		$blueprints     = Blueprints::instance()->get( 'image' );

		if ( ! empty( $blueprints ) && Blueprints::show_default_blueprint() ) {
			$default_option = [
				0 => '<img data-toggle="tooltip" data-placement="bottom" ' .
					 ' title="' . esc_attr__( 'Default Wordpress Site', 'dollie' ) . '"' .
					 ' class="fw-blueprint-screenshot" src="' . DOLLIE_ASSETS_URL . 'img/default-blueprint.jpg">' .
					 esc_html__( 'No Blueprint', 'dollie' ),
			];
		}
		$field['choices'] = $default_option + $blueprints;

		// Hide the blueprints field or check the value from cookie.
		if ( empty( $blueprints ) ) {

			$field['wrapper']['class'] = 'acf-hidden';
			$field['disabled']         = 1;

		} elseif ( isset( $_COOKIE[ DOLLIE_BLUEPRINTS_COOKIE ] ) && ! is_admin() ) {
			$field['value'] = (int) sanitize_text_field( $_COOKIE[ DOLLIE_BLUEPRINTS_COOKIE ] );
		}

		return $field;
	}

}
