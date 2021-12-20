<?php

namespace Dollie\Core\Widgets\Dashboard;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class LatestNews
 *
 * @package Dollie\Core\Widgets\Dashboard
 */
class LatestNews extends \Elementor\Widget_Base {

	public function get_name() {
		return 'dollie-dashboard-latest-news';
	}

	public function get_title() {
		return esc_html__( 'Latest News', 'dollie' );
	}

	public function get_icon() {
		return 'eicon-posts-grid';
	}

	public function get_categories() {
		return [ 'dollie-category' ];
	}

	protected function _register_controls() {

	}

	protected function render() {
		$data = [
			'posts'    => dollie()->get_site_posts(),
			'settings' => $this->get_settings_for_display(),
		];

		dollie()->load_template( 'widgets/dashboard/latest-news', $data, true );
	}

}
