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

		$current_page = get_query_var( 'paged', 1 );
		if ( isset( $_GET['elementor_library'] ) && isset( $_GET['load-page'] ) && $_GET['load-page'] ) {
			$current_page = (int) sanitize_text_field( $_GET['load-page'] );
		}

		$args = [
			'posts_per_page' => isset( $_GET['per_page'] ) && sanitize_text_field( $_GET['per_page'] ) ? sanitize_text_field( $_GET['per_page'] ) : $settings['posts_per_page'],
			'paged'          => $current_page,
			'post_type'      => 'container',
			'post_status'    => 'publish',
		];

		$meta_query = [];

		if ( isset( $_GET['search'] ) && $_GET['search'] ) {
			$meta_query['search'][] = [
				'key'     => 'wpd_installation_name',
				'value'   => sanitize_text_field( $_GET['search'] ),
				'compare' => 'LIKE',
			];

			$meta_query['search'][] = [
				'key'     => 'wpd_domains',
				'value'   => sanitize_text_field( $_GET['search'] ),
				'compare' => 'LIKE',
			];

			$meta_query['search']['relation'] = 'OR';
		}

		if ( isset( $_GET['blueprints'] ) && $_GET['blueprints'] ) {
			$meta_query['blueprint'][] = [
				'key'     => 'wpd_is_blueprint',
				'value'   => 'yes',
				'compare' => '=',
			];
		} else {
			$meta_query['blueprint'][] = [
				'key'     => 'wpd_is_blueprint',
				'value'   => 'no',
				'compare' => '=',
			];

			$meta_query['blueprint'][] = [
				'key'     => 'wpd_is_blueprint',
				'compare' => 'NOT EXISTS',
			];

			$meta_query['blueprint']['relation'] = 'OR';
		}

		if ( count( $meta_query ) ) {
			if ( isset( $meta_query['search'] ) && isset( $meta_query['blueprint'] ) ) {
				$args['meta_query'][]           = $meta_query['search'];
				$args['meta_query'][]           = $meta_query['blueprint'];
				$args['meta_query']['relation'] = 'AND';
			} elseif ( isset( $meta_query['search'] ) ) {
				$args['meta_query'] = $meta_query['search'];
			} elseif ( isset( $meta_query['blueprint'] ) ) {
				$args['meta_query'] = $meta_query['blueprint'];
			}
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			$args['author'] = get_current_user_id();
		}

		if ( isset( $_GET['customer'] ) && $_GET['customer'] ) {
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
				'current_page' => $current_page,
			],
		];

		Tpl::load( 'loop/sites', $data, true );
	}

}
