<?php

namespace Dollie\Core\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use WP_Query;

/**
 * Class Blueprints
 *
 * @package Dollie\Core\Shortcodes
 */
final class Blueprints extends Singleton implements Base {
	/**
	 * Blueprints constructor.
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
		add_shortcode( 'dollie-blueprints', [ $this, 'shortcode' ] );
	}

	/**
	 * Shortcode logic
	 *
	 * @param $atts
	 *
	 * @return false|mixed|string
	 */
	public function shortcode( $atts ) {
		$a = shortcode_atts(
			[
				'amount'               => - 1,
				'columns'              => 3,
				'orderby'              => 'post_date',
				'order'                => 'DESC',
				'category'             => '',
				'id'                   => '',
				'checkout-url'         => '',
				'checkout-integration' => '',
				'launch-button-text'   => '',
				'view-demo-text'       => '',
				'custom-class'         => 'blueprint-item',
			],
			$atts
		);

		$args = [
			'post_type'      => 'container',
			'posts_per_page' => $a['amount'],
			'meta_query'     => [
				'relation' => 'AND',
				[
					'key'   => 'wpd_blueprint_created',
					'value' => 'yes',
				],
				[
					'key'     => 'wpd_installation_blueprint_title',
					'compare' => 'EXISTS',
				],
			],
			'orderby'        => $a['orderby'],
			'order'          => $a['order'],
		];

		if ( ! empty( $a['id'] ) ) {
			$posts            = explode( ',', $a['id'] );
			$args['post__in'] = $posts;

			// Check if only one ID is provided
			if ( count( $posts ) === 1 ) {
				// Load a different template for single ID
				dollie()->load_template(
					'loop/single-blueprint',
					[
						'post'                 => get_post( $posts[0] ),
						'launch_button_text'   => $a['launch-button-text'],
						'view_demo_text'       => $a['view-demo-text'],
						'checkout_url'         => $a['checkout-url'],
						'checkout_integration' => $a['checkout-integration'],
					],
					true
				);

				return ob_get_clean();
			}
		} elseif ( ! empty( $a['category'] ) ) {
			$args['tax_query'] = [
				[
					'taxonomy' => 'container_category',
					'field'    => 'slug',
					'terms'    => $a['category'],
				],
			];
		}

		$query = new WP_Query( $args );
		$posts = $query->get_posts();

		ob_start();

		if ( $query->have_posts() ) {
			$rows = $a['columns'];

			echo '<ul class="dol-grid dol-grid-cols-1 dol-gap-6 sm:dol-grid-cols-2' .
			     ' md:dol-grid-cols-' . $rows . ' lg:dol-grid-cols-' . $rows
			     . ' dol-m-0 dol-p-0">';

			foreach ( $posts as $post ) {
				dollie()->load_template(
					'loop/blueprints',
					[
						'post'                 => $post,
						'launch_button_text'   => $a['launch-button-text'],
						'view_demo_text'       => $a['view-demo-text'],
						'checkout_url'         => $a['checkout-url'],
						'checkout_integration' => $a['checkout-integration'],
					],
					true
				);
			}

			echo '</ul>';
		}

		wp_reset_query();

		return ob_get_clean();
	}

}
