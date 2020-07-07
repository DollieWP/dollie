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
     * Get backups
     *
	 * @param null $container_id
	 * @param bool $force
	 *
	 * @return array|mixed
	 */
	public function get_site_backups( $container_id = null, $force = false ) {
		if ( ! isset( $container_id ) ) {
			if ( ! is_singular( 'container' ) ) {
				return [];
			}

			$currentQuery   = dollie()->get_current_object();
			$container_id   = $currentQuery->id;
			$container_slug = $currentQuery->slug;
		} else {
			$container_slug = get_post( $container_id )->post_name;
		}

		$backups_transient_name = 'dollie_' . $container_slug . '_backups_data';

		if ( ! $force && $data = get_transient( $backups_transient_name ) ) {
			$backups = $data;
		} else {
			$secret = get_post_meta( $container_id, 'wpd_container_secret', true );

			$requestGetBackup = Api::post( Api::ROUTE_BACKUP_GET, [
				'container_url'    => dollie()->get_container_url(),
				'container_secret' => $secret
			] );

			if ( is_wp_error( $requestGetBackup ) ) {
				return [];
			}

			$responseGetBackup = json_decode( wp_remote_retrieve_body( $requestGetBackup ), true );

			if ( $responseGetBackup['status'] === 500 ) {
				return [];
			}

			$backups = json_decode( $responseGetBackup['body'], true );
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

	/**
     * Get customer's total backups
     *
	 * @return mixed
	 */
	public function get_customer_total_backups() {
		$currentQuery = dollie()->get_current_object();

		return get_transient( 'dollie_' . $currentQuery->slug . '_total_backups' );
	}

	/**
	 * List restores
	 */
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

	/**
     * Create a backup
     *
	 * @param null $container_id
	 *
	 * @return bool
	 */
	public function trigger_backup( $container_id = null ) {

		$container = dollie()->get_current_object( $container_id );

		if ( $container->id === 0 ) {
			return false;
		}

		$container_uri = get_post_meta( $container_id, 'wpd_container_uri', true );

		Api::post( Api::ROUTE_BACKUP_CREATE, [ 'container_uri' => $container_uri ] );
		Log::add( $container->slug . ' has triggered a backup', '', 'action' );

		return true;
    }

}
