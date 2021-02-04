<?php

namespace Dollie\Core\Widgets\General;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Utils\Tpl;

/**
 * Class SitesNavigation
 *
 * @package Dollie\Core\Widgets\General
 */
class SitesNavigation extends \Elementor\Widget_Base {

	public function get_name() {
		return 'dollie-sites-navigation';
	}

	public function __construct($data = [], $args = null)
	{
		parent::__construct($data, $args);

		wp_register_script(
			'dollie-layout-alpine',
			'https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js',
			[],
			DOLLIE_VERSION,
			true
		);
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

		Tpl::load( 'widgets/general/sites-navigation', $data, true );
	}

}
