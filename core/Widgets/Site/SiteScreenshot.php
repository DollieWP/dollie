<?php

namespace Dollie\Core\Widgets\Site;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class SiteNavigation
 *
 * @package Dollie\Core\Widgets\Site
 */
class SiteScreenshot extends \Elementor\Widget_Base {

	public function get_name() {
		return 'dollie-site-screenshot';
	}

	public function get_title() {
		return esc_html__( 'Site Screenshot', 'dollie' );
	}

	public function get_icon() {
		return 'eicon-bullet-list';
	}

	public function get_categories() {
		return [ 'dollie-category' ];
	}

	protected function render() {
		echo \Dollie\Core\Shortcodes\SiteScreenshot::instance()->shortcode( $this->get_settings_for_display() );
	}

}
