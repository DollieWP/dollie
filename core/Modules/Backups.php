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

		foreach ( dollie()->get_dollie_gravity_form_ids( 'dollie-create-backup' ) as $backup_id ) {
			add_action( 'gform_after_submission_' . $backup_id, [ $this, 'create_backup' ], 10, 2 );
		}
	}

	public function get_site_backups( $container_id = null , $force = false ) {

		/*if ( ob_get_length() > 0 ) {
			@ob_end_flush();
			@flush();
		}*/

		if( ! isset( $container_id ) ) {
		    if( ! is_singular( 'container' ) ) {
		        return [];
            }

			$currentQuery           = dollie()->get_current_object();
			$container_id = $currentQuery->id;
			$container_slug = $currentQuery->slug;

		} else {
			$container_slug = get_post( $container_id )->post_name;
		}

		$backups_transient_name = 'dollie_' . $container_slug . '_backups_data';

		if ( ! $force && $data = get_transient( $backups_transient_name ) ) {
			$backups = $data;
		} else {
			$secret = get_post_meta( $container_id, 'wpd_container_secret', true );
			$url    = dollie()->get_container_url() . '/' . $secret . '/codiad/backups/';

			$response = wp_remote_get( $url, [
				'timeout' => 20
			] );

			if ( is_wp_error( $response ) ) {
				return [];
			}

			$backups = json_decode( wp_remote_retrieve_body( $response ), true );

		}

		if ( empty( $backups ) ) {
			return [];
		}

		$total_backups = array_filter( $backups, static function ( $value ) {
			return ! ( strpos( $value, 'restore' ) !== false );
		} );

		set_transient( 'dollie_' . $container_slug . '_total_backups', count( $total_backups ), MINUTE_IN_SECONDS * 1 );
		update_post_meta( $container_id, 'wpd_installation_backups_available', count( $total_backups ) );

		set_transient( $backups_transient_name, $backups, 15 );

		return $backups;

	}

	public function get_customer_total_backups() {
		$currentQuery = dollie()->get_current_object();

		return get_transient( 'dollie_' . $currentQuery->slug . '_total_backups' );
	}


	public function trigger_backup() {
		$currentQuery = dollie()->get_current_object();

		Api::post( Api::ROUTE_BACKUP_CREATE, [ 'container_url' => dollie()->get_container_url() ] );
		Log::add( $currentQuery->slug . ' has triggered a backup', '', 'action' );
	}

	public function list_site_restores() {
		// Grab Some Recent Posts
		$backups = $this->get_site_backups();

		if ( empty( $backups ) ) {
			?>

            <div class="history">
				<?php _e( 'You have not restored your site yet.', 'dollie' ); ?>
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
				echo '<p class="padding-half">' . __( 'You have never restored your site.', 'dollie' ) . '</p>';
			}
			echo '</ul>';
		}
	}

	public function create_backup( $entry, $form ) {
		$this->trigger_backup();
		?>
        <div class="box-brand-secondary padding-full box-full margin-top-full create-backup-notice">
			<?php esc_html_e( 'We\'re building your backup! You\'ll see it appear in the backup list on the left once it\'s done! If you have a large site this might take a while!', 'dollie' ); ?>
        </div>
		<?php
	}

}
