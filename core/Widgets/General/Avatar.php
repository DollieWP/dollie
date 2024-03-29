<?php

namespace Dollie\Core\Widgets\General;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

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

	protected function register_controls() {

	}

	protected function render() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$data = [
			'settings' => $this->get_settings_for_display(),
		];

		dollie()->load_template( 'widgets/general/avatar', $data, true );
	}

}
