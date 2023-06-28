<?php

namespace Dollie\Core\Modules\Access\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Modules\Access\Plugin\AccessInterface; // Ensure this path is correct.

/**
 * Class WooCommerce
 *
 * @package Dollie\Core\Modules\Access\Plugin
 */
class WooCommerce extends Singleton implements AccessInterface {

	protected $name = 'WooCommerce';

	/**
	 * WooCommerce constructor
	 */
	public function __construct() {

		$this->name = 'WooCommerce';

		add_action( 'init', array( $this, 'enable_automatic_payments' ) );
		add_action( 'woocommerce_thankyou', array( $this, 'redirect_to_blueprint' ) );

		add_action( 'after_setup_theme', array( $this, 'add_theme_support' ) );
		add_filter( 'acf/fields/relationship/query/key=field_5e2c1adcc1543', array( $this, 'modify_query' ), 10, 3 );
		add_filter( 'acf/fields/relationship/query/key=field_5e2c1b94c1544', array( $this, 'modify_query' ), 10, 3 );

		// ACF hooks for variable products
		add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'render_acf_fields_for_variations' ), 10, 3 );
		add_action( 'woocommerce_save_product_variation', array( $this, 'save_acf_fields_for_variations' ), 10, 2 );
		add_filter( 'acf/location/rule_values/post_type', array( $this, 'my_acf_location_rule_values_post_type' ) );
		add_filter( 'acf/location/rule_match/post_type', array( $this, 'my_acf_location_rule_match_post_type' ), 10, 4 );
		add_action( 'acf/input/admin_footer', array( $this, 'my_acf_input_admin_footer' ) );

		// Add/Removed user to group when subscription
		if ( class_exists( 'WC_Subscriptions' ) ) {
			add_action( 'woocommerce_subscription_status_active', array( $this, 'add_user_to_group' ), 10, 1 );
			add_action( 'woocommerce_subscription_status_cancelled', array( $this, 'remove_user_from_group' ), 10, 1 );
			add_action( 'woocommerce_subscription_status_pending-cancel', array( $this, 'remove_user_from_group' ), 10, 1 );
		}
		// This is for regular WooCommerce orders
		add_action( 'woocommerce_order_status_completed', array( $this, 'add_user_to_group' ), 10, 1 );
		add_action( 'woocommerce_order_status_refunded', array( $this, 'remove_user_from_group' ), 10, 1 );

		// Add UI for WooCommerce
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
			'name'             => 'WooCommerce Accesss',
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
	 * @param null|int $customer_id
	 *
	 * @return array|bool
	 */
	public function get_customer_subscriptions( $customer_id = null ) {
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

	public function add_user_to_group( $order_id ) {
		// Get the order object
		$order = wc_get_order( $order_id );

		// Get the user ID from the order
		$user_id = $order->get_user_id();

		// Loop through order items
		foreach ( $order->get_items() as $item_id => $item ) {
			// Check if it's a variation
			if ( $item->get_variation_id() ) {
				$product_id   = $item->get_variation_id();
				$product_type = 'variation';
			} else {
				$product_id   = $item->get_product_id();
				$product_type = 'product';
			}

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
					$this->name,            // Source
					$this->name, // Log type
					'Added on purchase of ' . $product_type . ' ' . get_the_title( $product_id ) . '.'
				);
			}
		}
	}

	public function remove_user_from_group( $subscription ) {

		$user_id = $subscription->get_user_id();
		foreach ( $subscription->get_items() as $item_id => $item ) {
			// Check if it's a variation
			if ( $item->get_variation_id() ) {
				$product_id   = $item->get_variation_id();
				$product_type = 'product variation';
			} else {
				$product_id   = $item->get_product_id();
				$product_type = 'product';
			}

			// Get the group ID from the ACF field on the product
			$group_id_array = get_field( 'wpd_group_users', $product_id );

			// Check if group ID was found
			if ( $group_id_array ) {
				// Get the first group ID
				$group_id = $group_id_array[0];

				// Get instance of Hooks class
				$hooks = \Dollie\Core\Modules\AccessGroups\AccessGroups::instance();

				// Remove user from the access group
				$hooks->remove_from_access_group(
					$group_id,                // Group ID
					$user_id,        // User IDs
					$this->name,            // Source
					$this->name, // Log type
					'Removed on subscription cancel for ' . $product_type . ' ' . get_the_title( $product_id ) . '.'
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

	public function render_acf_fields_for_variations( $loop, $variation_data, $variation ) {
		global $acf_variation;
		$acf_variation = $loop;
		add_filter( 'acf/prepare_field', array( $this, 'acf_prepare_field_update_field_name' ) );

		$acf_field_groups = acf_get_field_groups();
		foreach ( $acf_field_groups as $acf_field_group ) {
			foreach ( $acf_field_group['location'] as $group_locations ) {
				foreach ( $group_locations as $rule ) {
					if ( $rule['param'] == 'post_type' && $rule['operator'] == '==' && $rule['value'] == 'product_variation' ) {
						acf_render_fields( $variation->ID, acf_get_fields( $acf_field_group ) );
						break 2;
					}
				}
			}
		}

		remove_filter( 'acf/prepare_field', array( $this, 'acf_prepare_field_update_field_name' ) );
	}

	public function acf_prepare_field_update_field_name( $field ) {
		global $acf_variation;
		$field['name'] = preg_replace( '/^acf\[/', "acf[$acf_variation][", $field['name'] );
		return $field;
	}

	public function save_acf_fields_for_variations( $variation_id, $i = -1 ) {
		if ( ! empty( $_POST['acf'] ) && is_array( $_POST['acf'] ) && array_key_exists( $i, $_POST['acf'] ) && is_array( ( $fields = $_POST['acf'][ $i ] ) ) ) {
			$unique_updates = array();
			foreach ( $fields as $key => $val ) {
				if ( strpos( $key, 'field_' ) === false ) {
					foreach ( $val as $repeater_key => $repeater_val ) {
						if ( ! array_key_exists( $repeater_key, $unique_updates ) || ! empty( $repeater_val ) ) {
							$unique_updates[ $repeater_key ] = $repeater_val;
						}
					}
				} elseif ( ! array_key_exists( $key, $unique_updates ) || ! empty( $val ) ) {
						$unique_updates[ $key ] = $val;
				}
			}

			foreach ( $unique_updates as $key => $val ) {
				update_field( $key, $val, $variation_id );
			}
		}
	}

	public function my_acf_location_rule_values_post_type( $choices ) {
		$keys  = array_keys( $choices );
		$index = array_search( 'product', $keys );

		$position = $index === false ? count( $choices ) : $index + 1;

		$choices = array_merge(
			array_slice( $choices, 0, $position ),
			array( 'product_variation' => __( 'Product Variation', 'auf' ) ),
			array_slice( $choices, $position )
		);

		return $choices;
	}

	public function my_acf_location_rule_match_post_type( $match, $rule, $options, $field_group ) {
		if ( $rule['value'] == 'product_variation' && isset( $options['post_type'] ) ) {
			$post_type = $options['post_type'];

			if ( $rule['operator'] == '==' ) {
				$match = $post_type == $rule['value'];
			} elseif ( $rule['operator'] == '!=' ) {
				$match = $post_type != $rule['value'];
			}
		}

		return $match;
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
		$args['meta_query'][] = array(
			'relation' => 'AND',
			array(
				'key'   => 'dollie_container_type',
				'value' => '1',
			),
			array(
				'key'   => 'wpd_blueprint_created',
				'value' => 'yes',
			),
			array(
				'key'     => 'wpd_installation_blueprint_title',
				'compare' => 'EXISTS',
			),
		);

		return $args;
	}

	public function my_acf_input_admin_footer() {
		?>
<script type="text/javascript">
	(function($) {
	$(document).on('woocommerce_variations_loaded', function () {
		acf.do_action('append', $('#post'));
	})
	})(jQuery);
</script>
		<?php
	}
}
