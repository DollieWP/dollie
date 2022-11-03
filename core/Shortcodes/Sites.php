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
		if ( isset( $_GET['dollie_db_update'] ) ) {
			return false;
		}

		$a = shortcode_atts(
			[
				'amount' => '15',
			],
			$atts
		);

		$args = [
			'posts_per_page' => $a['amount'],
			'paged'          => get_query_var( 'paged', 1 ),
			'post_type'      => 'container',
			'post_status'    => 'publish',
		];

		if ( isset( $_GET['search'] ) && $_GET['search'] ) {
			$args['meta_query'] = [
				'relation' => 'OR',
				[
					'key'     => 'dollie_container_details',
					'value'   => sanitize_text_field( $_GET['search'] ),
					'compare' => 'LIKE',
				],
			];
		}

		if ( isset( $_GET['blueprints'] ) ) {
			$args['meta_query'] = [
				'relation' => 'OR',
				[
					'key'     => 'dollie_container_type',
					'value'   => '1',
					'compare' => '=',
				],
			];
		}

		if ( isset( $_GET['vip'] ) ) {
			$args['meta_query'] = [
				'relation' => 'OR',
				[
					'key'     => 'dollie_vip_site',
					'value'   => '1',
					'compare' => '=',
				],
			];
		}

		if ( isset( $_GET['customer'] ) && $_GET['customer'] ) {
			$args['author'] = (int) sanitize_text_field( $_GET['customer'] );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			$args['author'] = get_current_user_id();
		}

		$sites = new WP_Query( $args );

		$data = [
			'sites'       => $sites->get_posts(),
			'sites_pages' => $sites->max_num_pages,
			'settings'    => $a,
			'query_data'  => [
				'permalink'    => get_the_permalink(),
				'current_page' => get_query_var( 'paged', 1 ),
			],
		];

		dollie()->load_template( 'loop/sites', $data, true );
	}

}
