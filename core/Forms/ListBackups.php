<?php

namespace Dollie\Core\Forms;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Modules\Forms;
use Dollie\Core\Singleton;

/**
 * Class ListBackups
 *
 * @package Dollie\Core\Forms
 */
class ListBackups extends Singleton {
	/**
	 * @var string
	 */
	private $form_key = 'form_dollie_list_backups';

	/**
	 * ListBackups constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'acf/init', [ $this, 'acf_init' ] );
	}

	/**
	 * Init ACF
	 */
	public function acf_init() {
		// Placeholders/Change values.
		add_filter( 'acf/load_field/name=site_backup', [ $this, 'populate_site_backups' ] );

		// Form args.
		add_filter( 'af/form/args/key=' . $this->form_key, [ $this, 'change_form_args' ] );

		// Form submission action.
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
		$container = dollie()->get_container( (int) $_POST['dollie_post_id'] );

		if ( is_wp_error( $container ) ) {
			return;
		}

		$container->restore_backup( Forms::get_field( 'site_backup' ), Forms::get_field( 'what_to_restore' ) );
	}

	/**
	 * Change form args
	 *
	 * @param $args
	 *
	 * @return mixed
	 */
	public function change_form_args( $args ) {
		$args['submit_text'] = __( 'Restore Backup', 'dollie' );

		return $args;
	}

	/**
	 * Populate site backups
	 *
	 * @param $field
	 *
	 * @return mixed
	 */
	public function populate_site_backups( $field ) {
		$container = dollie()->get_container();

		if ( is_wp_error( $container ) ) {
			$field['choices'] = [];

			return $field;
		}

		$backups = $container->get_backups();
		$choices = [];

		if ( ! empty( $backups ) ) {
			foreach ( $backups as $backup ) {
				$choices[ $backup['id'] ] = dollie()->icon()->clock( 'dol-mr-2' ) . "{$backup['date']} at {$backup['hour']}";
			}
		}

		$field['choices'] = $choices;

		return $field;
	}
}
