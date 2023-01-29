<?php

namespace Dollie\Core\Widgets\Site;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class SiteContent
 *
 * @package Dollie\Core\Widgets\General
 */
class SiteContent extends \Elementor\Widget_Base {

	public function __construct( $data = [], $args = null ) {
		parent::__construct( $data, $args );

		wp_register_script( 'dollie-custom-launch', DOLLIE_ASSETS_URL . 'js/custom-launch.js', [ 'jquery' ], DOLLIE_VERSION, true );
	}

	public function get_name() {
		return 'dollie-site-content';
	}

	public function get_title() {
		return esc_html__( 'Site Content', 'dollie' );
	}

	public function get_icon() {
		return 'eicon-nav-menu';
	}

	public function get_categories() {
		return [ 'dollie-category' ];
	}

	protected function register_controls() {

	}

	public function render() {
		echo \Dollie\Core\Shortcodes\SiteContent::instance()->shortcode();
	}

}
