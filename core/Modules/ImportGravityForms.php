<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Log;
use Dollie\Core\Singleton;
use GFAPI;

/**
 * Class ImportGravityForms
 * @package Dollie\Core\Modules
 */
class ImportGravityForms extends Singleton {

	/**
	 * Current plugin forms version
	 * @var string
	 */
	private $forms_version = '2.0.0';

	/**
	 * Keep track if the forms were updated during current request
	 * @var bool
	 */
	private $forms_updated = false;

	/**
	 * Current plugin forms
	 * @var array
	 */
	private $forms = [
		'dollie-blueprint',
		'dollie-create-backup',
		'dollie-delete',
		'dollie-domain',
		'dollie-launch',
		'dollie-list-backups',
		'dollie-performance',
		'dollie-support',
		'dollie-updates',
		'dollie-wizard',
	];

	/**
	 * ImportGravityForms constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'admin_notices', [ $this, 'admin_notice' ] );
		add_action( 'gform_after_save_form', [ $this, 'remove_forms_ids_transient' ] );

	}

	/**
	 * Create or update all forms with the new data
	 *
	 * @return bool
	 */
	private function import_gravity_forms() {
		if ( ! class_exists( 'GFAPI' ) ) {
			return false;
		}

		$success = true;

		foreach ( $this->forms as $form_slug ) {
			$current_form = dollie()->get_dollie_gravity_form_ids( $form_slug );
			$path         = DOLLIE_CORE_PATH . 'Extras/gravity/' . $form_slug . '.json';

			if ( file_exists( $path ) ) {
				$form = file_get_contents( $path );

				if ( $form ) {
					$form = json_decode( $form, true );
					if ( ! empty( $form ) ) {
						$form = $form[0];

						// Add the form
						if ( empty( $current_form ) ) {
							$result = GFAPI::add_form( $form );
						} else {

							//Update the form
							$result = GFAPI::update_form( $form, $current_form[0] );
						}

						if ( is_wp_error( $result ) ) {
							$success = false;
							Log::add( 'Form import error', $result->get_error_message() );
						}
					}
				}
			}
		}

		return $success;
	}

	/**
	 * Show admin notice to update Dollie forms
	 */
	public function admin_notice() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Check for database version
		$db_version = get_option( 'dollie_forms_version' ) ?: '1.0';

		// If we need to update
		if ( version_compare( $this->forms_version, $db_version, '>' ) ) {

			$this->process_update_action();

			// If we still need to show the message
			if ( ! $this->forms_updated ) {
				$url = wp_nonce_url( add_query_arg( 'dollie_gforms_update', '' ), 'action' );

				echo '<div class="notice notice-warning"><p>';
				echo wp_kses_post( sprintf(
					__( '<strong>Dollie</strong> plugin needs to update existing forms. <a href="%s">Update now</a>', 'dollie' ),
					esc_url( $url )
				) );
				echo '</p></div>';
			}
		}
	}

	/**
	 * Update the database with the new forms
	 */
	private function process_update_action() {
		if ( isset( $_REQUEST['dollie_gforms_update'] ) ) {
			$nonce = $_REQUEST['_wpnonce'];

			if ( wp_verify_nonce( $nonce, 'action' ) && $this->import_gravity_forms() ) {
				update_option( 'dollie_forms_version', $this->forms_version );
				$this->forms_updated = true;
			}

			if ( $this->forms_updated === true ) {
				echo '<div class="notice notice-success">
            		 <p>Awesome, forms are now at the latest version!</p>
         		</div>';
			} else {
				echo '<div class="notice notice-warning">
            		 <p>Something went wrong, please try again.</p>
         		</div>';
			}
		}
	}

	/**
	 * Remove cached data for gravity form ids mapping
	 */
	public function remove_forms_ids_transient() {
		delete_transient( 'dollie_gform_ids' );
	}
}
