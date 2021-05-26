<?php

namespace Dollie\Core\Modules\Subscription\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Modules\Blueprints;

/**
 * Class WooCommerce
 *
 * @package Dollie\Core\Modules\Subscription\Plugin
 */
class WooCommerce implements SubscriptionInterface {

	const
		SUB_STATUS_ANY = 'any',
		SUB_STATUS_ACTIVE = 'active';

	/**
	 * WooCommerce constructor
	 */
	public function __construct() {
		add_action( 'acf/init', [ $this, 'load_acf' ] );
		add_action( 'woocommerce_thankyou', [ $this, 'redirect_to_blueprint' ] );
		add_action( 'after_setup_theme', [ $this, 'add_theme_support' ] );

		add_filter( 'dollie/required_plugins', [ $this, 'required_woocommerce' ] );
	}

	/**
	 * Load ACF
	 *
	 * @return void
	 */
	public function load_acf() {
		require DOLLIE_CORE_PATH . 'Modules/Subscription/Plugin/acf-fields/woo-acf-fields.php';
	}

	/**
	 * Require Woocommerce plugins
	 *
	 * @param array $plugins
	 *
	 * @return array
	 */
	public function required_woocommerce( $plugins ) {
		$plugins[] = [
			'name'             => 'WooCommerce',
			'slug'             => 'woocommerce',
			'required'         => true,
			'version'          => '',
			'force_activation' => false,
		];

		$plugins[] = [
			'name'             => 'WooCommerce Subscriptions',
			'slug'             => 'woocommerce-subscriptions',
			'required'         => true,
			'version'          => '3.0.10',
			'force_activation' => false,
			'source'           => 'https://api.getdollie.com/releases/?action=download&slug=woocommerce-subscriptions',
		];

		return $plugins;
	}

	/**
	 * Redirect to payment success + blueprint if blueprint cookie is set
	 *
	 * @param $order_id
	 */
	public function redirect_to_blueprint( $order_id ) {
		if ( isset( $_COOKIE[ Blueprints::COOKIE_NAME ] ) && $_COOKIE[ Blueprints::COOKIE_NAME ] ) {
			$order = new \WC_Order( $order_id );
			if ( 'failed' !== $order->status ) {
				wp_redirect( dollie()->get_launch_page_url() . '?payment-status=success&blueprint_id=' . $_COOKIE[ Blueprints::COOKIE_NAME ] );
				exit;
			}
		}
	}

	/**
	 * Add theme support
	 */
	public function add_theme_support() {
		add_theme_support( 'woocommerce' );
	}

	/**
	 * Get checkout link
	 *
	 * @param $product_id
	 * @param $blueprint_id
	 *
	 * @return mixed|string|void
	 * @throws \Exception
	 */
	public function get_checkout_link( $product_id, $blueprint_id ) {
		if ( ! function_exists( 'wc_get_product' ) ) {
			return '#';
		}

		$product_obj = wc_get_product( $product_id );

		$link_args = [
			'add-to-cart'  => $product_id,
			'blueprint_id' => $blueprint_id,
		];

		if ( method_exists( $product_obj, 'get_type' ) && $product_obj->get_type() === 'variable-subscription' ) {
			$default_atts = $product_obj->get_default_attributes();

			if ( isset( $default_atts['pa_subscription'] ) ) {
				$data_store                = \WC_Data_Store::load( 'product' );
				$default_variation         = $data_store->find_matching_product_variation( $product_obj, [ 'attribute_pa_subscription' => $default_atts['pa_subscription'] ] );
				$link_args['variation_id'] = $default_variation;
			}
		}

		$link = add_query_arg(
			$link_args,
			wc_get_checkout_url()
		);

		return apply_filters( 'dollie/woo/checkout_link', $link, $product_id, $blueprint_id );
	}

