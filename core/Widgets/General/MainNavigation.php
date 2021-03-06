<?php

namespace Dollie\Core\Widgets\General;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Utils\Tpl;

/**
 * Class MainNavigation
 *
 * @package Dollie\Core\Widgets\General
 */
class MainNavigation extends \Elementor\Widget_Base {

	public function get_name() {
		return 'dollie-main-navigation';
	}

	public function get_title() {
		return esc_html__( 'Main Navigation', 'dollie' );
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

		Tpl::load( 'widgets/general/main-navigation', $data, true );
	}

}
