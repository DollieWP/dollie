<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use WP_Query;

/**
 * Class SiteInsights
 * @package Dollie\Core\Modules
 */
class SiteInsights extends Singleton {

	public function get_site_posts() {
		$slug = get_option( 'options_wpd_feed_url' );
		$data = get_transient( 'dollie_site_news_' . $slug );
		if ( empty( $data ) ) {
			$response = wp_remote_get( $slug . '/wp-json/wp/v2/posts/?filter[orderby]=date&per_page=6&_embed' );

			if ( is_wp_error( $response ) ) {
				return [];
			}

			$data = set_transient( 'dollie_site_news_' . $slug, $response, 3600 );

			if ( empty( $data ) ) {
				return [];
			}
		}

		return json_decode( wp_remote_retrieve_body( $data ) );
	}

	public function get_total_container_size() {
		$userID = get_current_user_id();

		$query = new WP_Query( [
			'post_type'   => [ 'container' ],
			'post_status' => [ 'published' ],
			'author'      => $userID,
		] );

		$total_amount = 0;

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$total_amount += get_post_meta( get_the_ID(), 'wpd_installation_size_bytes', true );
			}
		}

		update_user_meta( $userID, 'wpd_installation_size_bytes', $total_amount );
		update_user_meta( $userID, 'wpd_wp_core_updates', $total_amount );

		wp_reset_postdata();

		return $total_amount;
	}

	public function get_latest_container_posts() {
		global $wp_query;
		$post_id   = $wp_query->get_queried_object_id();
		$post_slug = get_queried_object()->post_name;
		$data      = get_transient( 'dollie_recent_posts_' . $post_slug );

		if ( empty( $data ) ) {
			$response = wp_remote_get( dollie()->helpers()->get_container_url( $post_id ) . '/wp-json/wp/v2/posts/?filter[orderby]=date&per_page=6&_embed' );

			if ( is_wp_error( $response ) ) {
				return [];
			}

			$data = set_transient( 'dollie_recent_posts_' . $post_slug, $response, 3600 );

			if ( empty( $data ) ) {
				return [];
			}
		}

		return json_decode( wp_remote_retrieve_body( $data ) );
	}

}
