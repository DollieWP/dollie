<?php

namespace Dollie\Core\Modules\Subscription;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Api\PartnerApi;
use Dollie\Core\Modules\Subscription\Plugin\SubscriptionInterface;
use Dollie\Core\Singleton;

/**
 * Class Subscription
 *
 * @package Dollie\Core\Modules\Subscription
 */
class Subscription extends Singleton implements SubscriptionInterface {
	use PartnerApi;

	private $module;

	/**
	 * Subscription contructor
	 */
	public function __construct() {
		parent::__construct();

		$subscription_plugin = $this->get_subscription_plugin();

		if ( $subscription_plugin === 'Woocommerce' ) {
			require_once DOLLIE_CORE_PATH . 'Modules/Subscription/Plugin/Woocommerce.php';
			$class_name = '\Dollie\Core\Modules\Subscription\Plugin\\Woocommerce';
		} else {
			$class_name = apply_filters( 'dollie/subscription/plugin_class', '\Dollie\Core\Modules\Subscription\Plugin\\' . $subscription_plugin, $subscription_plugin );
		}

		$this->module = $class_name::instance();

		if ( ! $this->module instanceof SubscriptionInterface ) {
			throw new \Exception( 'Invalid subscription plugin' );
		}

		add_filter( 'dollie/blueprints', array( $this, 'filter_blueprints' ) );
	}

	/**
	 * Get the plugin used for subscriptions.
	 *
	 * @return false|mixed|string|null
	 */
	public function get_subscription_plugin() {
		$subscription_plugin = get_option( 'options_wpd_subscription_plugin' );
		if ( ! $subscription_plugin ) {
			$subscription_plugin = 'WooCommerce';
		}

		return $subscription_plugin;
	}



	public function redirect_to_blueprint( $id ) {
		$this->module->redirect_to_blueprint( $id );
	}

	public function get_checkout_link( $args ) {
		return $this->module->get_checkout_link( $args );
	}

	public function get_customer_subscriptions( $status = null, $customer_id = null ) {

		// Use the new function
		_deprecated_function( __METHOD__, '1.0', 'Dollie\Core\Modules\AccessGroups::get_customer_access_details()' );

		// Create a new instance of the AccessGroups class
		$access_groups = \Dollie\Core\Modules\AccessGroups\AccessGroups::instance();

		// Call the new function
		return $access_groups->get_customer_access_details( $status, $customer_id );
	}

	public function has_bought_product( $user_id = null ) {
		return $this->module->has_bought_product( $user_id );
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

		$subscription = $this->get_customer_subscriptions( $this->module::SUB_STATUS_ACTIVE );

		return $subscription ? (bool) $subscription['plans'] : $subscription;
	}

	/**
	 * Get how many sites are left available for customer
	 *
	 * @return int|mixed
	 */
	public function sites_available( $customer_id = null ) {

		if ( ! $customer_id ) {
			$customer_id = get_current_user_id();
		}

		// Overwritten by Custom User Value
		$is_custom = get_field( '_wpd_installs', 'user_' . $customer_id );

		if ( ! empty( $is_custom ) && is_numeric( $is_custom ) && $is_custom > 0 ) {
			return $is_custom - dollie()->get_user()->count_containers();

		}

		$subscription = $this->get_customer_subscriptions( $this->module::SUB_STATUS_ACTIVE );

		if ( ! $subscription ) {
			return 0;
		}

		return $subscription['resources']['max_allowed_installs'] - dollie()->get_user()->count_containers();
	}

	/**
	 * Get storage available for customer
	 *
	 * @return int|mixed
	 */
	public function storage_available( $customer_id = null ) {

		if ( ! $customer_id ) {
			$customer_id = get_current_user_id();
		}

		$is_custom = get_field( '_wpd_max_size', 'user_' . $customer_id );

		if ( ! empty( $is_custom ) && is_numeric( $is_custom ) && $is_custom > 0 ) {
			return $is_custom;
		}

		$subscription = $this->get_customer_subscriptions( $this->module::SUB_STATUS_ACTIVE );

		if ( ! $subscription ) {
			return 0;
		}

		return $subscription['resources']['max_allowed_size'];
	}

	/**
	 * Get has VIP subscription enabled for customer
	 *
	 * @return bool
	 */
	public function vip_status( $user_id = null ) {

		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( get_field( '_wpd_woo_launch_as_vip', 'user_' . $user_id ) ) {
			return get_field( '_wpd_woo_launch_as_vip', 'user_' . $user_id );
		}

		$subscription = $this->get_customer_subscriptions( $this->module::SUB_STATUS_ACTIVE );

		if ( ! $subscription ) {
			return 0;
		}

		return $subscription['resources']['launch_as_vip'];
	}

