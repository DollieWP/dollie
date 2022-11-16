<?php

namespace Dollie\Core\Widgets\Dashboard;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Box_Shadow;

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
			'icon_enabled',
			[
				'label'     => __( 'Show Icon', 'dollie' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_off' => __( 'No', 'dollie' ),
				'label_on'  => __( 'Yes', 'dollie' ),
				'default'   => 'yes',
			]
		);

		$this->add_control(
			'icon',
			[
				'label'       => __( 'Icon', 'dollie' ),
				'type'        => Controls_Manager::ICONS,
				'label_block' => true,
				'default'     => [
					'value'   => 'fas fa-rocket',
					'library' => 'fa-solid',
				],
				'condition'   => [
					'icon_enabled' => 'yes',
				],
			]
		);

		$this->add_control(
			'title',
			[
				'label'       => __( 'Title', 'dollie' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'LAUNCH YOUR SITE', 'dollie' ),
				'label_block' => true,
			]
		);

		$this->add_control(
			'subtitle',
			[
				'label'       => __( 'Subtitle', 'dollie' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Get started and launch your site within minutes.', 'dollie' ),
				'label_block' => true,
			]
		);

		$this->add_control(
			'button',
			[
				'label'       => __( 'Button', 'dollie' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Launch', 'dollie' ),
				'label_block' => true,
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'container_style_section',
			[
				'label' => __( 'Container', 'dollie' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'container_bg_color',
			[
				'label'     => __( 'Background', 'dollie' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .dol-widget-launch-site' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'container_border',
				'selector' => '{{WRAPPER}} .dol-widget-launch-site',
			]
		);

		$this->add_control(
			'container_border_radius',
			[
				'label'      => __( 'Border Radius', 'dollie' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .dol-widget-launch-site' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'container_padding',
			[
				'label'      => __( 'Padding', 'dollie' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .dol-widget-launch-site' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'icon_style_section',
			[
				'label' => __( 'Icon', 'dollie' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'icon_color',
			[
				'label'     => __( 'Color', 'dollie' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .dol-widget-launch-site .dol-widget-icon i:before' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'icon_size',
			[
				'label'      => __( 'Size', 'dollie' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'selectors'  => [
					'{{WRAPPER}} .dol-widget-launch-site .dol-widget-icon i' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'icon_spacing',
			[
				'label'      => __( 'Spacing', 'dollie' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .dol-widget-launch-site .dol-widget-icon' => 'margin-right: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'title_style_section',
			[
				'label' => __( 'Title', 'dollie' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .dol-widget-launch-site .dol-widget-title',
			]
		);

		$this->add_control(
			'title_color',
			[
				'label'     => __( 'Color', 'dollie' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .dol-widget-launch-site .dol-widget-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'subtitle_style_section',
			[
				'label' => __( 'Sub Title', 'dollie' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'subtitle_typography',
				'selector' => '{{WRAPPER}} .dol-widget-launch-site .dol-widget-subtitle',
			]
		);

		$this->add_control(
			'subtitle_color',
			[
				'label'     => __( 'Color', 'dollie' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .dol-widget-launch-site .dol-widget-subtitle' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'button_style_section',
			[
				'label' => __( 'Button', 'dollie' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'btn_typography',
				'selector' => '{{WRAPPER}} .dol-widget-launch-site .dol-widget-button a',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'btn_text_shadow',
				'selector' => '{{WRAPPER}} .dol-widget-launch-site .dol-widget-button a',
			]
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		$this->start_controls_tab(
			'tab_button_normal',
			[
				'label' => __( 'Normal', 'dollie' ),
			]
		);

		$this->add_control(
			'btn_text_color',
			[
				'label'     => __( 'Text Color', 'dollie' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .dol-widget-launch-site .dol-widget-button a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'btn_background_color',
			[
				'label'     => __( 'Background Color', 'dollie' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .dol-widget-launch-site .dol-widget-button a' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'btn_box_shadow',
				'selector' => '{{WRAPPER}} .dol-widget-launch-site .dol-widget-button a',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_hover',
			[
				'label' => __( 'Hover', 'dollie' ),
			]
		);

		$this->add_control(
			'btn_hover_color',
			[
				'label'     => __( 'Text Color', 'dollie' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .dol-widget-launch-site .dol-widget-button a:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'btn_hover_bg_color',
			[
				'label'     => __( 'Background Color', 'dollie' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .dol-widget-launch-site .dol-widget-button a:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'btn_hover_border_color',
			[
				'label'     => __( 'Border Color', 'dollie' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => [
					'border_border!' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .dol-widget-launch-site .dol-widget-button a:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'btn_hover_box_shadow',
				'selector' => '{{WRAPPER}} .dol-widget-launch-site .dol-widget-button a:hover',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'btn_border',
				'selector'  => '{{WRAPPER}} .dol-widget-launch-site .dol-widget-button a',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'btn_border_radius',
			[
				'label'      => __( 'Border Radius', 'dollie' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .dol-widget-launch-site .dol-widget-button a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'btn_padding',
			[
				'label'      => __( 'Padding', 'dollie' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .dol-widget-launch-site .dol-widget-button a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$data = [
			'icon'     => $settings['icon'],
			'title'    => $settings['title'],
			'subtitle' => $settings['subtitle'],
			'button'   => $settings['button'],
		];

		dollie()->load_template( 'widgets/dashboard/launch-site', $data, true );
	}

}
