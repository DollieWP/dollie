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

		add_action( 'init', array( $this, 'enable_automatic_payments' ) );
		add_action( 'woocommerce_thankyou', array( $this, 'redirect_to_blueprint' ) );
		add_action( 'woocommerce_order_status_completed', array( $this, 'add_user_to_group_on_purchase' ), 10, 1 );
		add_action( 'woocommerce_subscription_status_active', array( $this, 'add_user_to_group_on_purchase' ), 10, 1 );
		add_action( 'woocommerce_subscription_status_cancelled', array( $this, 'remove_user_from_group_on_subscription_cancel' ), 10, 1 );
		add_action( 'woocommerce_subscription_status_pending-cancel', array( $this, 'remove_user_from_group_on_subscription_cancel' ), 10, 1 );

		add_action( 'after_setup_theme', array( $this, 'add_theme_support' ) );

		add_filter( 'dollie/required_plugins', array( $this, 'required_woocommerce' ) );

		add_filter( 'acf/prepare_field_group_for_import', array( $this, 'add_acf_fields' ) );
	}

	/**
	 * Require Woocommerce plugins
	 *
	 * @param array $plugins
	 *
	 * @return array
	 */
	public function required_woocommerce( $plugins ) {
		$plugins[] = array(
			'name'             => 'WooCommerce',
			'slug'             => 'woocommerce',
			'required'         => true,
			'version'          => '',
			'force_activation' => false,
		);

		$plugins[] = array(
			'name'             => 'WooCommerce Subscriptions',
			'slug'             => 'woocommerce-subscriptions',
			'required'         => true,
			'version'          => '3.0.10',
			'force_activation' => false,
			'source'           => 'https://manager.getdollie.com/releases/?action=download&slug=woocommerce-subscriptions',
		);

		return $plugins;
	}

	/**
	 * Redirect to payment success + blueprint if blueprint cookie is set
	 *
	 * @param $order_id
	 */
	public function redirect_to_blueprint( $order_id ) {

		// general setting.
		if ( ! get_field( 'wpd_override_thank_you_page', 'options' ) ) {
			return;
		}

		if ( ! isset( $_COOKIE[ DOLLIE_BLUEPRINTS_COOKIE ] ) || ! $_COOKIE[ DOLLIE_BLUEPRINTS_COOKIE ] ) {
			return;
		}

		$order = new \WC_Order( $order_id );
		if ( 'failed' !== $order->status ) {
			wp_redirect( dollie()->page()->get_launch_site_url() . '?payment-status=success&blueprint_id=' . $_COOKIE[ DOLLIE_BLUEPRINTS_COOKIE ] );
			exit;
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

		$link_args = array(
			'add-to-cart'  => $args['product_id'],
			'blueprint_id' => $args['blueprint_id'],
		);

		if ( method_exists( $product_obj, 'get_type' ) && $product_obj->get_type() === 'variable-subscription' ) {
			$default_atts = $product_obj->get_default_attributes();

			if ( isset( $default_atts['pa_subscription'] ) ) {
				$data_store                = \WC_Data_Store::load( 'product' );
				$default_variation         = $data_store->find_matching_product_variation( $product_obj, array( 'attribute_pa_subscription' => $default_atts['pa_subscription'] ) );
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
			return true;
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
	 * Woocommerce subscriptions update payments staging
	 *
	 * @return bool
	 */
	public function enable_automatic_payments() {
		if ( get_option( 'dollie_hosted_initial_setup' ) !== '1' ) {
			update_option( 'wc_subscription_duplicate_site', 'update' );
			update_option( 'wcs_ignore_duplicate_siteurl_notice', 1 );
		}
	}

	public function add_user_to_group_on_purchase( $order_id ) {
		// Get the order object
		$order = wc_get_order( $order_id );

		// Get the user ID from the order
		$user_id = $order->get_user_id();

		// Loop through order items
		foreach ( $order->get_items() as $item_id => $item ) {
			// Get the product ID
			$product_id = $item->get_product_id();

			// Get the group ID from the ACF field on the product
			$group_id_array = get_field( 'wpd_group_users', $product_id );

			// Check if group ID was found
			if ( $group_id_array ) {
				// Get the first group ID
				$group_id = $group_id_array[0];

				// Get instance of Hooks class
				$access = \Dollie\Core\Modules\AccessGroups\AccessGroups::instance();

				// Add user to the access group
				$access->add_to_access_group(
					$group_id,                // Group ID
					$user_id,        // User IDs
					'WooCommerce',            // Source
					'WooCommerce', // Log type
					'User added to group on purchase of product ' . get_the_title( $product_id ) . '.'
				);
			}
		}
	}



	public function remove_user_from_group_on_subscription_cancel( $subscription ) {

		$user_id = $subscription->get_user_id();
		foreach ( $subscription->get_items() as $item_id => $item ) {
			// Get the product ID
			$product_id = $item->get_product_id();

			// Get the group ID from the ACF field on the product
			$group_id_array = get_field( 'wpd_group_users', $product_id );

			// Check if group ID was found
			if ( $group_id_array ) {
				// Get the first group ID
				$group_id = $group_id_array[0];

				// Get instance of Hooks class
				$hooks = \Dollie\Core\Modules\AccessGroups\AccessGroups::instance();

				// Add user to the access group
				$hooks->remove_from_access_group(
					$group_id,                // Group ID
					$user_id,        // User IDs
					'WooCommerce',            // Source
					'WooCommerce', // Log type
					'User removed from group on subscription cancel for ' . get_the_title( $product_id ) . '.'
				);
			}
		}
	}




	public function add_acf_fields( $field_group ) {
		$fields = array(
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
		);

		return dollie()->add_acf_fields_to_group( $field_group, $fields, 'group_5affdcd76c8d1', 'wpd_installation_blueprint_description', 'after' );
	}
}
