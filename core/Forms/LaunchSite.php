<?php

namespace Dollie\Core\Forms;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Modules\Forms;
use Dollie\Core\Services\BlueprintService;
use Dollie\Core\Services\DeployService;
use Dollie\Core\Singleton;
use Dollie\Core\Utils\ConstInterface;

/**
 * Class LaunchSite
 *
 * @package Dollie\Core\Forms
 */
class LaunchSite extends Singleton implements ConstInterface {
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
		$domain = af_get_field( 'site_url' );

		if ( ! preg_match( '/^[a-zA-Z0-9-]+$/', $domain ) ) {
			af_add_error( 'site_url', esc_html__( 'Site URL can only contain letters, numbers and dash.', 'dollie' ) );
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
		if ( af_get_field( 'assign_to_customer' ) ) {
			$owner_id = af_get_field( 'assign_to_customer' );
		} else {
			$owner_id = get_current_user_id();
		}

		$deploy_data = [
			'owner_id'    => $owner_id,
			'blueprint'   => Forms::instance()->get_form_blueprint( $form, $args ),
			'email'       => af_get_field( 'site_admin_email' ),
			'username'    => af_get_field( 'admin_username' ),
			'password'    => af_get_field( 'admin_password' ),
			'name'        => af_get_field( 'site_name' ),
			'description' => af_get_field( 'site_description' ),
		];

		$deploy_data = apply_filters( 'dollie/launch_site/form_deploy_data', $deploy_data );

		DeployService::instance()->start(
			'blueprint' === af_get_field( 'site_type' ) ? self::TYPE_BLUEPRINT : self::TYPE_SITE,
			af_get_field( 'site_url' ),
			$deploy_data
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
		$blueprints     = BlueprintService::instance()->get( 'html' );

		if ( ! empty( $blueprints ) ) {
			$default_option = [
				0 => dollie()->load_template( 'parts/blueprint-default-image' ),
			];
		}

		$field['choices'] = $default_option + $blueprints;

		if ( empty( $blueprints ) ) {
			$field['wrapper']['class'] = 'acf-hidden';
			$field['disabled']         = 1;
		} elseif ( isset( $_COOKIE[ DOLLIE_BLUEPRINTS_COOKIE ] ) && ! is_admin() ) {
			$field['value'] = (int) sanitize_text_field( $_COOKIE[ DOLLIE_BLUEPRINTS_COOKIE ] );
		}

		return $field;
	}
}
