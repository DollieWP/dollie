<?php

namespace Dollie\Core\Widgets\Customers;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;
use WP_User_Query;

/**
 * Class CustomersList
 *
 * @package Dollie\Core\Widgets\Dashboard
 */
class CustomersList extends \Elementor\Widget_Base {

	public function __construct( $data = [], $args = null ) {
		parent::__construct( $data, $args );

		wp_register_script(
			'dollie-customers-list',
			DOLLIE_ASSETS_URL . 'js/widgets/customers-list.js',
			[],
			DOLLIE_VERSION,
			true
		);
	}

	public function get_script_depends() {
		return [ 'dollie-customers-list' ];
	}

	public function get_name() {
		return 'dollie-customers-listing';
	}

	public function get_title() {
		return esc_html__( 'Customers', 'dollie' );
	}

	public function get_icon() {
		return 'eicon-posts-grid';
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
			'customers_per_page',
			[
				'label'   => __( 'Customers Per Page', 'dollie' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => - 1,
				'max'     => 40,
				'step'    => 1,
				'default' => 10,
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		echo \Dollie\Core\Shortcodes\CustomersList::instance()->shortcode( $settings );
	}

}
