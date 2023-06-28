<?php

namespace Dollie\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Api\PartnerApi;
use Dollie\Core\Singleton;

/**
 * Class Partner Subscription
 *
 * @package Dollie\Core\Modules\Access
 */
class Partner extends Singleton {
	use PartnerApi;



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
