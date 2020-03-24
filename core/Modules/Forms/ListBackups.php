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
 * @package Dollie\Core\Modules\Forms
 */
class ListBackups extends Singleton {

    private $form_key = 'form_dollie_list_backups';

	/**
	 * ListBackups constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'acf/init', [ $this, 'acf_init' ] );
	}

	public function acf_init() {


	    // Restrictions
		add_filter( 'af/form/restriction/key=form_dollie_list_backups', [ $this, 'restrict_form' ], 10 );

		// Placeholders/Change values.
		add_filter( 'acf/load_field/name=site_backup', [ $this, 'site_backups_populate' ] );

		// Form submission action.
		add_action( 'af/form/before_submission/key=' . $this->form_key, [ $this, 'submission_callback' ], 10, 3 );

	}

	public function submission_callback( $form, $fields, $args ) {

		Api::post( Api::ROUTE_BACKUP_RESTORE, [
			'container_url' => dollie()->get_container_url(),
			'backup'        => Forms::get_field( 'site_backup' ),
			'backup_type'   => Forms::get_field( 'what_to_restore' ),
		] );

	}


	/**
     * If no backups, restrict the forms and show a message
     *
	 * @param bool $restriction
	 *
	 * @return bool|string
	 */
	public function restrict_form( $restriction = false ) {

		// Added in case another restriction already applies
		if ( $restriction ) {
			return $restriction;
		}

		$backups = Backups::instance()->get_site_backups();
		if ( empty( $backups ) ) {

			$data = '';
			ob_start();
			?>
            <div id="no-backups-created" class="blockquote-box blockquote-info clearfix">
                <div class="square pull-left">
                    <i class="fal fa-hdd"></i>
                </div>
                <h4 class="padding-bottom-none margin-top-none">
					<?php esc_html_e( 'We could not retrieve your backups.', 'dollie' ); ?>
                </h4>
                <p>
					<?php esc_html_e( 'This usually means we have trouble reaching your WordPress installation. Please get in touch with our support of you keep seeing this message.', 'dollie' ); ?>
                </p>
            </div>
			<?php

			$data .= ob_get_clean();

			return $data;
		}

		return $restriction;

	}

	public function site_backups_populate( $field ) {

		// Grab our array of available backups
		$backups = Backups::instance()->get_site_backups();
		$choices = [];

		if ( ! empty( $backups ) ) {

			foreach ( $backups as $backup ) {

				// Split info via pipe.
				$info = explode( '|', $backup );
				if ( $info[1] === 'restore' ) {
					continue;
				}

				if ( strpos( $info[1], 'MB' ) !== false ) {
					$get_mb_size = (float) str_replace( 'MB', '', $info[1] );

					$real_size = $get_mb_size . ' MB';
				} else {
					$real_size = $info[1];
				}

				$size = '&nbsp; <span class="pull-right"><i class="fal fa-hdd"></i> ' . $real_size . '</span>';
				// Time is first part but needs to be split
				$backup_date = explode( '_', $info[0] );
				// Date of backup
				$date        = strtotime( $backup_date[0] );
				$raw_time    = str_replace( '-', ':', $backup_date[1] );
				$pretty_time = date( 'g:i a', strtotime( $raw_time ) );

				// Time of backup
				$time = ' at ' . $pretty_time . '';
				// Size of backup
				// Format for compat with duplicity.
				$format_time    = str_replace( '-', ':', $backup_date[1] );
				$duplicity_time = $backup_date[0] . 'T' . $format_time . ':00';

				$choices[ $duplicity_time ] = "<i class='fa fa-calendar-o'></i>" . date( 'd F y', $date ) . $time . $size;
			}
		}

		$field['choices'] = $choices;

		return $field;
	}

}
