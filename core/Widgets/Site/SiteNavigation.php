<?php

namespace Dollie\Core\Widgets\Site;

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

	protected function _register_controls() {

		$this->start_controls_section(
			'Design',
			[
				'label' => __( 'Design', 'dollie' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'layout',
			[
				'label'   => __( 'Layout', 'dollie' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
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
				'type'    => \Elementor\Controls_Manager::SELECT,
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
				'type'      => \Elementor\Controls_Manager::COLOR,
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
		$data = [
			'settings'  => $this->get_settings_for_display(),
			'container' => dollie()->get_container(),
		];

		if ( dollie()->is_elementor_editor() ) {
			$my_sites = get_posts(
				[
					'post_type'      => 'container',
					'author'         => get_current_user_id(),
					'posts_per_page' => 1,
				]
			);

			if ( ! empty( $my_sites ) ) {
				$data['container'] = dollie()->get_container( $my_sites[0]->ID );
			}
		}

		if ( get_post_type() !== 'container' && ! dollie()->is_elementor_editor() ) {
			esc_html_e( 'This widget will only show content when you visit a Single Dollie Site.', 'dollie' );
		} else {
			dollie()->load_template( 'widgets/site/site-navigation', $data, true );
		}
	}

}
