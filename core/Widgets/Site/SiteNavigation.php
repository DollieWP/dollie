<?php

namespace Dollie\Core\Widgets\Site;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

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

	protected function register_controls() {

		$this->start_controls_section(
			'Design',
			[
				'label' => __( 'Design', 'dollie' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'layout',
			[
				'label'   => __( 'Layout', 'dollie' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'vertical',
				'options' => [
					'vertical'   => __( 'Vertical', 'dollie' ),
					'horizontal' => __( 'Horizontal', 'dollie' ),
				],
			]
		);

		$this->add_control(
			'colors',
			[
				'label'   => __( 'Color Scheme', 'dollie' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'light',
				'options' => [
					'light' => __( 'Light', 'dollie' ),
					'dark'  => __( 'Dark', 'dollie' ),
				],
			]
		);

		$this->add_control(
			'nav_color',
			[
				'label'     => __( 'Navigation Link Color', 'plugin-domain' ),
				'type'      => Controls_Manager::COLOR,
				'scheme'    => [
					'type'  => \Elementor\Core\Schemes\Color::get_type(),
					'value' => \Elementor\Core\Schemes\Color::COLOR_1,
				],
				'selectors' => [
					'{{WRAPPER}} li a.dol-nav-btn' => 'color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {

		echo \Dollie\Core\Shortcodes\SiteNavigation::instance()->shortcode( $this->get_settings_for_display() );
	}

}
