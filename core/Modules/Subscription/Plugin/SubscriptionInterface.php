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

	public function redirect_to_blueprint( $id );
	public function get_checkout_link( $args );
	public function get_customer_subscriptions( $status = null, $customer_id = null );
	public function has_bought_product( $user_id = 0 );
	public function has_subscription();
	public function sites_available();
	public function storage_available();
	public function subscription_name();
	public function site_limit_reached();
	public function size_limit_reached();
	public function get_blueprints_exception( $type = 'excluded' );
	public function has_staging( $user_id = null );
	public function staging_sites_limit_reached( $user_id = null );

}
