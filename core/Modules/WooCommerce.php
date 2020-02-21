<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use WC_Order;

/**
 * Class WooCommerce
 * @package Dollie\Core\Modules
 */
class WooCommerce extends Singleton {

	/**
	 * WooCommerce constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'after_setup_theme', [ $this, 'add_theme_support' ] );

		// Todo: move next line to theme
		add_action( 'wf_before_container', [ $this, 'my_account_header' ] );

		add_filter( 'woocommerce_payment_complete_order_status', [ $this, 'mark_order_as_complete' ], 10, 2 );
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
	 * Mark order as complete if payment went through
	 *
	 * @param $order_status
	 * @param $order_id
	 *
	 * @return string
	 */
	public function mark_order_as_complete( $order_status, $order_id ) {
		$order = new WC_Order( $order_id );
		if ( $order_status === 'processing' && ( $order->status === 'on-hold' || $order->status === 'pending' || $order->status === 'failed' ) ) {
			return 'completed';
		}

		return $order_status;
	}

	// Todo: move next funciton to theme
	public function my_account_header() {
		if ( is_page( 'my-account' ) ) {
			include_once get_template_directory() . '/templates/site-manager/my-account-header.php';
		}
	}

	/**
	 * Redirect to payment success + blueprint if blueprint cookie is set
	 *
	 * @param $order_id
	 */
	public function redirect_to_blueprint( $order_id ) {
		if ( isset( $_COOKIE['dollie_blueprint_id'] ) && $_COOKIE['dollie_blueprint_id'] ) {
			$order = new WC_Order( $order_id );
			if ( $order->status !== 'failed' ) {
				wp_redirect( get_site_url() . '/launch-site/?payment-status=success&blueprint_id=' . $_COOKIE['dollie_blueprint_id'] );
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
