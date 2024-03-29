<?php

namespace Dollie\Core\Widgets\Launch;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;

/**
 * Class LaunchSite
 *
 * @package Dollie\Core\Widgets\Sites
 */
class LaunchSite extends \Elementor\Widget_Base {

	public function get_script_depends() {
		return [ 'acf-field-group' ];
	}

	public function get_style_depends() {
		return [ 'acf-pro-field-group' ];
	}

	public function get_name() {
		return 'dollie-launch-site';
	}

	public function get_title() {
		return esc_html__( 'Launch Site', 'dollie' );
	}

	public function get_icon() {
		return 'eicon-form-horizontal';
	}

	public function get_categories() {
		return [ 'dollie-category' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Content', 'dollie' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'type',
			[
				'label'   => __( 'Type of site to launch', 'dollie' ),
				'type'    => Controls_Manager::SELECT2,
				'options' => [
					'site'      => 'Site',
					'blueprint' => 'Blueprint',
				],
				'default' => 'site',
			]
		);

		$this->add_control(
			'advanced_settings',
			[
				'label'        => __( 'Advanced Settings State', 'dollie' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'dollie' ),
				'label_off'    => __( 'Hide', 'dollie' ),
				'return_value' => '1',
				'default'      => '0',
				'condition'    => [
					'type' => 'site',
				],
			]
		);

		$this->add_control(
			'button_text',
			[
				'label'   => __( 'Button text', 'dollie' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$data = [
			'settings' => $this->get_settings_for_display(),
		];

		dollie()->load_template( 'widgets/launch/launch-site', $data, true );
	}

}
