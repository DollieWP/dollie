<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use WP_Query;

/**
 * Class SiteInsights
 *
 * @package Dollie\Core\Modules
 */
class SiteInsights extends Singleton {

	/**
	 * Get site posts
	 *
	 * @return array|mixed
	 */
	public function get_posts() {
		$slug     = get_option( 'options_wpd_feed_url' );
		$response = get_transient( 'dollie_site_news_' . $slug );

		if ( empty( $response ) ) {
			$response = wp_remote_get( $slug . '/wp-json/wp/v2/posts/?filter[orderby]=date&per_page=6&_embed' );

			if ( is_wp_error( $response ) ) {
				return [];
			}

			set_transient( 'dollie_site_news_' . $slug, $response, 3600 );
		}

		return json_decode( wp_remote_retrieve_body( $response ) );
	}

	/**
	 * Get site posts
	 *
	 * @return array|mixed
	 */
	public function get_dashboard_news() {
		$response = get_transient( 'dollie_dashboard_news' );

		if ( empty( $response ) ) {
			$response = wp_remote_get( 'https://getdollie.com/wp-json/wp/v2/posts/?filter[orderby]=date&per_page=3&_embed' );

			if ( is_wp_error( $response ) ) {
				return [];
			}

			set_transient( 'dollie_dashboard_news', $response, 3600 );
		}

		return json_decode( wp_remote_retrieve_body( $response ) );
	}

	/**
	 * Get site posts
	 *
	 * @return array|mixed
	 */
	public function get_kb_articles() {
		 $response = get_transient( 'dollie_dashboard_articles' );

		if ( empty( $response ) ) {
			$response = wp_remote_get( 'https://partners.getdollie.com/wp-json/wp/v2/kb/?filter[orderby]=date&per_page=12&_embed' );

			if ( is_wp_error( $response ) ) {
				return [];
			}

			set_transient( 'dollie_dashboard_articles', $response, 3600 );
		}

		return json_decode( wp_remote_retrieve_body( $response ) );
	}

	/**
	 * Get total container size
	 *
	 * @return int|mixed
	 */
	public function get_total_container_size() {
		$user_id = get_current_user_id();

		$query = new WP_Query(
			[
				'post_type'   => [ 'container' ],
				'post_status' => [ 'published' ],
				'author'      => $user_id,
			]
		);

		$total_amount = 0;

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				if ( $amount = get_post_meta( get_the_ID(), 'wpd_installation_size_bytes', true ) ) {
					$total_amount += (int) $amount;
				}
			}
		}

		update_user_meta( $user_id, 'wpd_installation_size_bytes', $total_amount );

		wp_reset_postdata();

		return $total_amount;
	}

	/**
	 * Get latest container posts
	 *
	 * @return array|mixed
	 */
	public function get_latest_container_posts() {
		$container = dollie()->get_container();

		if ( is_wp_error( $container ) ) {
			return [];
		}

		$response = get_transient( 'dollie_recent_posts_' . $container->slug );

		if ( empty( $response ) ) {
			$response = wp_remote_get( $container->get_url() . '/wp-json/wp/v2/posts/?filter[orderby]=date&per_page=6&_embed' );

			if ( is_wp_error( $response ) ) {
				return [];
			}

			set_transient( 'dollie_recent_posts_' . $container->slug, $response, 3600 );
		}

		return json_decode( wp_remote_retrieve_body( $response ) );
	}

}
