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
use Nubs\RandomNameGenerator\Alliteration as NameGenerator;

/**
 * Class QuickLaunch
 * @package Dollie\Core\Modules\Forms
 */
class QuickLaunch extends Singleton {

	private $form_key = 'form_dollie_quick_launch';

	/**
	 * QuickLaunch constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'acf/init', [ $this, 'acf_init' ] );

	}

	public function acf_init() {

		// Form args
		add_filter( 'af/form/args/key=' . $this->form_key, [ $this, 'change_form_args' ] );

		add_filter( 'af/field/before_render', [ $this, 'modify_fields' ], 10, 3 );
		add_action( 'af/form/before_submission/key=' . $this->form_key, array( $this, 'submission_callback' ), 10, 3 );

	}


	public function submission_callback( $form, $fields, $args ) {

		$generator = new NameGenerator();
		$domain    = strtolower( str_replace( ' ', '-', $generator->getName() ) );

		$email     = af_get_field( 'client_email' );
		$blueprint = isset( $_COOKIE[ Blueprints::COOKIE_NAME ] ) ? $_COOKIE[ Blueprints::COOKIE_NAME ] : Forms::instance()->get_form_arg( 'site_blueprint', $form, $args );
		$demo      = esc_url_raw( get_site_url() );

		// If we allow registration and not logged in - create account
		if ( ! is_user_logged_in() && get_option( 'users_can_register' ) ) {

			$user_id       = username_exists( $email );
			$user_password = af_get_field( 'client_password' ) ?: wp_generate_password( $length = 12, $include_standard_special_chars = false );

			if ( ! $user_id && false === email_exists( $email ) ) {
				$user_id = wp_create_user( $email, $user_password, $email );
				update_user_meta( $user_id, 'first_name', af_get_field( 'client_name' ) );
			} else {
				af_add_error( 'client_email', esc_html__( 'Email already exists. Please login first', 'dollie' ) );
			}
		} else {
			$user_id = get_current_user_id();
		}

		$deploy_data = WP::instance()->deploy_site( $email, $domain, $user_id, $blueprint );

		if ( is_wp_error( $deploy_data ) ) {
			af_add_submission_error( $deploy_data->get_error_message() );

			return;
		}

		$data_container = $deploy_data['data_container'];
		$post_id        = $deploy_data['post_id'];

		// add WP site details
		$password = wp_generate_password( 8, false );

		$data = [
			'container_uri' => $data_container['uri'],
			'email'         => af_get_field( 'client_email' ),
			'name'          => esc_html__( 'My WP', 'dollie' ),
			'description'   => esc_html__( 'My WordPress Install', 'dollie' ),
			'username'      => sanitize_title( af_get_field( 'client_name' ) ),
			'password'      => $password,
		];

		$status = WP::instance()->update_site_details( $data, $post_id );

		if ( is_wp_error( $status ) ) {
			af_add_submission_error( $status->get_error_message() );
		}

		//Save our container ID
		AF()->submission['extra']['dollie_container_id'] = $post_id;

	}

	public function change_form_args( $args ) {
		$args['submit_text'] = esc_html__( 'Launch New Site', 'dollie' );

		return $args;
	}


	public function modify_fields( $field, $form, $args ) {

		if ( $form['key'] !== $this->form_key ) {
			return $field;
		}

		if ( is_user_logged_in() ) {

			$user = wp_get_current_user();

			if ( $field['name'] === 'client_password' ) {
				$field['wrapper']['class'] = 'acf-hidden';
			}

			if ( $field['name'] === 'client_name' ) {
				$field['value']            = $user->display_name;
				$field['wrapper']['width'] = '50';
			}

			if ( $field['name'] === 'client_email' ) {
				$field['value'] = $user->user_email;
			}

		} elseif ( ! get_option( 'users_can_register' ) ) {

			if ( $field['name'] === 'client_name' ) {
				$field['wrapper']['width'] = '50';
			}

			if ( $field['name'] === 'client_password' ) {
				$field['wrapper']['class'] = 'acf-hidden';
			}

		}

		return $field;
	}


}
