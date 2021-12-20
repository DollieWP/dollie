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

	protected function _register_controls() {
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
				'label'   => __( 'Customers per page', 'dollie' ),
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

		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$settings = $this->get_settings_for_display();

		$current_page       = get_query_var( 'paged' ) ? (int) get_query_var( 'paged' ) : 1;
		$customers_per_page = $settings['customers_per_page'];

		$args = [
			[ 'role__in' => [ 'author', 'subscriber', 'customer' ] ],
			'number' => $customers_per_page, // How many per page
			'paged'  => $current_page, // What page to get, starting from 1.
		];

		if ( isset( $_GET['search'] ) && $_GET['search'] ) {
			$searchword = sanitize_text_field( $_GET['search'] );
			$parts      = explode( ' ', $searchword );

			$args['search_columns'] = [ 'display_name' ];
			$args['search']         = "*{$searchword}*";
			$args['orderby']        = 'display_name';
			$args['order']          = 'ASC';

			if ( ! empty( $parts ) ) {

				$args['meta_query']             = [];
				$args['meta_query']['relation'] = 'OR';

				foreach ( $parts as $part ) {
					$args['meta_query'][] = [
						'key'     => 'first_name',
						'value'   => $part,
						'compare' => 'LIKE',
					];
					$args['meta_query'][] = [
						'key'     => 'last_name',
						'value'   => $part,
						'compare' => 'LIKE',
					];
				}
			}
		}

		$customers = new WP_User_Query( $args );

		$total_customers = $customers->get_total(); // How many users we have in total (beyond the current page)
		$num_pages       = ceil( $total_customers / $customers_per_page ); // How many pages of users we will need

		$view_type = isset( $_GET['list_type'] ) && in_array(
			$_GET['list_type'],
			[
				'list',
				'grid',
			]
		) ? sanitize_text_field( $_GET['list_type'] ) : 'list';

		$data = [
			'customers'       => $customers,
			'total_customers' => $total_customers,
			'per_page'        => $total_customers,
			'pages'           => $num_pages,
			'view_type'       => $view_type,
			'settings'        => $settings,
			'current_page'    => $current_page,
			'query_data'      => [
				'permalink'    => get_the_permalink(),
				'current_page' => get_query_var( 'paged', 1 ),
			],
		];

		dollie()->load_template( 'loop/customers', $data, true );
	}

}
