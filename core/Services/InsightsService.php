<?php

namespace Dollie\Core\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Factories\BaseContainer;
use Dollie\Core\Singleton;

class InsightsService extends Singleton {
	/**
	 * Get site posts
	 *
	 * @return array|mixed
	 */
	public function get_posts() {
		$feed_url = get_option( 'options_wpd_feed_url' );
		$response = get_transient( 'dollie_site_news_' . $feed_url );

		if ( empty( $response ) ) {
			$response = wp_remote_get( $feed_url . '/wp-json/wp/v2/posts/?filter[orderby]=date&per_page=6&_embed' );

			if ( is_wp_error( $response ) ) {
				return [];
			}

			set_transient( 'dollie_site_news_' . $feed_url, $response, 3600 );
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
				return array();
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
			$response = wp_remote_get( 'https://cloud.getdollie.com/wp-json/wp/v2/kb/?filter[orderby]=date&per_page=12&_embed' );

			if ( is_wp_error( $response ) ) {
				return array();
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

		$query = new \WP_Query(
			[
				'post_type'   => [ 'container' ],
				'post_status' => [ 'published' ],
				'author'      => $user_id,
			]
		);

		$posts        = $query->get_posts();
		$total_amount = 0;

		foreach ( $posts as $post ) {
			if ( $amount = get_post_meta( $post->ID, 'wpd_installation_size_bytes', true ) ) {
				$total_amount += (int) $amount;
			}
		}

		update_user_meta( $user_id, 'wpd_installation_size_bytes', $total_amount );

		wp_reset_postdata();

		return $total_amount;
	}

	/**
	 * Get latest container posts
	 *
	 * @param BaseContainer $container
	 *
	 * @return array|mixed
	 */
	public function get_latest_container_posts( BaseContainer $container ) {
		$response = get_transient( 'dollie_recent_posts_' . $container->get_id() );

		if ( empty( $response ) ) {
			$response = wp_remote_get( $container->get_url( true ) . '/wp-json/wp/v2/posts/?filter[orderby]=date&per_page=6&_embed' );

			if ( is_wp_error( $response ) ) {
				return [];
			}

			set_transient( 'dollie_recent_posts_' . $container->get_id(), $response, 3600 );
		}

		return json_decode( wp_remote_retrieve_body( $response ) );
	}

}
