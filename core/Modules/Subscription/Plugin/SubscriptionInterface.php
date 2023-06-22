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
}
