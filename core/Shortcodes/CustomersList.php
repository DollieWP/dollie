<?php

namespace Dollie\Core\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use WP_Query;

/**
 * Class Sites
 *
 * @package Dollie\Core\Shortcodes
 */
final class CustomersList extends Singleton implements Base {
	/**
	 * Sites constructor.
	 */
	public function __construct() {
		parent::__construct();
		add_action( 'init', [ $this, 'register' ] );
	}

	/**
	 * Add shortcode
	 *
	 * @return mixed|void
	 */
	public function register() {
		add_shortcode( 'dollie-customers', [ $this, 'shortcode' ] );
	}

	/**
	 * Shortcode logic
	 *
	 * @param $atts
	 *
	 * @return bool|false|mixed|string
	 */
	public function shortcode( $atts ) {

		$settings = shortcode_atts(
			[
				'customers_per_page' => '10',
			],
			$atts
		);

		if ( ! is_user_logged_in() ) {
			return false;
		}

		if ( isset( $_GET['dollie_db_update'] ) ) {
			return false;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

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

		$customers = new \WP_User_Query( $args );

		$total_customers = $customers->get_total();
		$num_pages       = ceil( $total_customers / $customers_per_page );

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

		return dollie()->load_template( 'loop/customers', $data, false );

	}

}
