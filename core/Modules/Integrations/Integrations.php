<?php

namespace Dollie\Core\Modules\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Api\PartnerApi;
use Dollie\Core\Modules\Integrations\IntegrationsInterface;
use Dollie\Core\Singleton;

/**
 * Class Access
 *
 * @package Dollie\Core\Modules\Access
 */
class Integrations extends Singleton implements IntegrationsInterface {


	private $module;

	/**
	 * Access contructor
	 */
	public function __construct() {
		parent::__construct();

		$integration = $this->get_integration();
		$class_name  = apply_filters( 'dollie/subscription/plugin_class', '\Dollie\Core\Modules\Integrations\\' . $integration, $integration );

		if ( ! class_exists( $class_name ) ) {
			$class_name = '\Dollie\Core\Modules\Integrations\WooCommerce';
		}

		$this->module = $class_name::instance();

		if ( ! $this->module instanceof IntegrationsInterface ) {
			throw new \Exception( 'Invalid subscription plugin' );
		}
	}

	/**
	 * Get the plugin used for subscriptions.
	 *
	 * @return false|mixed|string|null
	 */
	public function get_integration() {
		$integration = get_option( 'options_wpd_subscription_plugin' );
		if ( ! $integration ) {
			$integration = 'WooCommerce';
		}

		return $integration;
	}


	public function redirect_to_blueprint( $id ) {
		$this->module->redirect_to_blueprint( $id );
	}

	public function get_checkout_link( $args ) {
		return $this->module->get_checkout_link( $args );
	}

	public function get_customer_access( $customer_id = null ) {

		if ( ! $customer_id ) {
			$customer_id = get_current_user_id();
		}

		// Use the new function
		_deprecated_function( __METHOD__, '1.0', 'Dollie\Core\Modules\AccessGroups::get_customer_access()' );

		// Create a new instance of the AccessGroups class
		$access_groups = \Dollie\Core\Modules\AccessGroups\AccessGroups::instance();

		// Call the new function
		return $access_groups->get_customer_access( $customer_id );
	}

	public function has_bought_product( $user_id = null ) {
		return $this->module->has_bought_product( $user_id );
	}
}
