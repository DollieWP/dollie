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

	const COOKIE_NAME = 'dollie_blueprint_id';
	const COOKIE_GET_PARAM = 'blueprint_id';

	/**
	 * Backups constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'wp_head', [ $this, 'get_site_available_blueprints' ], 11 );
		add_action( 'init', [ $this, 'set_blueprint_cookie' ], - 99999 );
	}

	/**
	 * Get all available blueprints
	 *
	 * @param string $value_format image|title
	 *
	 * @return array
	 */
	public function get_all_blueprints( $value_format = 'image' ) {

		$data = [];

		/*if ( is_admin && ! current_user_can( 'manage_options' ) ) {
			return $data;
		}*/

		$sites = get_posts( [
			'post_type'      => 'container',
			'posts_per_page' => - 1,
			'meta_query'     => [
				'relation' => 'AND',
				[
					'key'   => 'wpd_blueprint_created',
					'value' => 'yes',
				],
				[
					'key'   => 'wpd_is_blueprint',
					'value' => 'yes',
				],
				[
					'key'     => 'wpd_installation_blueprint_title',
					'compare' => 'EXISTS',
				]
			],
			// 'p'              => isset( $_COOKIE[ self::COOKIE_NAME ] ) ? $_COOKIE[ self::COOKIE_NAME ] : '',
		] );

		if ( empty( $sites ) ) {
			return $data;
		}

		foreach ( $sites as $site ) {

			$private = get_field( 'wpd_private_blueprint', $site->ID );

			if ( $private === 'yes' ) {
				continue;
			}

			if ( 'image' === $value_format ) {

				if ( get_field( 'wpd_blueprint_image', $site->ID ) === 'custom' ) {
					$image = get_field( 'wpd_blueprint_custom_image', $site->ID );
				} elseif ( get_field( 'wpd_blueprint_image', $site->ID ) === 'theme' ) {
					$image = wpthumb( get_post_meta( $site->ID, 'wpd_installation_site_theme_screenshot', true ), 'width=900&crop=0' );
				} else {
					$image = get_post_meta( $site->ID, 'wpd_site_screenshot', true );
				}
				$value = '<img data-toggle="tooltip" data-placement="bottom" ' .
				         'title="' . esc_attr( get_post_meta( $site->ID, 'wpd_installation_blueprint_description', true ) ) . '" ' .
				         'class="fw-blueprint-screenshot" src=' . $image . '>' .
				         esc_html( get_post_meta( $site->ID, 'wpd_installation_blueprint_title', true ) );

			} else {
				$value = get_post_meta( $site->ID, 'wpd_installation_blueprint_title', true );
			}

			$data[ $site->ID ] = $value;

		}

		return $data;
	}


	/**
	 * Get available blueprints
	 *
	 * @param int $site_id
	 *
	 * @return array
	 */
	public function get_site_available_blueprints( $site_id = null ) {
		if ( isset( $_GET['page'] ) && $_GET['page'] === 'blueprint' && is_singular( 'container' ) ) {

			$site = dollie()->get_current_object( $site_id );

			$secret = get_post_meta( $site->id, 'wpd_container_secret', true );

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

			set_transient( 'dollie_' . $site->slug . '_total_blueprints', count( $total_blueprints ), MINUTE_IN_SECONDS * 1 );
			update_post_meta( $site->id, 'wpd_installation_blueprints_available', count( $total_blueprints ) );

			return $blueprints;
		}

		return [];
	}


	public function set_blueprint_cookie() {
		if ( isset( $_GET[ self::COOKIE_GET_PARAM ] ) ) {
			$cookie_id = $_GET[ self::COOKIE_GET_PARAM ];
		}

		$currentQuery   = dollie()->get_current_object();

		// No Cookies set? Check is parameter are valid
		if ( isset( $cookie_id ) ) {
			setcookie( self::COOKIE_NAME, $cookie_id, time() + ( 86400 * 30 ), '/' );
		}
	}

	public function list_available_blueprints() {
		$blueprints = $this->get_site_available_blueprints();

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
					<?php
					printf(
						esc_html__( '%s Created on %s', 'dollie' ),
						'<i class="fal fa-calendar"></i>',
						( date( 'd F y', $date ) . $time . $size )
					); ?>
                </li>
			<?php endforeach; ?>
			<?php
			echo '</ul>';
		}
	}

}
