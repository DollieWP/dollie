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
 *
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
	public function get( $container_id = null, $force = false ) {
		$current_query          = dollie()->get_current_object( $container_id );
		$container_slug         = $current_query->slug;
		$backups_transient_name = 'dollie_' . $container_slug . '_backups_data';

		if ( get_transient( $backups_transient_name ) ) {
			$backups = get_transient( $backups_transient_name );
		} else {
			$backups       = [];
			$secret        = get_post_meta( $container_id, 'wpd_container_secret', true );
			$container_url = dollie()->get_container_url( $container_id, true );

			if ( empty( $secret ) || empty( $container_url ) ) {
				return $backups;
			}

			$backups_response = Api::process_response(
				Api::post(
					Api::ROUTE_BACKUP_GET,
					[
						'container_url'    => $container_url,
						'container_secret' => $secret,
					]
				)
			);

			if ( ! $backups_response || ! is_array( $backups_response ) ) {

				// Grab the last known backup points if connection fails.
				$cached_backups = get_post_meta( $container_id, 'wpd_installation_backups_listing', true );

				if ( $cached_backups ) {
					$backups = $cached_backups;
					update_post_meta( $container_id, 'wpd_installation_backups_outdated', 'yes' );
				}
			} else {

				$backups = $backups_response;
				update_post_meta( $container_id, 'wpd_installation_backups_listing', $backups );
				update_post_meta( $container_id, 'wpd_installation_backups_outdated', 'no' );
				set_transient( $backups_transient_name, $backups, 60 );
			}
		}

		$total_backups = array_filter(
			$backups,
			static function ( $value ) {
				if ( is_array( $value ) ) {
					return false;
				}

				return ! ( strpos( $value, 'restore' ) !== false );
			}
		);

		update_post_meta( $container_id, 'wpd_installation_backups_available', count( $total_backups ) );

		return $backups;
	}

	/**
	 * Get site total available backups
	 *
	 * @param null $container_id
	 *
	 * @return mixed
	 */
	public function count( $container_id = null ) {
		$container = dollie()->get_current_object( $container_id );
		$backups   = $this->get( $container->id );

		if ( false !== $backups ) {
			return count(
				array_filter(
					$backups,
					static function ( $value ) {
						if ( is_array( $value ) ) {
							return false;
						}

						return ! ( false !== strpos( $value, 'restore' ) );
					}
				)
			);
		}

		return false;
	}

	/**
	 * @return array
	 */
	public function get_site_restores() {
		$backups  = $this->get();
		$restores = [];

		foreach ( $backups as $backup ) {
			$info = explode( '|', $backup );

			if ( 'restore' !== $info[1] ) {
				continue;
			}

			$backup_date = explode( '_', $info[0] );

			// Date of backup.
			$date        = strtotime( $backup_date[0] );
			$raw_time    = str_replace( '-', ':', $backup_date[1] );
			$pretty_time = date( 'g:i a', strtotime( $raw_time ) );
			$time        = ' at ' . $pretty_time . '';

			$restores[] = date( 'd F y', $date ) . $time;
		}

		return $restores;
	}

	/**
	 * Create a backup
	 *
	 * @param null $container_id
	 * @param bool $with_log
	 *
	 * @return bool
	 */
	public function make( $container_id = null, $with_log = true ) {
		$container = dollie()->get_current_object( $container_id );

		if ( 0 === $container->id ) {
			return false;
		}

		$container_uri = dollie()->get_wp_site_data( 'uri', $container->id );

		Api::process_response( Api::post( Api::ROUTE_BACKUP_CREATE, [ 'container_uri' => $container_uri ] ) );

		if ( $with_log ) {
			Log::add_front( Log::WP_SITE_BACKUP_STARTED, $container, $container->slug );
		}

		return true;
	}
}
