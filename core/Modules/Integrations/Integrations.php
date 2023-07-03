<?php

namespace Dollie\Core\Modules\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Modules\AccessGroups\AccessGroups;
use Dollie\Core\Singleton;

/**
 * Class Integrations
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

		// Integrations
		if ( class_exists( 'WooCommerce' ) ) {
			WooCommerce::instance();
		}
		if ( defined( 'PMPRO_VERSION' ) ) {
			PaidMembershipsPro::instance();
		}
		if ( defined( 'MEPR_VERSION' ) ) {
			MemberPress::instance();
		}
		if (function_exists( 'EDD' )) {
			EasyDigitalDownloads::instance();
		}

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

		return AccessGroups::instance()->get_customer_access( $customer_id );
	}

	public function has_bought_product( $user_id = null ) {
		return $this->module->has_bought_product( $user_id );
	}
}
