<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Utils\Helpers;
use Dollie\Core\Log;
use WC_Order;

/**
 * Class WooCommerce
 * @package Dollie\Core\Modules
 */
class WooCommerce extends Singleton {

	/**
	 * @var mixed
	 */
	private $helpers;

	/**
	 * WooCommerce constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->helpers = Helpers::instance();

		add_action( 'after_setup_theme', [ $this, 'woocommerce_support' ] );
		add_action( 'wf_before_container', [ $this, 'my_account_header' ] );
		add_filter( 'woocommerce_payment_complete_order_status', [ $this, 'mark_all_orders_as_complete' ], 10, 2 );
		add_action( 'woocommerce_thankyou', [ $this, 'checkout_redirect' ] );
		add_filter( 'acf/fields/relationship/query/key=field_5e2c1adcc1543', [
			$this,
			'include_exclude_blueprints'
		], 10, 3 );
		add_filter( 'acf/fields/relationship/query/key=field_5e2c1b94c1544', [
			$this,
			'include_exclude_blueprints'
		], 10, 3 );
	}

	public function woocommerce_support() {
		add_theme_support( 'woocommerce' );
	}

	public function mark_all_orders_as_complete( $order_status, $order_id ) {
		$order = new WC_Order( $order_id );
		if ( $order_status === 'processing' && ( $order->status === 'on-hold' || $order->status === 'pending' || $order->status === 'failed' ) ) {
			return 'completed';
		}

		return $order_status;
	}

	public function my_account_header() {
		if ( is_page( 'my-account' ) ) {
			include_once get_template_directory() . '/templates/site-manager/my-account-header.php';
		}
	}

	public function checkout_redirect( $order_id ) {
		if ( isset( $_COOKIE['dollie_blueprint_id'] ) && $_COOKIE['dollie_blueprint_id'] ) {
			$order = new WC_Order( $order_id );
			$url   = get_site_url() . '/launch-site/?payment-status=success&blueprint_id=' . $_COOKIE['dollie_blueprint_id'];
			if ( $order->status !== 'failed' ) {
				wp_redirect( $url );
				exit;
			}
		}
	}

	public function include_exclude_blueprints( $args, $field, $post_id ) {
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
