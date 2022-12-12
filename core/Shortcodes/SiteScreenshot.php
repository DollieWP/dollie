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
final class SiteScreenshot extends Singleton implements Base {
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
		add_shortcode( 'dollie-site-screenshot', [ $this, 'shortcode' ] );
	}

	/**
	 * Shortcode logic
	 *
	 * @param $atts
	 *
	 * @return bool|false|mixed|string
	 */
	public function shortcode( $atts ) {

		$settings = shortcode_atts(
			[],
			$atts,
		);

		$data = [
			'settings'  => $settings,
			'current_id' => get_the_ID(),
		];

		if ( dollie()->is_elementor_editor() ) {
			$my_sites = get_posts(
				[
					'post_type'      => 'container',
					'author'         => get_current_user_id(),
					'posts_per_page' => 1,
				]
			);

			if ( ! empty( $my_sites ) ) {
				$data['current_id'] = $my_sites[0]->ID;
			}
		}

		if ( get_post_type() !== 'container' && ! dollie()->is_elementor_editor() ) {
			return esc_html__( 'This widget will only show content when you visit a Single Dollie Site.', 'dollie' );
		}

		return dollie()->load_template( 'widgets/site/site-screenshot', $data );

	}

}