	/**
	 * Get subscription name
	 *
	 * @return mixed|string
	 */
	public function subscription_name() {
		$subscription = $this->get_customer_subscriptions( $this->module::SUB_STATUS_ACTIVE );

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
	public function site_limit_reached( $customer_id = null ) {
		if ( ! class_exists( \WooCommerce::class ) || get_option( 'options_wpd_charge_for_deployments' ) !== '1' ) {
			return false;
		}

		$user = dollie()->get_user();

		if ( $user->can_manage_all_sites() ) {
			return false;
		}

		if ( ! $customer_id ) {
			$customer_id = get_current_user_id();
		}

		$is_custom = get_field( '_wpd_installs', 'user_' . $customer_id );

		if ( ! empty( $is_custom ) && is_numeric( $is_custom ) && $is_custom > 0 ) {
			return dollie()->get_user()->count_containers() >= $is_custom;
		}

		if ( ! $this->has_subscription() ) {
			return true;
		}

		$subscription = $this->get_customer_subscriptions( $this->module::SUB_STATUS_ACTIVE );

		if ( ! is_array( $subscription ) || empty( $subscription ) ) {
			return true;
		}

		return dollie()->get_user()->count_containers() >= $subscription['resources']['max_allowed_installs'];
	}

	/**
	 * Check if the size limit has been reached
	 *
	 * @return bool
	 */
	public function size_limit_reached( $customer_id = null ) {
		if ( ! class_exists( \WooCommerce::class ) || get_option( 'options_wpd_charge_for_deployments' ) !== '1' ) {
			return false;
		}

		if ( ! $customer_id ) {
			$customer_id = get_current_user_id();
		}

		$user = dollie()->get_user();

		$is_custom = get_field( '_wpd_max_size', 'user_' . $customer_id );

		if ( ! empty( $is_custom ) && is_numeric( $is_custom ) && $is_custom > 0 ) {
			$allowed_size  = $is_custom;
			$total_size    = dollie()->insights()->get_total_container_size();
			$allowed_size *= 1024 * 1024 * 1024;

			return $total_size >= $allowed_size && ! $user->can_manage_all_sites();

		}

		$subscription = $this->get_customer_subscriptions( $this->module::SUB_STATUS_ACTIVE );

		if ( ! $subscription ) {
			return false;
		}

		$total_size   = dollie()->insights()->get_total_container_size();
		$allowed_size = $subscription['resources']['max_allowed_size'] * 1024 * 1024 * 1024;

		return $this->has_subscription() && $total_size >= $allowed_size && ! $user->can_manage_all_sites();
	}

	/**
	 * Get excluded blueprints
	 *
	 * @return array|boolean
	 */
	public function get_blueprints_exception( $type = 'excluded' ) {
		$data          = array();
		$type         .= '_blueprints';
		$subscriptions = $this->get_customer_subscriptions( $this->module::SUB_STATUS_ACTIVE );

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
	 * Check if user has staging
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

		$subscriptions = $this->get_customer_subscriptions( $this->module::SUB_STATUS_ACTIVE, $user_id );

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
	 * Filter blueprints
	 *
	 * @param array $blueprints
	 *
	 * @return array
	 */
	public function filter_blueprints( $blueprints ) {

		$user = dollie()->get_user();

		if ( $user->can_manage_all_sites() ) {
			return $blueprints;
		}

		if ( empty( $blueprints ) ) {
			return $blueprints;
		}

		$customer_id  = get_current_user_id();
		$sub_included = $this->get_blueprints_exception( 'included' );

		// Has Blueprint includes in User meta?
		if ( get_field( '_wpd_included_blueprints', 'user_' . $customer_id ) ) {
			$user_included_blueprints = get_field( '_wpd_included_blueprints', 'user_' . $customer_id );

			// Check if arrays should be merged.
			if ( ! empty( $sub_included ) ) {
				$included = array_merge( $sub_included, $user_included_blueprints );
			} else {
				$excluded = $user_included_blueprints;
			}
		} else {
			$included = $sub_included;
		}

		if ( ! empty( $included ) ) {
			return array_intersect_key( $blueprints, $included );
		}

		// Has Blueprint exclusions in sub?
		$sub_excluded = $this->get_blueprints_exception();

		// Has Blueprint excludes in User meta?
		if ( get_field( '_wpd_excluded_blueprints', 'user_' . $customer_id ) ) {
			$user_excluded_blueprints = get_field( '_wpd_excluded_blueprints', 'user_' . $customer_id );

			// Check if arrays should be merged.
			if ( ! empty( $sub_excluded ) ) {
				$excluded = array_merge( $sub_excluded, $user_excluded_blueprints );
			} else {
				$excluded = $user_excluded_blueprints;
			}
		} else {
			$excluded = $sub_excluded;
		}

		// Filter blueprints.
		if ( ! empty( $excluded ) ) {
			foreach ( $excluded as $bp_id ) {
				if ( isset( $blueprints[ $bp_id ] ) ) {
					unset( $blueprints[ $bp_id ] );
				}
			}
		}

		return $blueprints;
	}

	/**
	 * Check if site limit has been reached
	 *
	 * @return bool
	 */
	public function staging_sites_limit_reached( $user_id = null ) {

		$user = dollie()->get_user();

		if ( $user->can_manage_all_sites() ) {
			return false;
		}

		if ( get_option( 'options_wpd_charge_for_deployments' ) !== '1' ) {
			return false;
		}

		$user = dollie()->get_user( $user_id );

		if ( $user->can_manage_options() ) {
			return false;
		}

		$subscriptions = $this->get_customer_subscriptions( $this->module::SUB_STATUS_ACTIVE, $user->get_id() );

		if ( ! is_array( $subscriptions ) || empty( $subscriptions ) ) {
			return false;
		}

		return ( $subscriptions['resources']['staging_max_allowed'] - (int) $user->count_stagings() ) <= 0;
	}

	/**
	 * Check if user has vip status
	 *
	 * @param null|int $user_id
	 *
	 * @return boolean
	 */
	public function has_vip( $user_id = null ) {

		if ( ! get_field( 'wpd_enable_vip_sites', 'options' ) ) {
			return false;
		}

		if ( is_super_admin() ) {
			return true;
		}

		if ( null === $user_id ) {
			$user_id = get_current_user_id();
		}

		// Has VIP via User meta overwrite?
		$usermeta_vip = get_field( '_wpd_woo_launch_as_vip', 'user_' . $user_id );

		if ( $usermeta_vip ) {
			return true;
		}

		// Has subscription?
		$subscriptions = $this->get_customer_subscriptions( null, $user_id );

		// If no subscription is active or no subscription is found.
		if ( empty( $subscriptions ) ) {
			return false;
		}

		// Has subscription but is VIP enabled for this subcription?
		if ( isset( $subscriptions['resources']['launch_as_vip'] ) ) {
			return true;
		}

		return false;
	}


	/**
	 * Get partner subscription
	 *
	 * @return array|bool
	 */
	public function get_partner_subscription() {

		$subscription = get_transient( 'wpd_partner_subscription' );

		if ( ! $subscription ) {
			$subscription = $this->get_subscription();
			if ( ! is_wp_error( $subscription ) ) {

				// mark as connected successfully.
				update_option( 'wpd_connected', 1 );

				set_transient( 'wpd_partner_subscription', $subscription, MINUTE_IN_SECONDS * 10 );
			} else {

				// mark as not connected.
				delete_option( 'wpd_connected' );

				return false;
			}
		}

		return $subscription;
	}

	/**
	 * Check if partner has subscription
	 *
	 * @return boolean
	 */
	public function has_partner_subscription() {
		$subscription = $this->get_partner_subscription();

		if ( is_wp_error( $subscription ) || empty( $subscription ) ) {
			return false;
		}

		return false === $subscription ? $subscription : $subscription['status'];
	}

	/**
	 * Check if partner has verified account
	 *
	 * @return boolean
	 */
	public function has_partner_verified() {
		$subscription = $this->get_partner_subscription();

		if ( is_wp_error( $subscription ) || empty( $subscription ) ) {
			return false;
		}

		if ( ! isset( $subscription['verified'] ) ) {
			return false;
		}

		return $subscription['verified'];
	}

	/**
	 * Check if partner has credits
	 *
	 * @return boolean
	 */
	public function has_partner_credits() {
		return apply_filters( 'dollie/subscription/has_credits', true );
	}

	/**
	 * Get how many containers can partner deploy
	 *
	 * @return int
	 */
	public function get_partner_deploy_limit() {
		$subscription = $this->get_partner_subscription();

		if ( is_wp_error( $subscription ) || false === $subscription || empty( $subscription ) ) {
			return 0;
		}

		return $subscription['limit'];
	}
}