	/**
	 * Get subscriptions for customer
	 *
	 * @param string $status
	 * @param null|int $customer_id
	 *
	 * @return array|bool
	 */
	public function get_customer_subscriptions( $status = null, $customer_id = null ) {
		if ( ! function_exists( 'wcs_get_subscriptions' ) ) {
			return false;
		}

		if ( ! $customer_id ) {
			$customer_id = get_current_user_id();
		}

		if ( ! $status ) {
			$status = self::SUB_STATUS_ANY;
		}

		$subscriptions = wcs_get_subscriptions(
			[
				'customer_id'         => $customer_id,
				'subscription_status' => $status,
			]
		);

		if ( ! is_array( $subscriptions ) || empty( $subscriptions ) ) {
			return false;
		}

		$data = [
			'plans'     => [],
			'resources' => [
				'max_allowed_installs' => 0,
				'max_allowed_size'     => 0,
				'staging'              => null
			],
		];

		foreach ( $subscriptions as $subscription_id => $subscription ) {
			// Getting the subscription Order ID.
			$the_subscription = wcs_get_subscription( $subscription_id );

			// Get the right number of items, count also any upgraded/downgraded orders.
			$order_items = $the_subscription->get_items();

			if ( ! is_array( $order_items ) || empty( $order_items ) ) {
				continue;
			}

			// Iterating through each item in the order.
			foreach ( $order_items as $item_id => $item_data ) {
				$id = $item_data['product_id'];

				if ( 0 === $id ) {
					continue;
				}

				// Filter out non Dollie subscriptions by checking custom meta field.
				if ( ! get_field( '_wpd_installs', $id ) || ! get_field( '_wpd_staging', $id ) ) {
					continue;
				}

				$installs = (int) get_field( '_wpd_installs', $id );
				$max_size = get_field( '_wpd_max_size', $id );
				$staging  = get_field( '_wpd_staging', $id );

				if ( ! $max_size ) {
					$max_size = 0;
				}

				$data['plans']['products'][ $id ] = [
					'name'                => $item_data['name'],
					'installs'            => $installs,
					'max_size'            => $max_size,
					'included_blueprints' => get_field( '_wpd_included_blueprints', $id ),
					'excluded_blueprints' => get_field( '_wpd_excluded_blueprints', $id ),
				];

				$quantity = $item_data['quantity'] ? (int) $item_data['quantity'] : 1;

				$data['resources']['max_allowed_installs'] += $installs * $quantity;
				$data['resources']['max_allowed_size']     += $max_size * $quantity;
				$data['resources']['name']                 = $item_data['name'];

				if ( $data['resources']['staging'] === null && in_array( $staging, [ 'yes', 'no' ] ) ) {
					$data['resources']['staging'] = $staging === 'yes';
				}
			}
		}

		return $data;
	}

	/**
	 * Check if a user has bought any product
	 *
	 * @param null|int $user_id
	 *
	 * @return bool
	 */
	public function has_bought_product( $user_id = null ) {
		global $wpdb;
		$customer_id         = ! $user_id ? get_current_user_id() : $user_id;
		$paid_order_statuses = array_map( 'esc_sql', wc_get_is_paid_statuses() );

		$results = $wpdb->get_col(
			"SELECT p.ID FROM {$wpdb->prefix}posts AS p
			INNER JOIN {$wpdb->prefix}postmeta AS pm ON p.ID = pm.post_id
			WHERE p.post_status IN ( 'wc-" . implode( "','wc-", $paid_order_statuses ) . "' )
			AND p.post_type LIKE 'shop_order'
			AND pm.meta_key = '_customer_user'
			AND pm.meta_value = $customer_id"
		);

		return count( $results ) > 0;
	}

	/**
	 * Check if customer has subscription
	 *
	 * @return bool
	 */
	public function has_subscription() {
		if ( get_option( 'options_wpd_charge_for_deployments' ) !== '1' ) {
			return true;
		}

		$subscription = $this->get_customer_subscriptions();

		return $subscription ? (bool) $subscription['plans'] : $subscription;
	}

