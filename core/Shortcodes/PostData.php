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
final class PostData extends Singleton implements Base {
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
		add_shortcode( 'dollie-post-data', [ $this, 'shortcode' ] );
	}

	/**
	 * Shortcode logic
	 *
	 * @param $atts
	 *
	 * @return bool|false|mixed|string
	 */
	public function shortcode( $atts ) {

		$data = '';
		$atts = shortcode_atts( [
			'id' => '',
		], $atts, 'dollie-post-data' );

		if ( ! $atts['id'] ) {
			return '';
		}

		// get post content for a specific post by post ID
		$post = get_post( $atts['id'] );
		if ( $post ) {
			$data = apply_filters( 'the_content', $post->post_content );
		}

		return $data;

	}

}
