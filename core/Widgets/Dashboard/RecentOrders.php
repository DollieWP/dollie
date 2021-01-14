<?php

namespace Dollie\Core\Widgets\Dashboard;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Utils\Tpl;

/**
 * Class RecentOrders
 *
 * @package Dollie\Core\Widgets\Dashboard
 */
class RecentOrders extends \Elementor\Widget_Base {

	public function get_name() {
		return 'dollie-dashboard-recent-orders';
	}

	public function get_title() {
		return esc_html__( 'Recent Orders', 'dollie' );
	}

	public function get_icon() {
		return 'eicon-price-list';
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

		Tpl::load( 'widgets/dashboard/recent-orders', $data, true );
	}

}