	/**
	 * Get how many sites are left available for customer
	 *
	 * @return int|mixed
	 */
	public function sites_available() {
		$subscription = $this->get_customer_subscriptions( self::SUB_STATUS_ACTIVE );

		if ( ! $subscription ) {
			return 0;
		}

		$total_site = dollie()->count_customer_containers( get_current_user_id() );

		return $subscription['resources']['max_allowed_installs'] - $total_site;
	}

	/**
	 * Get storage available for customer
	 *
	 * @return int|mixed
	 */
	public function storage_available() {
		$subscription = $this->get_customer_subscriptions( self::SUB_STATUS_ACTIVE );

		if ( ! $subscription ) {
			return 0;
		}

		return $subscription['resources']['max_allowed_size'];
	}

	/**
	 * Get subscription name
	 *
	 * @return mixed|string
	 */
	public function subscription_name() {
		$subscription = $this->get_customer_subscriptions( self::SUB_STATUS_ACTIVE );

		if ( ! $subscription || ! isset( $subscription['resources']['name'] ) ) {
			return __( 'None', 'dollie' );
		}

		return $subscription['resources']['name'];
	}

	/**
	 * Check if site limit has been reached
	 *
	 * @return bool
	 */
	public function site_limit_reached() {
		if ( ! class_exists( \WooCommerce::class ) || get_option( 'options_wpd_charge_for_deployments' ) !== '1' ) {
			return false;
		}

		$subscription = $this->get_customer_subscriptions( self::SUB_STATUS_ACTIVE );

		if ( ! is_array( $subscription ) || empty( $subscription ) ) {
			return false;
		}

		$total_site = (int) dollie()->count_customer_containers( get_current_user_id() );

		return $this->has_subscription() && ( $subscription['resources']['max_allowed_installs'] - $total_site ) <= 0 && ! current_user_can( 'manage_options' );
	}

	/**
	 * Check if the size limit has been reached
	 *
	 * @return bool
	 */
	public function size_limit_reached() {
		if ( ! class_exists( \WooCommerce::class ) || get_option( 'options_wpd_charge_for_deployments' ) !== '1' ) {
			return false;
		}

		$subscription = $this->get_customer_subscriptions( self::SUB_STATUS_ACTIVE );

		if ( ! $subscription ) {
			return false;
		}

		$total_size   = dollie()->get_total_container_size();
		$allowed_size = $subscription['resources']['max_allowed_size'] * 1024 * 1024 * 1024;

		return $this->has_subscription() && $total_size >= $allowed_size && ! current_user_can( 'manage_options' );
	}

	/**
	 * Get excluded blueprints
	 *
	 * @return bool
	 */
	public function get_excluded_blueprints() {
		$subscription = $this->get_customer_subscriptions( self::SUB_STATUS_ACTIVE );

		if ( ! $subscription ) {
			return false;
		}

		$get_first = $subscription['plans']['products'];
		$product   = reset( $get_first );

		return $product['excluded_blueprints'];
	}

	/**
	 * Get included blueprints
	 *
	 * @return bool
	 */
	public function get_included_blueprints() {
		$subscription = $this->get_customer_subscriptions( self::SUB_STATUS_ACTIVE );

		if ( ! $subscription ) {
			return false;
		}

		$get_first = $subscription['plans']['products'];
		$product   = reset( $get_first );

		return $product['included_blueprints'];
	}

	public function is_staging_allowed( $user_id = null ) {
		if ( get_option( 'options_wpd_charge_for_deployments' ) !== '1' ) {
			return true;
		}
		if ( $user_id === null ) {
			$user_id = get_current_user_id();
		}

		if ( ! get_field( 'wpd_enable_staging', 'options' ) ) {
			return false;
		}

		$subscriptions = $this->get_customer_subscriptions( 'active', $user_id );

		// apply overrides at product level
		if ( isset( $subscriptions['resources']['staging'] ) ) {
			return $subscriptions['resources']['staging'];
		}

		$default = get_field( 'wpd_staging_restrictions', 'options' );

		return $default === 'allow';

	}

}
