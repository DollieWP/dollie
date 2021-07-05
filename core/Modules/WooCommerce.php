<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use WC_Order;

/**
 * Class WooCommerce
 *
 * @package Dollie\Core\Modules
 */
class WooCommerce extends Singleton {

	/**
	 * WooCommerce constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'after_setup_theme', [ $this, 'add_theme_support' ] );
		add_action( 'woocommerce_thankyou', [ $this, 'redirect_to_blueprint' ] );

		add_filter( 'acf/fields/relationship/query/key=field_5e2c1adcc1543', [ $this, 'modify_query' ], 10, 3 );
		add_filter( 'acf/fields/relationship/query/key=field_5e2c1b94c1544', [ $this, 'modify_query' ], 10, 3 );
	}

	/**
	 * Add theme support
	 */
	public function add_theme_support() {
		add_theme_support( 'woocommerce' );
	}


	/**
	 * Redirect to payment success + blueprint if blueprint cookie is set
	 *
	 * @param $order_id
	 */
	public function redirect_to_blueprint( $order_id ) {
		if ( isset( $_COOKIE[ DOLLIE_BLUEPRINTS_COOKIE ] ) && $_COOKIE[ DOLLIE_BLUEPRINTS_COOKIE ] ) {
			$order = new WC_Order( $order_id );
			if ( 'failed' !== $order->status ) {
				wp_redirect( dollie()->get_launch_page_url() . '?payment-status=success&blueprint_id=' . $_COOKIE[ DOLLIE_BLUEPRINTS_COOKIE ] );
				exit;
			}
		}
	}

	/**
	 * Modify query to include/exclude blueprints
	 *
	 * @param $args
	 * @param $field
	 * @param $post_id
	 *
	 * @return mixed
	 */
	public function modify_query( $args, $field, $post_id ) {
		$args['meta_query'][] = [
			'relation' => 'AND',
			[
				'key'   => 'wpd_blueprint_created',
				'value' => 'yes',
			],
			[
				'key'   => 'wpd_is_blueprint',
				'value' => 'yes',
			],
			[
				'key'     => 'wpd_installation_blueprint_title',
				'compare' => 'EXISTS',
			],
		];

		return $args;
	}

}
