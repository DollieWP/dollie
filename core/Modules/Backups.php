<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;
use Dollie\Core\Log;

/**
 * Class Backups
 * @package Dollie\Core\Modules
 */
class Backups extends Singleton {

	/**
	 * Backups constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'wf_before_container', [ $this, 'get_site_backups' ], 11 );
		add_filter( 'gform_pre_render', [ $this, 'list_site_backups' ] );

		foreach ( dollie()->helpers()->get_dollie_gravity_form_ids( 'dollie-list-backups' ) as $form_id ) {
			add_action( 'gform_after_submission_' . $form_id, [ $this, 'restore_site' ], 10, 2 );
		}

		foreach ( dollie()->helpers()->get_dollie_gravity_form_ids( 'dollie-create-backup' ) as $backup_id ) {
			add_action( 'gform_after_submission_' . $backup_id, [ $this, 'create_backup' ], 10, 2 );
		}
	}

	public function get_site_backups() {
		if ( isset( $_GET['page'] ) && $_GET['page'] === 'backups' && is_singular( 'container' ) ) {
			if ( ob_get_length() > 0 ) {
				@ob_end_flush();
				@flush();
			}

			global $wp_query;
			$post_id   = $wp_query->get_queried_object_id();
			$post_slug = get_queried_object()->post_name;
			$install   = $post_slug;
			$secret    = get_post_meta( $post_id, 'wpd_container_secret', true );
			$url       = dollie()->helpers()->get_container_url( $post_id ) . '/' . $secret . '/codiad/backups/';

			$response = wp_remote_get( $url, [
				'timeout' => 20
			] );

			if ( is_wp_error( $response ) ) {
				return [];
			}

			$backups = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( empty( $backups ) ) {
				return [];
			}

			$total_backups = array_filter( $backups, static function ( $value ) {
				return ! ( strpos( $value, 'restore' ) !== false );
			} );

			set_transient( 'dollie_' . $install . '_total_backups', count( $total_backups ), MINUTE_IN_SECONDS * 1 );
			update_post_meta( $post_id, 'wpd_installation_backups_available', count( $total_backups ) );

			return $backups;
		}

		return [];
	}

	public function list_site_backups( $form ) {
		foreach ( $form['fields'] as $field ) {
			if ( $field['type'] !== 'radio' || strpos( $field['cssClass'], 'site-backups' ) === false ) {
				continue;
			}

			// Grab our array of available backups
			$backups = $this->get_site_backups();
			$choices = [];

			if ( empty( $backups ) ) {
				?>

                <div id="no-backups-created" class="blockquote-box blockquote-info clearfix">
                    <div class="square pull-left">
                        <i class="fal fa-hdd"></i>
                    </div>
                    <h4 class="padding-bottom-none margin-top-none">
						<?php _e( 'We could not retrieve your backups.', DOLLIE_SLUG ); ?>
                    </h4>
                    <p>
						<?php _e( 'This usually means we have trouble reaching your WordPress installation. Please get in touch with our support of you keep seeing this message.', DOLLIE_SLUG ); ?>
                    </p>
                </div>

				<?php
			} else {
				foreach ( $backups as $backup ) {
					// Split info via pipe
					$info = explode( '|', $backup );
					if ( $info[1] === 'restore' ) {
						continue;
					}

					if ( strpos( $info[1], 'MB' ) !== false ) {
						$get_mb_size = str_replace( 'MB', '', $info[1] );
						$real_size   = $get_mb_size - 0 . ' MB';
					} else {
						$real_size = $info[1];
					}

					$size = '<span class="pull-right"><i class="fal fa-hdd-o"></i>' . $real_size . '</span>';
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

					$choices[] = [
						'text'  => "<i class='fa fa-calendar-o'></i>" . date( 'd F y', $date ) . $time . $size,
						'value' => $duplicity_time
					];
				}
			}

			$field['choices'] = $choices;
		}

		return $form;
	}

	public function get_customer_total_backups() {
		return get_transient( 'dollie_' . get_queried_object()->post_name . '_total_backups' );
	}

	function restore_site( $entry, $form ) {
		global $wp_query;
		$install = dollie()->helpers()->get_container_url( $wp_query->get_queried_object_id() );

		// Our form field ID + User meta fields
		$backup = rgar( $entry, '1' );
		$type   = rgar( $entry, '2' );


		$backup_type = '';
		if ( $type === 'full' ) {
			$backup_type = 'a32bf123-fe75-4664-962f-b6901e28b5da';
		}
		if ( $type === 'files-only' ) {
			$backup_type = '40b2af3e-eaab-4469-8001-24c43186fa40';
		}
		if ( $type === 'database-only' ) {
			$backup_type = '4b766076-2dfd-475b-bc18-c4eb1407cc5d';
		}

		if ( $backup_type ) {
			// Only run the job on the container of the customer.
			$post_body = [
				'filter'    => 'name: ' . $install . '-' . DOLLIE_RUNDECK_KEY,
				'argString' => '-backup ' . $backup
			];

			Api::postRequestRundeck( '1/job/' . $backup_type . '/run/', $post_body );
		}

		?>
        <div class="alert alert-success">
			<?php printf(
				__( 'Your site is being restored! Depending on the size of your installation this could take a while. Once your site is restored you\'ll see a message in your <a href="%s">WordPress Admin</a>', DOLLIE_SLUG ),
				esc_url( dollie()->helpers()->get_customer_login_url() )
			); ?>
            <br>
			<?php printf(
				__( 'Note: In some cases you might have to <a href="%s">login</a> to your site again after a restoration.', DOLLIE_SLUG ),
				esc_url( dollie()->helpers()->get_customer_login_url() )
			); ?>
        </div>
		<?php
	}

	public function trigger_backup() {
		global $wp_query;
		$install = dollie()->helpers()->get_container_url( $wp_query->get_queried_object_id() );

		// Success now send the Rundeck request
		// Only run the job on the container of the customer.
		$post_body = [
			'filter' => 'name: ' . $install . '-' . DOLLIE_RUNDECK_KEY
		];

		Api::postRequestRundeck( '1/job/6b51b1a4-bcc7-4c2c-a799-b024e561c87f/run/', $post_body );
		Log::add( get_queried_object()->post_name . ' has triggered a backup', '', 'action' );
	}

	public function list_site_restores() {
		// Grab Some Recent Posts
		$backups = $this->get_site_backups();

		if ( empty( $backups ) ) {
			?>

            <div class="history">
				<?php _e( 'You have not restored your site yet.', DOLLIE_SLUG ); ?>
            </div>

			<?php
		} else {
			echo '<ul class="list-group list-unstyled box-full font-size-smaller">';
			$count = 0;
			foreach ( $backups as $backup ) {
				// Split info via pipe
				$info = explode( '|', $backup );

				if ( $info[1] !== 'restore' ) {
					continue;
				}

				// Time is firsts part but needs to be split
				$backup_date = explode( '_', $info[0] );

				// Date of backup
				$date        = strtotime( $backup_date[0] );
				$raw_time    = str_replace( '-', ':', $backup_date[1] );
				$pretty_time = date( 'g:i a', strtotime( $raw_time ) );

				// Time of backup
				$time = ' at ' . $pretty_time . '';

				echo "<li class='list-group-item'>" . date( 'd F y', $date ) . $time . '</li>';

				$count ++;
			}
			if ( $count === 0 ) {
				echo '<p class="padding-half">' . __( 'You have never restored your site.', DOLLIE_SLUG ) . '</p>';
			}
			echo '</ul>';
		}
	}

	public function create_backup( $entry, $form ) {
		$this->trigger_backup();
		?>
        <div class="box-brand-secondary padding-full box-full margin-top-full create-backup-notice">
			<?php esc_html_e( 'We\'re building your backup! You\'ll see it appear in the backup list on the left once it\'s done! If you have a large site this might take a while!', DOLLIE_SLUG ); ?>
        </div>
		<?php
	}

}
