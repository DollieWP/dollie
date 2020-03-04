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
	private $forms_version = '2.0.2';

	/**
	 * Option name that gets saved in the options database table
	 *
	 * @var string
	 */
	private $option_name = 'dollie_forms_version';

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

	public function needs_update() {

		// Check for database version
		$db_version = get_option( $this->option_name ) ?: '1.0.0';

		// If we need an update
		if ( version_compare( $this->forms_version, $db_version, '>' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Create or update all forms with the new data
	 *
	 * @return bool
	 */
	public function import_gravity_forms() {
		if ( ! class_exists( 'GFAPI' ) ) {
			return false;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		// If we don't need to update.
		if( ! $this->needs_update() ) {
			return true;
		}

		$success = true;

		foreach ( $this->forms as $form_slug ) {
			$current_form = dollie()->get_dollie_gravity_form_ids( $form_slug, false );
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

		if ( $success === true ) {
			Log::add( 'Forms successfully imported' );
			update_option( $this->option_name, $this->forms_version );

			Tools::instance()->remove_forms_ids_transient();
		}

		return $success;
	}
}
