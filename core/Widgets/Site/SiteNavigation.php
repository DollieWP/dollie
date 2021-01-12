<?php

namespace Dollie\Core\Widgets\Site;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Utils\Tpl;

/**
 * Class SiteNavigation
 *
 * @package Dollie\Core\Widgets\Site
 */
class SiteNavigation extends \Elementor\Widget_Base {

	public function get_name() {
		return 'dollie-site-navigation';
	}

	public function get_title() {
		return esc_html__( 'Site Navigation', 'dollie' );
	}

	public function get_icon() {
		return 'eicon-bullet-list';
	}

	public function get_categories() {
		return [ 'dollie-category' ];
	}

	protected function _register_controls() {

	}

	protected function render() {
		$data = [
			'settings' => $this->get_settings_for_display(),
		];

		if ( get_post_type() !== 'container' ) {
			esc_html_e( 'This widget can only be used on the "Site" page.', 'dollie' );
		} else {
			Tpl::load( DOLLIE_WIDGETS_PATH . 'Site/templates/site-navigation', $data, true );
		}
	}

}
