<?php


namespace Dollie\Core\Forms;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Modules\Forms;
use Dollie\Core\Services\DeployService;
use Dollie\Core\Singleton;
use Dollie\Core\Utils\ConstInterface;
use Nubs\RandomNameGenerator\Alliteration as NameGenerator;

/**
 * Class QuickLaunch
 *
 * @package Dollie\Core\Forms
 */
class QuickLaunch extends Singleton implements ConstInterface {
	/**
	 * @var string
	 */
	private $form_key = 'form_dollie_quick_launch';

	/**
	 * QuickLaunch constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'acf/init', [ $this, 'acf_init' ] );
	}

	/**
	 * Init ACF
	 */
	public function acf_init() {
		// Form args
		add_filter( 'af/form/args/key=' . $this->form_key, [ $this, 'change_form_args' ] );

		add_filter( 'af/field/before_render', [ $this, 'modify_fields' ], 10, 3 );
		add_action( 'af/form/before_submission/key=' . $this->form_key, [ $this, 'submission_callback' ], 10, 3 );
	}

	/**
	 * Callback
	 *
	 * @param $form
	 * @param $fields
	 * @param $args
	 */
	public function submission_callback( $form, $fields, $args ) {
		$generator = new NameGenerator();
		$domain    = strtolower( str_replace( ' ', '', $generator->getName() ) );
		$email     = af_get_field( 'client_email' );

		$user_id = get_current_user_id();

		// If we allow registration and not logged in - create account.
		if ( ! is_user_logged_in() && get_option( 'users_can_register' ) ) {
			$user_id       = username_exists( $email );
			$user_password = af_get_field( 'client_password' ) ?: wp_generate_password( $length = 12, $include_standard_special_chars = false );

			if ( ! $user_id && false === email_exists( $email ) ) {
				$user_id = wp_create_user( $email, $user_password, $email );
				update_user_meta( $user_id, 'first_name', af_get_field( 'client_name' ) );

				// Login user.
				wp_clear_auth_cookie();
				wp_set_current_user( $user_id ); // Set the current user detail.
				wp_set_auth_cookie( $user_id ); // Set auth details in cookie.

			} else {
				af_add_error( 'client_email', __( 'Email already exists. Please login first', 'dollie' ) );

				return;
			}
		}

		if ( ! $user_id ) {
			return;
		}

		$blueprint_hash = null;
		$blueprint_id   = Forms::instance()->get_form_blueprint( $form, $args );

		if ( $blueprint_id ) {
			$container = dollie()->get_container( $blueprint_id );

			if ( ! is_wp_error( $container ) && $container->is_blueprint() ) {
				$blueprint_id   = $container->get_id();
				$blueprint_hash = $container->get_hash();
			}
		}

		$redirect = '';
		if ( isset( $_POST['dollie_redirect'] ) && ! empty( $_POST['dollie_redirect'] ) ) {
			$redirect = sanitize_text_field( $_POST['dollie_redirect'] );
		}

		$deploy_data = [
			'owner_id'     => $user_id,
			'blueprint_id' => $blueprint_id,
			'blueprint'    => $blueprint_hash,
			'email'        => $email,
			'username'     => af_get_field( 'client_name' ),
			'redirect'     => $redirect,
		];

		$deploy_data = apply_filters( 'dollie/launch_site/form_deploy_data', $deploy_data );

		$container = DeployService::instance()->start(
			self::TYPE_SITE,
			$domain,
			$deploy_data
		);

		if ( is_wp_error( $container ) ) {
			af_add_submission_error(
				esc_html__( 'Something went wrong. Please try again or contact our support if the problem persists.', 'dollie' )
			);
		}

		wp_redirect( $container->get_permalink() );
		exit();
	}

	/**
	 * Change form args
	 *
	 * @param $args
	 *
	 * @return mixed
	 */
	public function change_form_args( $args ) {
		$args['submit_text'] = esc_html__( 'Launch New Site', 'dollie-setup' );

		return $args;
	}

	/**
	 * Modify fields
	 *
	 * @param $field
	 * @param $form
	 * @param $args
	 *
	 * @return mixed
	 */
	public function modify_fields( $field, $form, $args ) {
		if ( $form['key'] !== $this->form_key ) {
			return $field;
		}

		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();

			if ( 'client_password' === $field['name'] ) {
				$field['wrapper']['class'] = 'acf-hidden';
			}

			if ( 'client_name' === $field['name'] ) {
				$field['value']            = $user->display_name;
				$field['wrapper']['width'] = '50';
			}

			if ( 'client_email' === $field['name'] ) {
				$field['value'] = $user->user_email;
			}
		} elseif ( ! get_option( 'users_can_register' ) ) {
			if ( 'client_name' === $field['name'] ) {
				$field['wrapper']['width'] = '50';
			}

			if ( 'client_password' === $field['name'] ) {
				$field['wrapper']['class'] = 'acf-hidden';
			}
		}

		return $field;
	}
}
