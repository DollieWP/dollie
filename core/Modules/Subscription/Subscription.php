<?php

namespace Dollie\Core\Modules\Subscription;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Modules\Subscription\Plugin\SubscriptionInterface;
use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;

/**
 * Class Subscription
 *
 * @package Dollie\Core\Modules\Subscription
 */
class Subscription extends Singleton implements SubscriptionInterface {

	/**
	 * Undocumented variable
	 *
	 * @var [type]
	 */
	private $module;

	/**
	 * Subscription contructor
	 */
	public function __construct() {
		parent::__construct();

		$subscription_plugin = get_option( 'options_wpd_subscription_plugin' );

		if ( ! $subscription_plugin ) {
			$subscription_plugin = 'WooCommerce';
		}

		$class_path = apply_filters( 'dollie/subscription/plugin_path', DOLLIE_CORE_PATH . 'Modules/Subscription/Plugin/' . $subscription_plugin . '.php', $subscription_plugin );
		$class_name = apply_filters( 'dollie/subscription/plugin_class', '\Dollie\Core\Modules\Subscription\Plugin\\' . $subscription_plugin, $subscription_plugin );

		require_once $class_path;
		$this->module = new $class_name();

		if ( ! $this->module instanceof SubscriptionInterface ) {
			throw new \Exception( 'Invalid subscription plugin' );
		}

	}

	public function redirect_to_blueprint( $id ) {
		$this->module->redirect_to_blueprint( $id );
	}

	public function get_checkout_link( $product_id, $blueprint_id ) {
		return $this->module->get_checkout_link( $product_id, $blueprint_id );
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

	public function get_excluded_blueprints() {
		return $this->module->get_excluded_blueprints();
	}

	public function get_included_blueprints() {
		return $this->module->get_included_blueprints();
	}

	public function has_staging() {
		return $this->module->has_staging();
	}

	public function staging_sites_limit_reached() {
		return $this->module->staging_sites_limit_reached();
	}

	/**
	 * Get partner subscription
	 *
	 * @return array|bool
	 */
	public function get_partner_subscription() {
		if ( ! Api::get_auth_token() || get_transient( 'wpd_just_connected' ) ) {
			return false;
		}

		$subscription = get_transient( 'wpd_partner_subscription' );

		if ( ! $subscription ) {
			$check_request  = Api::get( Api::ROUTE_CHECK_SUBSCRIPTION );
			$check_response = Api::process_response( $check_request, null );

			if ( ! $check_response ) {
				return false;
			}

			$subscription = $check_response['data'];

			set_transient( 'wpd_partner_subscription', $subscription, HOUR_IN_SECONDS * 6 );
		}

		return $subscription;
	}

	/**
	 * Check if partner subscription
	 *
	 * @return boolean
	 */
	public function has_partner_subscription() {
		$subscription = $this->get_partner_subscription();

		if ( ! $subscription ) {
			return false;
		}

		return $subscription['active'];
	}

	/**
	 * Check if partner subscription is trial
	 *
	 * @return boolean
	 */
	public function is_partner_subscription_trial() {
		$subscription = $this->get_partner_subscription();

		if ( ! $subscription ) {
			return false;
		}

		return $subscription['subscription']['trial'];
	}

	/**
	 * Get how many containers can partner deploy
	 *
	 * @return int
	 */
	public function get_partner_subscription_credits() {
		$subscription = $this->get_partner_subscription();

		if ( ! $subscription ) {
			return 0;
		}

		return $subscription['subscription']['limit'];
	}

}
