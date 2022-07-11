<?php

namespace Dollie\Core\Widgets\Dashboard;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;

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
				'default'     => __( 'Plan detalis', 'dollie' ),
				'label_block' => true,
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'item_title',
			[
				'label' => __( 'Text', 'dollie' ),
				'type'  => Controls_Manager::TEXT,
			]
		);

		$repeater->add_control(
			'item_type',
			[
				'label'   => __( 'Data source', 'dollie' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'subscription-plan' => __( 'Subscription plan', 'dollie' ),
					'sites-available'   => __( 'Sites available', 'dollie' ),
					'storage-available' => __( 'Available storage', 'dollie' ),
					'storage-used'      => __( 'Used storage', 'dollie' ),
				],
				'default' => 'subscription-plan',
			]
		);

		$this->add_control(
			'items',
			[
				'label'       => __( 'Items', 'dollie' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => [
					[
						'item_title' => __( 'Current plan', 'dollie' ),
						'item_type'  => 'subscription-plan',
					],
					[
						'item_title' => __( 'Available sites', 'dollie' ),
						'item_type'  => 'sites-available',
					],
					[
						'item_title' => __( 'Available storage', 'dollie' ),
						'item_type'  => 'storage-available',
					],
					[
						'item_title' => __( 'Used storage', 'dollie' ),
						'item_type'  => 'storage-used',
					],
				],
				'title_field' => '{{{ item_title }}}',
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
					'{{WRAPPER}} .dol-widget-subscription' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'container_border',
				'selector' => '{{WRAPPER}} .dol-widget-subscription',
			]
		);

		$this->add_control(
			'container_border_radius',
			[
				'label'      => __( 'Border Radius', 'dollie' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .dol-widget-subscription' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
				'selector' => '{{WRAPPER}} .dol-widget-subscription .dol-widget-title-section h4',
			]
		);

		$this->add_control(
			'title_bg_color',
			[
				'label'     => __( 'Background', 'dollie' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .dol-widget-subscription .dol-widget-title-section' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'title_color',
			[
				'label'     => __( 'Color', 'dollie' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .dol-widget-subscription .dol-widget-title-section h4' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'title_align',
			[
				'label'     => __( 'Alignment', 'dollie' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'   => [
						'title' => __( 'Left', 'dollie' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'dollie' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'  => [
						'title' => __( 'Right', 'dollie' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .dol-widget-subscription .dol-widget-title-section' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'title_section_border',
				'selector'  => '{{WRAPPER}} .dol-widget-subscription .dol-widget-title-section',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'title_section_border_radius',
			[
				'label'      => __( 'Border Radius', 'dollie' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .dol-widget-subscription .dol-widget-title-section' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'title_section_padding',
			[
				'label'      => __( 'Padding', 'dollie' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .dol-widget-subscription .dol-widget-title-section' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'items_container_style_section',
			[
				'label' => __( 'Items Container', 'dollie' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'items_items_padding',
			[
				'label'      => __( 'Items Padding', 'dollie' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .dol-widget-subscription .dol-widget-content-section ul > li' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'items_container_padding',
			[
				'label'      => __( 'Section Padding', 'dollie' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .dol-widget-subscription .dol-widget-content-section' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'items_title_style_section',
			[
				'label' => __( 'Item Title', 'dollie' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'item_title_width',
			[
				'label'      => __( 'Width', 'dollie' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .dol-widget-subscription .dol-widget-content-section .dol-widget-item-title' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'item_title_typography',
				'selector' => '{{WRAPPER}} .dol-widget-subscription .dol-widget-content-section .dol-widget-item-title',
			]
		);

		$this->add_control(
			'item_title_color',
			[
				'label'     => __( 'Color', 'dollie' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .dol-widget-subscription .dol-widget-content-section .dol-widget-item-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'items_data_source_style_section',
			[
				'label' => __( 'Item Data Source', 'dollie' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'item_data_source_width',
			[
				'label'      => __( 'Width', 'dollie' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .dol-widget-subscription .dol-widget-content-section .dol-widget-item-source' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'item_data_source_typography',
				'selector' => '{{WRAPPER}} .dol-widget-subscription .dol-widget-content-section .dol-widget-item-source',
			]
		);

		$this->add_control(
			'item_data_source_color',
			[
				'label'     => __( 'Color', 'dollie' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .dol-widget-subscription .dol-widget-content-section .dol-widget-item-source' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings     = $this->get_settings_for_display();
		$subscription = dollie()->subscription();

		$data = [
			'title' => $settings['title'],
			'items' => [],
		];

		foreach ( $settings['items'] as $item ) {
			$value = '';

			switch ( $item['item_type'] ) {
				case 'subscription-plan':
					$value = $subscription->subscription_name();
					break;
				case 'sites-available':
					$value = $subscription->sites_available();
					break;
				case 'storage-available':
					$available_storage = $subscription->storage_available();

					if ( $available_storage ) {
						$value = esc_html( $available_storage ) . ' GB';
					} else {
						$value = esc_html( $available_storage );
					}
					break;
				case 'storage-used':
					$value = dollie()->convert_to_readable_size( dollie()->insights()->get_total_container_size() );
					break;
				default:
			}

			$data['items'][] = [
				'title' => $item['item_title'],
				'value' => $value,
			];
		}

		dollie()->load_template( 'widgets/dashboard/subscription-details', $data, true );
	}

}
