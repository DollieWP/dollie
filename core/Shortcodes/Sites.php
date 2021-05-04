<?php

namespace Dollie\Core\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Utils\Tpl;
use WP_Query;

/**
 * Class Sites
 * @package Dollie\Core\Shortcodes
 */
final class Sites extends Singleton implements Base {

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
		add_shortcode( 'dollie-sites', [ $this, 'shortcode' ] );
	}

	/**
	 * Shortcode logic
	 *
	 * @param $atts
	 *
	 * @return bool|false|mixed|string
	 */
	public function shortcode( $atts ) {
		// if ( ! current_user_can( 'manage_options' ) ) {
		// 	return false;
		// }

		if ( isset( $_GET['dollie_db_update'] ) ) {
			return false;
		}

		$a = shortcode_atts(
			[
				'amount'  => '15',
			],
			$atts
		);

		$args = [
			'posts_per_page' => $a['amount'],
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
					'compare' => 'LIKE'
				],
				[
					'key'     => 'wpd_domains',
					'value'   => sanitize_text_field( $_GET['search'] ),
					'compare' => 'LIKE'
				],
				/*[
					'key'     => '_wpd_container_data',
					'value'   => sanitize_text_field( $_GET['search'] ),
					'compare' => 'LIKE'
				]*/
			];
		}

		if (isset($_GET['blueprints']) && $_GET['blueprints']) {
			$args['meta_query'] = [
				'relation' => 'OR',
				[
					'key'     => 'wpd_is_blueprint',
					'value'   => 'yes',
					'compare' => '='
				]
			];
		}


		if ( ! current_user_can( 'manage_options' ) ) {
			$args['author'] = get_current_user_id();
		}

		$sites = new WP_Query( $args );

		$view_type = isset( $_GET['list_type'] ) && in_array( $_GET['list_type'], [
			'list',
			'grid'
		] ) ? sanitize_text_field( $_GET['list_type'] ) : 'list';

		$data = [
			'sites'      => $sites,
			'view_type'  => $view_type,
			'settings'   => $a,
			'query_data' => [
				'permalink'    => get_the_permalink(),
				'current_page' => get_query_var( 'paged', 1 )
			]
		];

		Tpl::load( 'loop/sites', $data, true );
	}

}
