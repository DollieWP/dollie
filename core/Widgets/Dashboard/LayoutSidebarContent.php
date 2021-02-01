<?php

namespace Dollie\Core\Widgets\Dashboard;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Utils\Tpl;

use Elementor\Controls_Manager;

/**
 * Class LayoutSidebarContent
 *
 * @package Dollie\Core\Widgets\Dashboard
 */
class LayoutSidebarContent extends \Elementor\Widget_Base {

	public function get_name() {
		return 'dollie-layout-sidebar-content';
	}

	public function get_title() {
		return esc_html__( 'Layout - Sidebar / Content', 'dollie' );
	}

	public function get_icon() {
		return 'eicon-welcome';
	}

	public function get_categories() {
		return [ 'dollie-category' ];
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Template', 'dollie' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'content',
			[
				'label'   => __( 'Content Template', 'dollie' ),
				'type'    => Controls_Manager::NUMBER
			]
		);

		$this->add_control(
			'sidebar',
			[
				'label'   => __('Sidebar template', 'dollie'),
				'type'    => Controls_Manager::NUMBER,
			]
		);

		$this->add_control(
			'header',
			[
				'label'   => __('Header template', 'dollie'),
				'type'    => Controls_Manager::NUMBER,
			]
		);


		$this->end_controls_section();
	}

	protected function render() {
		$data = [
			'settings' => $this->get_settings_for_display(),
		];

		Tpl::load( 'widgets/dashboard/sidebar-content-layout', $data, true );
	}

}
