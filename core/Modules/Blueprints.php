<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;
use Dollie\Core\Log;
use WP_Query;

/**
 * Class Blueprints
 * @package Dollie\Core\Modules
 */
class Blueprints extends Singleton {

	/**
	 * Backups constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'wp_head', [ $this, 'get_available_blueprints' ], 11 );
		add_action( 'template_redirect', [ $this, 'set_blueprint_cookie' ], - 99999 );
	}

	/**
     * Get available blueprints
     *
	 * @return array
	 */
	public function get_available_blueprints() {
		if ( isset( $_GET['page'] ) && $_GET['page'] === 'blueprint' && is_singular( 'container' ) ) {

			$currentQuery = dollie()->get_current_object();

			$secret = get_post_meta( $currentQuery->id, 'wpd_container_secret', true );

			$requestGetBlueprint = Api::post( Api::ROUTE_BLUEPRINT_GET, [
				'container_url'    => dollie()->get_container_url(),
				'container_secret' => $secret
			] );

			$responseGetBlueprint = json_decode( wp_remote_retrieve_body( $requestGetBlueprint ), true );

			if ( $responseGetBlueprint['status'] === 500 ) {
				return [];
			}

			$blueprints = json_decode( $responseGetBlueprint['body'], true );

			if ( empty( $blueprints ) ) {
				return [];
			}

			$total_blueprints = array_filter( $blueprints, static function ( $value ) {
				return ! ( strpos( $value, 'restore' ) !== false );
			} );

			set_transient( 'dollie_' . $currentQuery->slug . '_total_blueprints', count( $total_blueprints ), MINUTE_IN_SECONDS * 1 );
			update_post_meta( $currentQuery->id, 'wpd_installation_blueprints_available', count( $total_blueprints ) );

			return $blueprints;
		}

		return [];
	}


	public function set_blueprint_cookie() {
		if ( isset( $_GET['blueprint_id'] ) ) {
			$cookie_id = $_GET['blueprint_id'];
		}

		$currentQuery   = dollie()->get_current_object();
		$setup_complete = get_post_meta( $currentQuery->id, 'wpd_container_based_on_blueprint', true );

		// No Cookies set? Check is parameter are valid
		if ( isset( $cookie_id ) ) {
			setcookie( 'dollie_blueprint_id', $cookie_id, time() + ( 86400 * 30 ), '/' );
		}

		if ( $setup_complete === 'yes' && is_singular( 'container' ) ) {
			setcookie( 'dollie_blueprint_id', '', time() - 3600, '/' );
		}
	}

	public function list_available_blueprints() {
		$blueprints = $this->get_available_blueprints();

		if ( empty( $blueprints ) ) {
			echo 'No Blueprints Created yet';
		} else {
			?>
            <ul class="list-unstyled">
			<?php foreach ( $blueprints as $blueprint ) : ?>
				<?php
				// Split info via pipe
				$info = explode( '|', $blueprint );
				if ( $info[1] === 'restore' ) {
					continue;
				}

				if ( strpos( $info[1], 'MB' ) !== false ) {
					$get_mb_size = str_replace( 'MB', '', $info[1] );
					$real_size   = $get_mb_size . ' MB';
				} else {
					$real_size = $info[1];
				}

				$size = '<br><span class="pull-right mt-2"><i class="fal fa-hdd"></i> Size ' . $real_size . '</span>';

				// Time is first part but needs to be split
				$backup_date = explode( '_', $info[0] );

				// Date of backup
				$date        = strtotime( $backup_date[0] );
				$raw_time    = str_replace( '-', ':', $backup_date[1] );
				$pretty_time = date( 'g:i a', strtotime( $raw_time ) );

				// Time of backup
				$time = ' at ' . $pretty_time . '';
				?>
                <li>
                    <i class='fal fa-calendar'></i> Created on <?php echo date( 'd F y', $date ) . $time . $size; ?>
                </li>
			<?php endforeach; ?>
			<?php
			echo '</ul>';
		}
	}

}
