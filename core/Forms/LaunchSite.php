<?php

namespace Dollie\Core\Forms;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Modules\Forms;
use Dollie\Core\Services\BlueprintService;
use Dollie\Core\Services\DeployService;
use Dollie\Core\Services\WorkspaceService;
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
		add_filter( 'acf/load_field/name=site_url', [ $this, 'set_default_site_url' ] );
		add_filter( 'acf/prepare_field/name=site_url', [ $this, 'append_site_url' ] );

		// Form args
		add_filter( 'af/form/args/key=' . $this->form_key, [ $this, 'change_form_args' ] );

		// Form submission action.
		add_action( 'af/form/validate/key=' . $this->form_key, [ $this, 'validate_form' ], 10, 2 );
		add_action( 'af/form/before_submission/key=' . $this->form_key, [ $this, 'submission_callback' ], 10, 3 );
		add_action( 'af/form/hidden_fields/key=' . $this->form_key, [ $this, 'hidden_field' ], 10, 2 );

	}

	/**
	 * Validate form
	 *
	 * @param $form
	 * @param $args
	 */
	public function validate_form( $form, $args ) {
		$deployment_domain = WorkspaceService::instance()->get_deployment_domain();
		$domain            = af_get_field( 'site_url' );
		$domain_extension  = 'blueprint' === af_get_field( 'site_type' ) ? '.wp-site.xyz' : $deployment_domain;

		if ( strlen( $domain . $domain_extension ) > 63 ) {
			$max = 63 - strlen( $deployment_domain );
			af_add_error( 'site_url', sprintf( esc_html__( 'Site URL is too long. The name should not exceed %d characters. Don\'t worry, this is just your temporary URL. You can add a custom domain after launching.', 'dollie' ), $max ) );
		}

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
	 *
	 * @return void
	 */
	public function submission_callback( $form, $fields, $args ) {

		$subscription = dollie()->subscription($owner_id);
		$owner_id = get_current_user_id();
		$email = af_get_field( 'site_admin_email' );

		if ( af_get_field( 'assign_to_customer' ) ) {
			$owner_id = af_get_field( 'assign_to_customer' );
			$email = get_user_by( 'ID', $owner_id )->user_email;
		}

		$subscription_vip = dollie()->subscription()->has_vip($owner_id);

		//Launch as VIP?
		$vip_checked = af_get_field( 'launch_as_vip' );

		//Is launch as VIP checked and does the user have a VIP subscription?
		if ( $vip_checked && $subscription_vip  ) {
			$vip = 1;
		} // is global VIP enabled? If yes, launch as VIP even if user is no VIP
		elseif ( get_field( 'wpd_enable_global_vip_sites', 'options' )) {
			$vip = 1;
		} else {
			$vip = 0;
		}

		$blueprint_id   = null;
		$blueprint_hash = null;

		if ( 'blueprint' !== af_get_field( 'site_type' ) ) {
			$blueprint_id = Forms::instance()->get_form_blueprint( $form, $args );

			if ( $blueprint_id ) {
				$container = dollie()->get_container( $blueprint_id );

				$vip_blueprint = get_post_meta( $blueprint_id, 'launch_blueprint_as_vip', true );

				if ( $vip_blueprint ) {
					$vip = 1;
				}

				if ( ! is_wp_error( $container ) && $container->is_blueprint() ) {
					$blueprint_id   = $container->get_id();
					$blueprint_hash = $container->get_hash();
				}
			}
		}

		$redirect = '';
		if ( isset( $_POST['dollie_redirect'] ) && ! empty( $_POST['dollie_redirect'] ) ) {
			$redirect = sanitize_text_field( $_POST['dollie_redirect'] );
		}

		$deploy_data = [
			'owner_id'     => $owner_id,
			'blueprint_id' => $blueprint_id,
			'blueprint'    => $blueprint_hash,
			'email'        => $email,
			'username'     => af_get_field( 'admin_username' ),
			'password'     => af_get_field( 'admin_password' ),
			'name'         => af_get_field( 'site_name' ),
			'description'  => af_get_field( 'site_description' ),
			'vip'  		   => $vip,
			'redirect'     => $redirect,
		];

		$deploy_data = apply_filters( 'dollie/launch_site/form_deploy_data', $deploy_data );

		$domain_prefix = af_get_field( 'site_url' );
		$domain_prefix = strtolower( preg_replace( '/-+/', '-', ltrim( rtrim( trim( $domain_prefix ), '-' ), '-' ) ) );

		$container = DeployService::instance()->start(
			'blueprint' === af_get_field( 'site_type' ) ? self::TYPE_BLUEPRINT : self::TYPE_SITE,
			$domain_prefix,
			$deploy_data
		);

		if ( is_wp_error( $container ) ) {
			af_add_submission_error(
				esc_html__( 'Something went wrong. Please try again or contact our support if the problem persists.', 'dollie' )
			);

			return;
		}

		wp_redirect( $container->get_permalink() );
		exit();
	}

	/**
	 * Set default site URL using URL param.
	 *
	 * @param $field
	 *
	 * @return mixed
	 */
	public function set_default_site_url( $field ) {
		if ( isset( $_GET['default_site_url'] ) ) {
			$field['default_value'] = esc_attr( $_GET['default_site_url'] );
		}

		return $field;
	}

	/**
	 * Append site URL
	 *
	 * @param $field
	 *
	 * @return mixed
	 */
	public function append_site_url( $field ) {
		$deployment_domain = WorkspaceService::instance()->get_deployment_domain();

		$field['append'] = ".{$deployment_domain}";

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

		if ( ! empty( $blueprints ) && get_field( 'wpd_show_default_blueprint', 'options' ) ) {
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

			//Cookie is found, hide Blueprints selectio
			$field['wrapper']['class'] = 'acf-hidden';
		}

		return $field;
	}

	/**
	 * @param $form
	 * @param $args
	 */
	function hidden_field( $form, $args ) {
		$redirect = isset( $_GET['redirect'] ) ? sanitize_text_field( $_GET['redirect'] ) : '';
		$redirect = apply_filters( 'dollie/launch_site/redirect', $redirect );
		echo sprintf( '<input type="hidden" name="dollie_redirect" value="%s">', $redirect );
	}
}
