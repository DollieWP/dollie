<?php

namespace Dollie\Core\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use WP_Query;

/**
 * Class Sites
 *
 * @package Dollie\Core\Shortcodes
 */
final class Sites extends Singleton implements Base {
	/**
	 * Sites constructor.
	 */
	public function __construct() {
		parent::__construct();
		add_action( 'init', [ $this, 'register' ] );
	}

	/**
	 * Add shortcode
	 *
	 * @return mixed|void
	 */
	public function register() {
		add_shortcode( 'dollie-sites', [ $this, 'shortcode' ] );
	}

	/**
	 * Shortcode logic
	 *
	 * @param $atts
	 *
	 * @return bool|false|mixed|string
	 */
	public function shortcode( $atts ) {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		if ( isset( $_GET['dollie_db_update'] ) ) {
			return false;
		}
		$settings = shortcode_atts(
			[
				'posts_per_page' => 15
			],
			$atts,
		);

		global $wp_query;

		$current_page = isset( $_GET['dpg'] ) && (int) $_GET['dpg'] ? (int) sanitize_text_field( $_GET['dpg'] ) : 1;

		if ( isset( $_GET['elementor_library'] ) && isset( $_GET['load-page'] ) && $_GET['load-page'] ) {
			$current_page = (int) sanitize_text_field( $_GET['load-page'] );
		}

		$per_page = $settings['posts_per_page'];

		if ( isset( $_GET['per_page'] ) && (int) sanitize_text_field( $_GET['per_page'] ) > 0 ) {
			$per_page = (int) sanitize_text_field( $_GET['per_page'] );
		}

		$args = [
			'posts_per_page' => $per_page,
			'paged'          => $current_page,
			'post_type'      => 'container',
			'post_status'    => 'publish',
		];

		$meta_query = [];

		if ( isset( $_GET['search'] ) && $_GET['search'] ) {
			$meta_query['search'][] = [
				'key'     => 'dollie_container_details',
				'value'   => sanitize_text_field( $_GET['search'] ),
				'compare' => 'LIKE',
			];
		}

		if ( isset( $_GET['status'] ) && $_GET['status'] ) {
			$status = sanitize_text_field( $_GET['status'] );

			if ( in_array(
				$status,
				[
					'Running',
					'Stopped',
					'Deploying',
					'Undeployed',
					'Deploy Failure',
				],
				true
			) ) {
				$meta_query['search'][] = [
					'key'     => 'dollie_container_details',
					'value'   => $status,
					'compare' => 'LIKE',
				];
			}
		}

		if ( isset( $meta_query['search'] ) && count( $meta_query['search'] ) > 1 ) {
			$meta_query['search']['relation'] = 'AND';
		}

		if ( isset( $_GET['blueprints'] ) && $_GET['blueprints'] ) {
			$meta_query['container_type'][] = [
				'key'     => 'dollie_container_type',
				'value'   => '1',
				'compare' => '=',
			];
		} else {
			$meta_query['container_type'][] = [
				'key'     => 'dollie_container_type',
				'value'   => '0',
				'compare' => '=',
			];
		}

		if ( ! isset( $_GET['blueprints'] ) && isset( $_GET['site_type'] ) ) {
			$site_type = sanitize_text_field( $_GET['site_type'] );

			if ( in_array(
				$site_type,
				[
					'vip',
					'normal',
				],
				true
			) ) {
				$meta_query['container_type'][] = [
					'key'     => 'dollie_vip_site',
					'value'   => '1',
					'compare' => 'vip' === $site_type ? '=' : '!=',
				];

				$meta_query['container_type']['relation'] = 'AND';
			}
		}

		if ( isset( $meta_query['search'] ) ) {
			$args['meta_query'][]           = $meta_query['search'];
			$args['meta_query'][]           = $meta_query['container_type'];
			$args['meta_query']['relation'] = 'AND';
		} else {
			$args['meta_query'] = $meta_query['container_type'];
		}

		if ( isset( $_GET['customer'] ) && $_GET['customer'] ) {
			$args['author'] = (int) sanitize_text_field( $_GET['customer'] );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			$args['author'] = get_current_user_id();
		}

		$sites = new WP_Query( $args );

		$pagenum_link = html_entity_decode( get_pagenum_link() );
		$url_parts    = explode( '?', $pagenum_link );

		$data = [
			'sites'       => $sites->get_posts(),
			'sites_pages' => $sites->max_num_pages,
			'settings'    => $settings,
			'query_data'  => [
				'permalink'    => $url_parts[0],
				'current_page' => $current_page,
			],
		];

		return dollie()->load_template( 'loop/sites', $data );
	}

}
