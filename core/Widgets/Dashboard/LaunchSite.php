<?php

namespace Dollie\Core\Widgets\Dashboard;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;

/**
 * Class LaunchSite
 *
 * @package Dollie\Core\Widgets\Dashboard
 */
class LaunchSite extends \Elementor\Widget_Base {

	public function get_name() {
		return 'dollie-dashboard-launch-site';
	}

	public function get_title() {
		return esc_html__( 'Launch Site', 'dollie' );
	}

	public function get_icon() {
		return 'eicon-site-identity';
	}

	public function get_categories() {
		return [ 'dollie-category' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Content', 'dollie' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'title',
			[
				'label'       => __( 'Title', 'dollie' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'LAUNCH YOUR SITE',
				'label_block' => true,
			]
		);

		$this->add_control(
			'subtitle',
			[
				'label'       => __( 'Subtitle', 'dollie' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'Get started and launch your site within minutes.',
				'label_block' => true,
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$data = [
			'settings' => $this->get_settings_for_display(),
		];

		dollie()->load_template( 'widgets/dashboard/launch-site', $data, true );
	}

}
