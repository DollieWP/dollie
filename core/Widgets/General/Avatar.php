<?php

namespace Dollie\Core\Widgets\General;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Utils\Tpl;

/**
 * Class Avatar
 *
 * @package Dollie\Core\Widgets\General
 */
class Avatar extends \Elementor\Widget_Base {

	public function get_name() {
		return 'dollie-avatar';
	}

	public function get_title() {
		return esc_html__( 'Avatar', 'dollie' );
	}

	public function get_icon() {
		return 'eicon-user-circle-o';
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

		Tpl::load( DOLLIE_WIDGETS_PATH . 'General/templates/avatar', $data, true );
	}

}
