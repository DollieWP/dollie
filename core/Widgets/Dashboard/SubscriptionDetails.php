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
 * Class AccessDetails
 *
 * @package Dollie\Core\Widgets\Dashboard
 */
class AccessDetails extends \Elementor\Widget_Base {

	public function get_name() {
		return 'dollie-dashboard-subscription-details';
	}

	public function get_title() {
		return esc_html__( 'Access Details', 'dollie' );
	}

	public function get_icon() {
		return 'eicon-product-price';
	}

	public function get_categories() {
		return array( 'dollie-category' );
	}

	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			array(
				'label' => __( 'Content', 'dollie' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'dollie' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Plan detalis', 'dollie' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'no_sub_text',
			array(
				'label'       => __( 'No subscription text', 'dollie' ),
				'type'        => Controls_Manager::TEXTAREA,
				'default'     => __( 'You have no active subscriptions. Please sign-up for one of our plans to launch your site(s)!', 'dollie' ),
				'label_block' => true,
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'item_title',
			array(
				'label' => __( 'Text', 'dollie' ),
				'type'  => Controls_Manager::TEXT,
			)
		);

		$repeater->add_control(
			'item_type',
			array(
				'label'   => __( 'Data source', 'dollie' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'subscription-plan' => __( 'Access plan', 'dollie' ),
					'sites-available'   => __( 'Sites available', 'dollie' ),
					'storage-available' => __( 'Available storage', 'dollie' ),
					'storage-used'      => __( 'Used storage', 'dollie' ),
				),
				'default' => 'subscription-plan',
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => __( 'Items', 'dollie' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array(
						'item_title' => __( 'Current plan', 'dollie' ),
						'item_type'  => 'subscription-plan',
					),
					array(
						'item_title' => __( 'Available sites', 'dollie' ),
						'item_type'  => 'sites-available',
					),
					array(
						'item_title' => __( 'Available storage', 'dollie' ),
						'item_type'  => 'storage-available',
					),
					array(
						'item_title' => __( 'Used storage', 'dollie' ),
						'item_type'  => 'storage-used',
					),
				),
				'title_field' => '{{{ item_title }}}',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'container_style_section',
			array(
				'label' => __( 'Container', 'dollie' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'container_bg_color',
			array(
				'label'     => __( 'Background', 'dollie' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .dol-widget-subscription' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'container_border',
				'selector' => '{{WRAPPER}} .dol-widget-subscription',
			)
		);

		$this->add_control(
			'container_border_radius',
			array(
				'label'      => __( 'Border Radius', 'dollie' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .dol-widget-subscription' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'title_style_section',
			array(
				'label' => __( 'Title', 'dollie' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .dol-widget-subscription .dol-widget-title-section h4',
			)
		);

		$this->add_control(
			'title_bg_color',
			array(
				'label'     => __( 'Background', 'dollie' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .dol-widget-subscription .dol-widget-title-section' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => __( 'Color', 'dollie' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .dol-widget-subscription .dol-widget-title-section h4' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'title_align',
			array(
				'label'     => __( 'Alignment', 'dollie' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'left'   => array(
						'title' => __( 'Left', 'dollie' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => __( 'Center', 'dollie' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'  => array(
						'title' => __( 'Right', 'dollie' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .dol-widget-subscription .dol-widget-title-section' => 'text-align: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'title_section_border',
				'selector'  => '{{WRAPPER}} .dol-widget-subscription .dol-widget-title-section',
				'separator' => 'before',
			)
		);

		$this->add_control(
			'title_section_border_radius',
			array(
				'label'      => __( 'Border Radius', 'dollie' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .dol-widget-subscription .dol-widget-title-section' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'title_section_padding',
			array(
				'label'      => __( 'Padding', 'dollie' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .dol-widget-subscription .dol-widget-title-section' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'items_container_style_section',
			array(
				'label' => __( 'Items Container', 'dollie' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'items_items_padding',
			array(
				'label'      => __( 'Items Padding', 'dollie' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .dol-widget-subscription .dol-widget-content-section ul > li' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'items_container_padding',
			array(
				'label'      => __( 'Section Padding', 'dollie' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .dol-widget-subscription .dol-widget-content-section' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'items_title_style_section',
			array(
				'label' => __( 'Item Title', 'dollie' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'item_title_width',
			array(
				'label'      => __( 'Width', 'dollie' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .dol-widget-subscription .dol-widget-content-section .dol-widget-item-title' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'item_title_typography',
				'selector' => '{{WRAPPER}} .dol-widget-subscription .dol-widget-content-section .dol-widget-item-title',
			)
		);

		$this->add_control(
			'item_title_color',
			array(
				'label'     => __( 'Color', 'dollie' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .dol-widget-subscription .dol-widget-content-section .dol-widget-item-title' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'items_data_source_style_section',
			array(
				'label' => __( 'Item Data Source', 'dollie' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'item_data_source_width',
			array(
				'label'      => __( 'Width', 'dollie' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .dol-widget-subscription .dol-widget-content-section .dol-widget-item-source' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'item_data_source_typography',
				'selector' => '{{WRAPPER}} .dol-widget-subscription .dol-widget-content-section .dol-widget-item-source',
			)
		);

		$this->add_control(
			'item_data_source_color',
			array(
				'label'     => __( 'Color', 'dollie' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .dol-widget-subscription .dol-widget-content-section .dol-widget-item-source' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'no_sub_style_section',
			array(
				'label' => __( 'No subscription', 'dollie' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'no_sub_typography',
				'selector' => '{{WRAPPER}} .dol-widget-subscription .dol-widget-content-section .dol-widget-no-subscription',
			)
		);

		$this->add_control(
			'no_sub_color',
			array(
				'label'     => __( 'Color', 'dollie' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .dol-widget-subscription .dol-widget-content-section .dol-widget-no-subscription' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings     = $this->get_settings_for_display();
		$subscription = dollie()->subscription();

		$data = array(
			'title'       => $settings['title'],
			'no_sub_text' => $settings['no_sub_text'],
			'items'       => array(),
		);

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

			$data['items'][] = array(
				'title' => $item['item_title'],
				'value' => $value,
			);
		}

		dollie()->load_template( 'widgets/dashboard/subscription-details', $data, true );
	}
}
