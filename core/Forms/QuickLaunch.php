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
		$blueprint = Forms::instance()->get_form_blueprint( $form, $args );
		$site_type = 'site';

		$user_id = get_current_user_id();

		// If we allow registration and not logged in - create account
		if ( ! is_user_logged_in() && get_option( 'users_can_register' ) ) {
			$user_id       = username_exists( $email );
			$user_password = af_get_field( 'client_password' ) ?: wp_generate_password( $length = 12, $include_standard_special_chars = false );

			if ( ! $user_id && false === email_exists( $email ) ) {
				$user_id = wp_create_user( $email, $user_password, $email );
				update_user_meta( $user_id, 'first_name', af_get_field( 'client_name' ) );
			} else {
				af_add_error( 'client_email', __( 'Email already exists. Please login first', 'dollie' ) );

				return;
			}
		}

		if ( ! $user_id ) {
			return;
		}

		$deploy_data = compact( 'email', 'domain', 'user_id', 'blueprint' );
		$deploy_data = apply_filters( 'dollie/launch_site/form_deploy_data', $deploy_data, $domain, $blueprint );

		// add WP site details.
		$setup_data = [
			'email'    => af_get_field( 'client_email' ),
			'username' => sanitize_title( af_get_field( 'client_name' ) ),
		];

		DeployService::instance()->start( self::TYPE_SITE, $setup_data );
	}

	/**
	 * Change form args
	 *
	 * @param $args
	 *
	 * @return mixed
	 */
	public function change_form_args( $args ) {
		$args['submit_text'] = printf( esc_html__( 'Launch New %s', 'dollie-setup' ), dollie()->string_variants()->get_site_type_string() );

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
