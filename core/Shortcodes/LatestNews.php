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
final class LatestNews extends Singleton implements Base {
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
		add_shortcode( 'dollie-latest-news', [ $this, 'shortcode' ] );
	}

	/**
	 * Shortcode logic
	 *
	 * @param array $atts
	 *
	 * @return bool|false|mixed|string
	 */
	public function shortcode( $atts = [] ) {

		$a = shortcode_atts(
			[
				'amount' => '15',
			],
			$atts
		);
		$posts = dollie()->insights()->get_posts();

		return dollie()->load_template(
			'loop/news',
			[
				'title' => __( 'Latest News', 'dollie' ),
				'posts' => $posts,
			]
		);

	}

}
