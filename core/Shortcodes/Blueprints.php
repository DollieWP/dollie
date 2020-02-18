<?php

namespace Dollie\Core\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use WP_Query;

/**
 * Class Blueprints
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
				'amount'  => '999999',
				'columns' => 1,
			],
			$atts
		);

		$query = new WP_Query( [
			'post_type'     => 'container',
			'meta_query'    => [
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
			'post_per_page' => $a['amount'],
		] );

		ob_start();

		if ( $query->have_posts() ) {
			echo '<div class="row fw-blueprint-listing">';

			while ( $query->have_posts() ) {
				$query->the_post();

				include( locate_template( '/loop-templates/blueprints.php' ) );
			}

			echo '</div>';
		}

		wp_reset_query();

		return ob_get_clean();
	}

}
