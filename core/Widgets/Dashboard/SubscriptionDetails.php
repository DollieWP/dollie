<?php

namespace Dollie\Core\Widgets\Dashboard;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Utils\Tpl;

use Elementor\Controls_Manager;

/**
 * Class SubscriptionDetails
 *
 * @package Dollie\Core\Widgets\Dashboard
 */
class SubscriptionDetails extends \Elementor\Widget_Base {

	public function get_name() {
		return 'dollie-dashboard-subscription-details';
	}

	public function get_title() {
		return esc_html__( 'Subscription Details', 'dollie' );
	}

	public function get_icon() {
		return 'eicon-product-price';
	}

	public function get_categories() {
		return [ 'dollie-category' ];
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Content', 'dollie' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$data = [
			'settings' => $this->get_settings_for_display(),
		];

		Tpl::load( 'widgets/dashboard/subscription-details', $data, true );
	}

}
