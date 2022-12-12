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
final class SiteContent extends Singleton implements Base {
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
		add_shortcode( 'dollie-site-content', [ $this, 'shortcode' ] );
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
			return '';
		}

		$data = [
			'current_id' => dollie()->get_current_post_id(),
		];

		if ( get_post_type() !== 'container' && ! dollie()->is_elementor_editor() ) {
			return esc_html__( 'This widget will only show content when you visit a Single Dollie Site.', 'dollie' );
		}

		return dollie()->load_template( 'widgets/site/site-content', $data );

	}

}
