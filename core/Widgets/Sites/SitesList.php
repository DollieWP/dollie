<?php

namespace Dollie\Core\Widgets\Sites;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Utils\Tpl;
use Elementor\Controls_Manager;
use WP_Query;

/**
 * Class SitesList
 *
 * @package Dollie\Core\Widgets\Dashboard
 */
class SitesList extends \Elementor\Widget_Base {

	public function __construct( $data = [], $args = null ) {
		parent::__construct( $data, $args );

		wp_register_script(
			'dollie-site-list',
			DOLLIE_ASSETS_URL . 'js/widgets/sites-list.js',
			[],
			DOLLIE_VERSION,
			true
		);
	}

	public function get_script_depends() {
		return [ 'dollie-site-list' ];
	}

	public function get_name() {
		return 'dollie-sites-listing';
	}

	public function get_title() {
		return esc_html__( 'Sites', 'dollie' );
	}

	public function get_icon() {
		return 'eicon-posts-grid';
	}

	public function get_categories() {
		return [ 'dollie-category' ];
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Content', 'dollie' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'posts_per_page',
			[
				'label'   => __( 'Posts per page', 'elementor' ),
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
		if ( ! is_user_logged_in() ) {
			return false;
		}

		if ( isset( $_GET['dollie_db_update'] ) ) {
			return false;
		}

		$settings = $this->get_settings_for_display();

		$args = [
			'posts_per_page' => $settings['posts_per_page'],
			'paged'          => get_query_var( 'paged', 1 ),
			'post_type'      => 'container',
			'post_status'    => 'publish',
		];

		if ( isset( $_GET['search'] ) && $_GET['search'] ) {
			$args['meta_query'] = [
				'relation' => 'OR',
				[
					'key'     => 'wpd_installation_name',
					'value'   => sanitize_text_field( $_GET['search'] ),
					'compare' => 'LIKE',
				],
				[
					'key'     => 'wpd_domains',
					'value'   => sanitize_text_field( $_GET['search'] ),
					'compare' => 'LIKE',
				],
			];
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			$args['author'] = get_current_user_id();
		}

		if (isset($_GET['customer']) && $_GET['customer']) {
			$args['author'] = (int) $_GET['customer'];
		}

		$sites = new WP_Query( $args );

		$view_type = isset( $_GET['list_type'] ) && in_array(
			$_GET['list_type'],
			[
				'list',
				'grid',
			]
		) ? sanitize_text_field( $_GET['list_type'] ) : 'list';

		$data = [
			'sites'      => $sites,
			'view_type'  => $view_type,
			'settings'   => $settings,
			'query_data' => [
				'permalink'    => get_the_permalink(),
				'current_page' => get_query_var( 'paged', 1 ),
			],
		];

		Tpl::load( 'loop/sites', $data, true );
	}

}
