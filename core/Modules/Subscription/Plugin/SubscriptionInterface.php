<?php

namespace Dollie\Core\Modules\Subscription\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WooCommerce
 *
 * @package Dollie\Core\Modules\Subscription\Plugin
 */
interface SubscriptionInterface {

	public function redirect_to_blueprint( $id);
	public function get_checkout_link( $product_id, $blueprint_id);
	public function get_customer_subscriptions( $status = null, $customer_id = null);
	public function has_bought_product( $user_id = 0);
	public function has_subscription();
	public function sites_available();
	public function storage_available();
	public function subscription_name();
	public function site_limit_reached();
	public function size_limit_reached();
	public function get_excluded_blueprints();
	public function get_included_blueprints();
	public function has_staging();
	public function staging_sites_limit_reached();

}
