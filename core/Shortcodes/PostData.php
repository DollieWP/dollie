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
			if (current_user_can('manage_options')) {
				$admin_edit_link = '
					<div class="dol-font-bold dol-rounded-md dol-w-full dol-bg-primary-500 dol-p-2 dol-text-white dol-bottom-0 dol-left-0 dol-z-50 dol-text-center">
						<a class="dol-text-white hover:dol-text-white dol-text-sm" href="' . get_edit_post_link($post->ID) . '">
							Dollie Hub Admin Notice - You can customize the Site Dashboard layout here <span class="dol-icon dol-ml-1"><i class="fas fa-long-arrow-right"></i></span>
						</a>
					</div>';
			} else {
				$admin_edit_link = '';
			}
				$data = $admin_edit_link . apply_filters( 'the_content', $post->post_content );
			}

		return $data;

	}

}
