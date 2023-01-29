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
 * Class Onboarding
 *
 * @package Dollie\Core\Forms
 */
class Onboarding extends Singleton implements ConstInterface {
	/**
	 * @var string
	 */
	private $form_key = 'form_dollie_agency_onboarding';

	/**
	 * Onboarding constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'acf/init', array( $this, 'acf_init' ) );
	}

	/**
	 * Init ACF
	 */
	public function acf_init() {
		// Form args
		// add_filter( 'af/form/args/key=' . $this->form_key, array( $this, 'change_form_args' ) );

		add_filter( 'af/field/before_render', array( $this, 'modify_fields' ), 10, 3 );
		add_action( 'af/form/submission/key=' . $this->form_key, array( $this, 'submission_callback' ), 10, 3 );
	}

	/**
	 * Callback
	 *
	 * @param $form
	 * @param $fields
	 * @param $args
	 */
	public function submission_callback( $form, $fields, $args ) {

		$user_id = get_current_user_id();

		// Form setup complete, redirect to next step by reloading the page.
		update_site_option( '_dollie_setup_current_package', 'agency' );

		update_site_option( 'wpd_onboarding_partner_business_name', af_get_field( 'wpd_onboarding_partner_business_name' ) );

		// If Agency has selected Blueprint option
		if ( af_get_field( 'wpd_onboarding_enable_blueprint' ) == 1 ) {
			update_site_option( 'wpd_onboarding_blueprint_name', af_get_field( 'wpd_onboarding_blueprint_name' ) );
		}

		// If Agency has selected Guided Migration option
		if ( af_get_field( 'wpd_onboarding_migrate_site' ) == 1 ) {
			update_site_option( 'wpd_onboarding_migrate_site_url', af_get_field( 'wpd_onboarding_migrate_site_url' ) );
		}


	}

	/**
	 * Change form args
	 *
	 * @param $args
	 *
	 * @return mixed
	 */
	public function change_form_args( $args ) {
		$args['submit_text'] = printf( esc_html__( 'Continue Setup', 'dollie-setup' ), dollie()->string_variants()->get_site_type_string() );

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
