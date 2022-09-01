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

		$subscription_plugin = get_option( 'options_wpd_subscription_plugin' );

		if ( ! $subscription_plugin ) {
			$subscription_plugin = 'WooCommerce';

			require_once DOLLIE_CORE_PATH . 'Modules/Subscription/Plugin/' . $subscription_plugin . '.php';
			$class_name = '\Dollie\Core\Modules\Subscription\Plugin\\' . $subscription_plugin;
		} else {
			$class_name = apply_filters( 'dollie/subscription/plugin_class', '\Dollie\Core\Modules\Subscription\Plugin\\' . $subscription_plugin, $subscription_plugin );
		}

		$this->module = $class_name::instance();

		if ( ! $this->module instanceof SubscriptionInterface ) {
			throw new \Exception( 'Invalid subscription plugin' );
		}

		add_action( 'acf/init', [ $this, 'load_acf' ] );


	}

	/**
	 * Load ACF
	 *
	 * @return void
	 */
	public function load_acf() {
		require DOLLIE_CORE_PATH . 'Modules/Subscription/Plugin/acf-fields/acf-fields.php';
	}

	public function redirect_to_blueprint( $id ) {
		$this->module->redirect_to_blueprint( $id );
	}

	public function get_checkout_link( $args ) {
		return $this->module->get_checkout_link( $args );
	}

	public function get_customer_subscriptions( $status = null, $customer_id = null ) {
		return $this->module->get_customer_subscriptions( $status, $customer_id );
	}

	public function has_bought_product( $user_id = null ) {
		return $this->module->has_bought_product( $user_id );
	}


	public function has_subscription() {
		return $this->module->has_subscription();
	}

	public function sites_available() {
		return $this->module->sites_available();
	}

	public function storage_available() {
		return $this->module->storage_available();
	}


	public function subscription_name() {
		return $this->module->subscription_name();
	}

	public function site_limit_reached() {
		return $this->module->site_limit_reached();
	}

	public function size_limit_reached() {
		return $this->module->size_limit_reached();
	}

	public function get_blueprints_exception( $type = 'excluded' ) {
		return $this->module->get_blueprints_exception( $type );
	}

	public function has_staging( $user_id = null ) {
		return $this->module->has_staging( $user_id );
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
		$usermeta_vip = get_field('_wpd_woo_launch_as_vip', 'user_'.$user_id);

		if ( $usermeta_vip ) {
			return true;
		}

		//Has subscription?
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

	public function staging_sites_limit_reached( $user_id = null ) {
		return $this->module->staging_sites_limit_reached( $user_id );
	}

	/**
	 * Get partner subscription
	 *
	 * @return array|bool
	 */
	public function get_partner_subscription() {
		if ( ! dollie()->auth()->is_connected() ) {
			return false;
		}

		$subscription = get_transient( 'wpd_partner_subscription' );

		if ( ! $subscription ) {
			$subscription = $this->get_subscription();
			set_transient( 'wpd_partner_subscription', $subscription, MINUTE_IN_SECONDS * 10 );
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
