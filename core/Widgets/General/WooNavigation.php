<?php

namespace Dollie\Core\Widgets\General;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WooNavigation
 *
 * @package Dollie\Core\Widgets\General
 */
class WooNavigation extends \Elementor\Widget_Base {

	public function get_name() {
		return 'dollie-woo-navigation';
	}

	public function get_title() {
		return esc_html__( 'Woo Navigation', 'dollie' );
	}

	public function get_icon() {
		return 'eicon-bullet-list';
	}

	public function get_categories() {
		return [ 'dollie-category' ];
	}

	protected function register_controls() {

	}

	protected function render() {
		$data = [
			'settings' => $this->get_settings_for_display(),
		];

		dollie()->load_template( 'widgets/general/woo-navigation', $data, true );
	}

}
