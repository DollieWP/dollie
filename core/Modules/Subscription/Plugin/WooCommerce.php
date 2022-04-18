<?php

namespace Dollie\Core\Modules\Subscription\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;

/**
 * Class WooCommerce
 *
 * @package Dollie\Core\Modules\Subscription\Plugin
 */
class WooCommerce extends Singleton implements SubscriptionInterface {

	const
		SUB_STATUS_ANY    = 'any',
		SUB_STATUS_ACTIVE = 'active';

	/**
	 * WooCommerce constructor
	 */
	public function __construct() {
		add_action( 'acf/init', [ $this, 'load_acf' ] );
		add_action( 'woocommerce_thankyou', [ $this, 'redirect_to_blueprint' ] );
		add_action( 'after_setup_theme', [ $this, 'add_theme_support' ] );

		add_filter( 'dollie/required_plugins', [ $this, 'required_woocommerce' ] );

		add_filter( 'dollie/blueprints', [ $this, 'filter_blueprints' ] );

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
		if ( isset( $_COOKIE[ DOLLIE_BLUEPRINTS_COOKIE ] ) && $_COOKIE[ DOLLIE_BLUEPRINTS_COOKIE ] ) {
			$order = new \WC_Order( $order_id );
			if ( 'failed' !== $order->status ) {
				wp_redirect( dollie()->get_launch_page_url() . '?payment-status=success&blueprint_id=' . $_COOKIE[ DOLLIE_BLUEPRINTS_COOKIE ] );
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
	 * @param $args
	 *
	 * @return mixed|string|void
	 * @throws \Exception
	 */
	public function get_checkout_link( $args ) {
		if ( ! function_exists( 'wc_get_product' ) ) {
			return '#';
		}

		$product_obj = wc_get_product( $args['product_id'] );
		if ( ! $product_obj ) {
			return '#';
		}

		$link_args = [
			'add-to-cart'  => $args['product_id'],
			'blueprint_id' => $args['blueprint_id'],
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

		return apply_filters( 'dollie/woo/checkout_link', $link, $args );
	}

	/**
	 * Get subscriptions for customer
	 *
	 * @param string   $status
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

		$transient = 'wpd_woo_subscription_' . $customer_id . '_' . $status;
		if ( $data = get_transient( $transient ) ) {
			return $data;
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
				'staging_max_allowed'  => 0,
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
				if ( ! get_field( '_wpd_installs', $id ) ) {
					continue;
				}

				$installs = (int) get_field( '_wpd_installs', $id );
				$max_size = get_field( '_wpd_max_size', $id );
				$staging  = get_field( '_wpd_staging_installs', $id );

				if ( ! $staging ) {
					$staging = 0;
				}

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
				$data['resources']['name']                  = $item_data['name'];
				$data['resources']['staging_max_allowed']  += $staging * $quantity;

			}
		}

		if ( ! empty( $data['plans'] ) ) {
			set_transient( $transient, $data, 30 );
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

		$subscription = $this->get_customer_subscriptions( self::SUB_STATUS_ACTIVE );

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

		if ( current_user_can( 'manage_options' ) ) {
			return false;
		}

		if ( ! $this->has_subscription() ) {
			return true;
		}

		$subscription = $this->get_customer_subscriptions( self::SUB_STATUS_ACTIVE );

		if ( ! is_array( $subscription ) || empty( $subscription ) ) {
			return true;
		}

		$total_sites = (int) dollie()->count_customer_containers( get_current_user_id() );

		return $total_sites >= $subscription['resources']['max_allowed_installs'];
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
	 * @return array|boolean
	 */
	public function get_blueprints_exception( $type = 'excluded' ) {

		$data          = [];
		$type         .= '_blueprints';
		$subscriptions = $this->get_customer_subscriptions( self::SUB_STATUS_ACTIVE );

		if ( empty( $subscriptions ) ) {
			return false;
		}

		foreach ( $subscriptions['plans']['products'] as $product ) {
			if ( isset( $product[ $type ] ) && ! empty( $product[ $type ] ) ) {
				foreach ( $product[ $type ] as $bp ) {
					$data[ $bp ] = $bp;
				}
			}
		}

		if ( empty( $data ) ) {
			return false;
		}

		return $data;
	}

	/**
	 * Check if user has staing
	 *
	 * @param null|int $user_id
	 *
	 * @return boolean
	 */
	public function has_staging( $user_id = null ) {
		if ( get_option( 'options_wpd_charge_for_deployments' ) !== '1' ) {
			return true;
		}

		if ( ! get_field( 'wpd_enable_staging', 'options' ) ) {
			return false;
		}

		if ( is_super_admin() ) {
			return true;
		}

		if ( null === $user_id ) {
			$user_id = get_current_user_id();
		}

		$subscriptions = $this->get_customer_subscriptions( self::SUB_STATUS_ACTIVE, $user_id );

		// If no subscription is active.
		if ( empty( $subscriptions ) ) {
			return false;
		}

		// Apply overrides at product level.
		if ( isset( $subscriptions['resources']['staging_max_allowed'] ) ) {
			return $subscriptions['resources']['staging_max_allowed'] > 0;
		}

		return false;
	}

	/**
	 * Check if site limit has been reached
	 *
	 * @return bool
	 */
	public function staging_sites_limit_reached( $user_id = null ) {

		if ( current_user_can( 'manage_options' ) ) {
			return false;
		}

		if ( ! class_exists( \WooCommerce::class ) || get_option( 'options_wpd_charge_for_deployments' ) !== '1' ) {
			return false;
		}

		if ( null === $user_id ) {
			$user_id = get_current_user_id();
		}

		$subscriptions = $this->get_customer_subscriptions( self::SUB_STATUS_ACTIVE, $user_id );

		if ( ! is_array( $subscriptions ) || empty( $subscriptions ) ) {
			return false;
		}

		$total_site = (int) dollie()->get_user( $user_id )->count_stagings();

		return ( $subscriptions['resources']['staging_max_allowed'] - $total_site ) <= 0;
	}

	/**
	 * Filter blueprints
	 *
	 * @param array $blueprints
	 *
	 * @return array
	 */
	public function filter_blueprints( $blueprints ) {
		if ( current_user_can( 'manage_options' ) ) {
			return $blueprints;
		}

		if ( ! empty( $blueprints ) ) {
			$included = $this->get_blueprints_exception( 'included' );

			if ( ! empty( $included ) ) {
				return array_intersect_key( $blueprints, $included );
			}

			$excluded = $this->get_blueprints_exception();

			if ( ! empty( $excluded ) ) {
				foreach ( $excluded as $bp_id ) {
					if ( isset( $blueprints[ $bp_id ] ) ) {
						unset( $blueprints[ $bp_id ] );
					}
				}
			}
		}

		return $blueprints;
	}

}
