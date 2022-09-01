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
		SUB_STATUS_ANY = 'any',
		SUB_STATUS_ACTIVE = 'active';

	/**
	 * WooCommerce constructor
	 */
	public function __construct() {

		add_action( 'init', [ $this, 'enable_automatic_payments' ] );
		add_action( 'woocommerce_thankyou', [ $this, 'redirect_to_blueprint' ] );
		add_action( 'after_setup_theme', [ $this, 'add_theme_support' ] );

		add_filter( 'dollie/required_plugins', [ $this, 'required_woocommerce' ] );


		add_filter( 'acf/prepare_field_group_for_import', [ $this, 'add_acf_fields' ] );


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
				wp_redirect( dollie()->page()->get_launch_site_url() . '?payment-status=success&blueprint_id=' . $_COOKIE[ DOLLIE_BLUEPRINTS_COOKIE ] );
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
				$data['resources']['name']                 = $item_data['name'];
				$data['resources']['staging_max_allowed']  += $staging * $quantity;

				$data = apply_filters( 'dollie/woo/subscription_product_data', $data, $customer_id, $id );
			}
		}

		if ( ! empty( $data['plans'] ) ) {
			set_transient( $transient, $data, 30 );
		}

		return apply_filters( 'dollie/woo/subscription_data', $data, $customer_id );
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
	 * Check if the size limit has been reached
	 *
	 * @return bool
	 */
	public function enable_automatic_payments() {
		if ( get_option( 'dollie_hosted_initial_setup' ) !== '1' ) {
			update_option( 'wc_subscription_duplicate_site', 'update' );
			update_option( 'wcs_ignore_duplicate_siteurl_notice', 1 );
		}
	}

	public function add_acf_fields( $field_group ) {
		$fields = [
			array(
				'key'           => 'field_5b0578b4639a6',
				'label'         => __( 'Link to Hosting Product', 'dollie' ),
				'name'          => 'wpd_installation_blueprint_hosting_product',
				'type'          => 'relationship',
				'instructions'  => __( 'By linking this blueprint directly to a hosting product you can enable one-click checkout + deployment for your new customers.', 'dollie' ),
				'required'      => 0,
				'wrapper'       => array(
					'width' => '',
					'class' => '',
					'id'    => '',
				),
				'hide_admin'    => 0,
				'post_type'     => array(
					0 => 'product',
				),
				'taxonomy'      => '',
				'filters'       => array(
					0 => 'search',
					1 => 'taxonomy',
				),
				'elements'      => array(
					0 => 'featured_image',
				),
				'min'           => '',
				'max'           => 1,
				'return_format' => 'id',
			),
		];

		return dollie()->add_acf_fields_to_group( $field_group, $fields, 'group_5affdcd76c8d1', 'wpd_installation_blueprint_description', 'after' );

	}

}
