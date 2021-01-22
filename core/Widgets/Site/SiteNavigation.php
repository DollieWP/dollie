<?php

namespace Dollie\Core\Widgets\Site;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Utils\Tpl;
use Elementor\Plugin;

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

		$current_id = dollie()->get_current_site_id();

		$data = [
			'settings'   => $this->get_settings_for_display(),
			'current_id' => $current_id
		];

		Tpl::load( 'widgets/site/site-navigation', $data, true );

	}

}
