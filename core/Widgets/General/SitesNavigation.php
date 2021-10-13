<?php

namespace Dollie\Core\Widgets\General;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class SitesNavigation
 *
 * @package Dollie\Core\Widgets\General
 */
class SitesNavigation extends \Elementor\Widget_Base {

	public function get_name() {
		return 'dollie-sites-navigation';
	}

	public function get_title() {
		return esc_html__( 'Sites Navigation', 'dollie' );
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
		if ( ! is_user_logged_in() ) {
			return false;
		}

		$data = [
			'settings' => $this->get_settings_for_display(),
		];

		dollie()->load_template( 'widgets/general/sites-navigation', $data, true );
	}

}
