<?php

namespace Dollie\Core\Modules\Forms;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Modules\Backups;
use Dollie\Core\Modules\Forms;
use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;

/**
 * Class ListBackups
 *
 * @package Dollie\Core\Modules\Forms
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
		// Restrictions.
		add_filter( 'af/form/restriction/key=' . $this->form_key, [ $this, 'restrict_form' ], 10 );

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
		$container_id = (int) $_POST['dollie_post_id'];

		if ( $container_id <= 0 ) {
			return;
		}

		Api::process_response(
			Api::post(
				Api::ROUTE_BACKUP_RESTORE,
				[
					'container_uri' => dollie()->get_wp_site_data( 'uri', $container_id ),
					'backup'        => Forms::get_field( 'site_backup' ),
					'backup_type'   => Forms::get_field( 'what_to_restore' ),
				]
			)
		);
	}

	/**
	 * If no backups, restrict the forms and show a message
	 *
	 * @param bool $restriction
	 *
	 * @return bool|string
	 */
	public function restrict_form( $restriction = false ) {
		// Added in case another restriction already applies.
		if ( $restriction ) {
			return $restriction;
		}

		$backups = Backups::instance()->get();

		if ( false === $backups ) {

			return dollie()->load_template(
				'notice',
				[
					'type'         => 'info',
					'icon'         => 'fas fa-hdd',
					'title'        => __( 'We could not retrieve your backups', 'dollie' ),
					'message'      => __( 'This usually means we have trouble reaching your WordPress installation. Please get in touch with our support of you keep seeing this message.', 'dollie' ),
					'bottom_space' => true,
				],
				false
			);

		}

		return $restriction;
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
		// Grab our array of available backups.
		$backups = Backups::instance()->get();
		$choices = [];

		if ( ! empty( $backups ) ) {

			foreach ( $backups as $backup ) {

				if ( is_array( $backup ) ) {
					continue;
				}

				// Split info via pipe.
				$info = explode( '|', $backup );
				if ( 'restore' === $info[1] ) {
					continue;
				}

				if ( strpos( $info[1], 'MB' ) !== false ) {
					$get_mb_size = (float) str_replace( 'MB', '', $info[1] );

					$real_size = $get_mb_size . ' MB';
				} else {
					$real_size = $info[1];
				}

				$size = '<span class="dol-inline-block dol-ml-4">' . dollie()->get_icon_backups() . $real_size . '</span>';
				// Time is first part but needs to be split.
				$backup_date = explode( '_', $info[0] );
				// Date of backup.
				$date        = strtotime( $backup_date[0] );
				$raw_time    = str_replace( '-', ':', $backup_date[1] );
				$pretty_time = date( 'g:i a', strtotime( $raw_time ) );

				// Time of backup.
				$time = ' at ' . $pretty_time . '';
				// Size of backup
				// Format for compat with duplicity.
				$format_time    = str_replace( '-', ':', $backup_date[1] );
				$duplicity_time = $backup_date[0] . 'T' . $format_time . ':00';

				$choices[ $duplicity_time ] = dollie()->get_icon_clock() . date( 'd F y', $date ) . $time . $size;
			}
		}

		$field['choices'] = $choices;

		return $field;
	}

}
