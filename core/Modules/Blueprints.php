<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;

/**
 * Class Blueprints
 *
 * @package Dollie\Core\Modules
 */
class Blueprints extends Singleton {

	const COOKIE_NAME      = 'dollie_blueprint_id';
	const COOKIE_GET_PARAM = 'blueprint_id';

	/**
	 * Backups constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'wp_head', [ $this, 'get_by_site' ], 11 );
		add_action( 'init', [ $this, 'set_cookie' ], - 99999 );
	}

	public static function show_default_blueprint() {
		return get_field( 'wpd_show_default_blueprint', 'options' );
	}

	/**
	 * Get all available blueprints
	 *
	 * @param string $value_format image|title
	 *
	 * @return array
	 */
	public function get( $value_format = 'image' ) {
		$data = [];

		$sites = get_posts(
			[
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
					],
				],
			]
		);

		if ( empty( $sites ) ) {
			return $data;
		}

		foreach ( $sites as $site ) {
			$private = get_field( 'wpd_private_blueprint', $site->ID );

			if ( 'yes' === $private ) {
				continue;
			}

			if ( 'image' === $value_format ) {
				$image = '';

				if ( get_field( 'wpd_blueprint_image', $site->ID ) === 'custom' ) {
					$image = get_field( 'wpd_blueprint_custom_image', $site->ID );
					if ( is_array( $image ) ) {
						$image = $image['url'];
					}
				} else {
					$image = get_post_meta( $site->ID, 'wpd_site_screenshot', true );
				}
				$value = '<img data-toggle="tooltip" data-placement="bottom" ' .
						 'data-tooltip="' . esc_attr( get_post_meta( $site->ID, 'wpd_installation_blueprint_description', true ) ) . '" ' .
						 'class="fw-blueprint-screenshot acf__tooltip" src=' . $image . '>' .
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
	public function get_by_site( $site_id = null ) {
		if ( isset( $_GET['section'] ) && 'blueprint' === $_GET['section'] && is_singular( 'container' ) ) {

			$site = dollie()->get_current_object( $site_id );

			$secret = get_post_meta( $site->id, 'wpd_container_secret', true );

			$request_get_blueprint = Api::post(
				Api::ROUTE_BLUEPRINT_GET,
				[
					'container_url'    => dollie()->get_container_url( $site_id, true ),
					'container_secret' => $secret,
				]
			);

			$blueprints_response = Api::process_response( $request_get_blueprint, null );

			if ( false === $blueprints_response || 500 === $blueprints_response['status'] ) {
				return [];
			}

			$blueprints = dollie()->maybe_decode_json( $blueprints_response['body'] );

			if ( empty( $blueprints ) ) {
				return [];
			}

			$total_blueprints = array_filter(
				$blueprints,
				static function ( $value ) {
					return ! ( strpos( $value, 'restore' ) !== false );
				}
			);

			set_transient( 'dollie_' . $site->slug . '_total_blueprints', count( $total_blueprints ), MINUTE_IN_SECONDS * 1 );
			update_post_meta( $site->id, 'wpd_installation_blueprints_available', count( $total_blueprints ) );

			return $blueprints;
		}

		return [];
	}

	/**
	 * Set blueprint cookie
	 */
	public function set_cookie() {
		if ( isset( $_GET[ self::COOKIE_GET_PARAM ] ) && (int) $_GET[ self::COOKIE_GET_PARAM ] > 0 ) {
			$cookie_id = sanitize_text_field( $_GET[ self::COOKIE_GET_PARAM ] );
		}

		// No Cookies set? Check is parameter are valid
		if ( isset( $cookie_id ) ) {
			setcookie( self::COOKIE_NAME, $cookie_id, time() + ( 86400 * 30 ), '/' );
		}
	}

	/**
	 * Get available blueprints
	 *
	 * @return array
	 */
	public function get_available() {
		$blueprints           = $this->get_by_site();
		$formatted_blueprints = [];

		foreach ( $blueprints as $blueprint ) {
			$info = explode( '|', $blueprint );

			if ( 'restore' === $info[1] ) {
				continue;
			}

			if ( strpos( $info[1], 'MB' ) !== false ) {
				$get_mb_size = str_replace( 'MB', '', $info[1] );
				$size        = $get_mb_size . ' MB';
			} else {
				$size = $info[1];
			}

			// Time is first part but needs to be split
			$backup_date = explode( '_', $info[0] );

			// Date of backup
			$date        = strtotime( $backup_date[0] );
			$raw_time    = str_replace( '-', ':', $backup_date[1] );
			$pretty_time = date( 'g:i a', strtotime( $raw_time ) );

			$formatted_blueprints[] = [
				'size' => $size,
				'date' => date( 'd F y', $date ),
				'time' => $pretty_time,
			];
		}

		return $formatted_blueprints;
	}

}
