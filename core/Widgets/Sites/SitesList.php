<?php

namespace Dollie\Core\Widgets\Sites;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

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

	protected function register_controls() {
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
				'label'   => __( 'Posts per page', 'dollie' ),
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

		global $wp_query;

		$current_page = isset( $_GET['dpg'] ) && (int) $_GET['dpg'] ? (int) sanitize_text_field( $_GET['dpg'] ) : 1;

		if ( isset( $_GET['elementor_library'] ) && isset( $_GET['load-page'] ) && $_GET['load-page'] ) {
			$current_page = (int) sanitize_text_field( $_GET['load-page'] );
		}

		$per_page = $settings['posts_per_page'];

		if ( isset( $_GET['per_page'] ) && (int) sanitize_text_field( $_GET['per_page'] ) > 0 ) {
			$per_page = (int) sanitize_text_field( $_GET['per_page'] );
		}

		$args = [
			'posts_per_page' => $per_page,
			'paged'          => $current_page,
			'post_type'      => 'container',
			'post_status'    => 'publish',
		];

		$meta_query = [];

		if ( isset( $_GET['search'] ) && $_GET['search'] ) {
			$meta_query['search'][] = [
				'key'     => 'dollie_container_details',
				'value'   => sanitize_text_field( $_GET['search'] ),
				'compare' => 'LIKE',
			];
		}

		if ( isset( $_GET['status'] ) && $_GET['status'] ) {
			$status = sanitize_text_field( $_GET['status'] );

			if ( in_array(
				$status,
				[
					'Running',
					'Stopped',
					'Deploying',
					'Undeployed',
					'Deploy Failure',
				],
				true
			) ) {
				$meta_query['search'][] = [
					'key'     => 'dollie_container_details',
					'value'   => $status,
					'compare' => 'LIKE',
				];
			}
		}

		if ( isset( $meta_query['search'] ) && count( $meta_query['search'] ) > 1 ) {
			$meta_query['search']['relation'] = 'AND';
		}

		if ( isset( $_GET['blueprints'] ) && $_GET['blueprints'] ) {
			$meta_query['container_type'][] = [
				'key'     => 'dollie_container_type',
				'value'   => '1',
				'compare' => '=',
			];
		} else {
			$meta_query['container_type'][] = [
				'key'     => 'dollie_container_type',
				'value'   => '0',
				'compare' => '=',
			];
		}

		if ( ! isset( $_GET['blueprints'] ) && isset( $_GET['site_type'] ) ) {
			$site_type = sanitize_text_field( $_GET['site_type'] );

			if ( in_array(
				$site_type,
				[
					'vip',
					'normal',
				],
				true
			) ) {
				$meta_query['container_type'][] = [
					'key'     => 'dollie_vip_site',
					'value'   => '1',
					'compare' => 'vip' === $site_type ? '=' : '!=',
				];

				$meta_query['container_type']['relation'] = 'AND';
			}
		}

		if ( isset( $meta_query['search'] ) ) {
			$args['meta_query'][]           = $meta_query['search'];
			$args['meta_query'][]           = $meta_query['container_type'];
			$args['meta_query']['relation'] = 'AND';
		} else {
			$args['meta_query'] = $meta_query['container_type'];
		}

		if ( isset( $_GET['customer'] ) && $_GET['customer'] ) {
			$args[' author'] = (int) sanitize_text_field( $_GET['customer'] );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			$args['author'] = get_current_user_id();
		}

		$sites = new WP_Query( $args );

		$pagenum_link = html_entity_decode( get_pagenum_link() );
		$url_parts    = explode( '?', $pagenum_link );

		$data = [
			'sites'       => $sites->get_posts(),
			'sites_pages' => $sites->max_num_pages,
			'settings'    => $settings,
			'query_data'  => [
				'permalink'    => $url_parts[0],
				'current_page' => $current_page,
			],
		];

		dollie()->load_template( 'loop/sites', $data, true );
	}

}
