<?php

namespace dollie\core\Modules\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Modules\AccessGroups\AccessGroups;
use Dollie\Core\Singleton;

/**
 * Class MemberPress
 *
 * @package Dollie\Core\Modules\Integrations
 */
class MemberPress extends Singleton implements IntegrationsInterface {

	protected $name = 'MemberPress';

	/**
	 * MemberPress constructor
	 */
	public function __construct() {

		add_action( 'template_redirect', [ $this, 'redirect_to_blueprint' ] );

		// Add user to group events.
		add_action( 'mepr-event-transaction-completed', [ $this, 'add_user_to_group_on_transaction' ] );
		add_action( 'mepr-event-subscription-resumed', [ $this, 'add_user_to_group_on_subscription' ] );
		add_action( 'mepr-event-subscription-created', [ $this, 'add_user_to_group_on_subscription' ] );

		// Remove user from group events.
		add_action( 'mepr-event-subscription-stopped', [ $this, 'remove_user_from_group_on_subscription' ] );
		add_action( 'mepr-event-subscription-expired', [ $this, 'remove_user_from_group_on_subscription' ] );
		add_action( 'mepr-event-subscription-paused', [ $this, 'remove_user_from_group_on_subscription' ] );

		add_action('mepr_subscription_transition_status', [$this, 'change_role_on_subscription_transition_status' ], 10, 3);

		add_filter( 'acf/fields/relationship/query/key=field_5e2c1adcc1543', array( $this, 'modify_query' ), 10, 3 );
		add_filter( 'acf/fields/relationship/query/key=field_5e2c1b94c1544', array( $this, 'modify_query' ), 10, 3 );

		// Add UI for MemberPress
		add_filter( 'dollie/required_plugins', array( $this, 'required_plugins' ) );
		add_filter( 'acf/prepare_field_group_for_import', array( $this, 'add_acf_fields' ) );
	}

	public function add_user_to_group_on_transaction( $event ) {

		/**
		 * @var \MeprTransaction $transaction
		 */
		$transaction = $event->get_data();

		/**
		 * @var \MeprSubscription $subscription
		 */
		$subscription = $transaction->subscription();

		$this->add_user_to_group( $subscription );
	}

	public function add_user_to_group_on_subscription( $event ) {

		/**
		 * @var \MeprSubscription $subscription
		 */
		$subscription = $event->get_data();

		$this->add_user_to_group( $subscription );

	}

	/**
	 * @param \MeprSubscription  $subscription
	 *
	 * @return void
	 */
	private function add_user_to_group( $subscription ) {

		if ( $subscription->status !== \MeprSubscription::$active_str ) {
			return;
		}

		$user_id    = $subscription->user_id;
		$product_id = $subscription->product_id;

		// Get the group ID from the ACF field on the product.
		$group_id_array = get_field( 'wpd_group_users', $product_id );

		// Check if group ID was found.
		if ( $group_id_array ) {

			// Get the first group ID.
			$group_id = $group_id_array[0];

			// Add user to the access group
			AccessGroups::instance()->add_to_access_group(
				$group_id,        // Group ID
				$user_id,        // User IDs
				$this->name,     // Source
				$this->name, // Log type
				'Added on purchase of product ' . get_the_title( $product_id ) . '.'
			);
		}
	}

	/**
	 * @param \MeprSubscription $subscription
	 *
	 * @return void
	 */
	public function remove_user_from_group( $subscription ) {

		$user_id    = $subscription->user_id;
		$product_id = $subscription->product_id;

		// Get the group ID from the ACF field on the product.
		$group_id_array = get_field( 'wpd_group_users', $product_id );

		// Check if group ID was found.
		if ( $group_id_array ) {

			// Get the first group ID.
			$group_id = $group_id_array[0];

			// Remove user from the access group
			AccessGroups::instance()->remove_from_access_group(
				$group_id,                // Group ID
				$user_id,        // User IDs
				$this->name,            // Source
				$this->name, // Log type
				'Removed on subscription stopped for product ' . get_the_title( $product_id ) . '.'
			);
		}
	}

	public function remove_user_from_group_on_subscription( $event ) {

		$subscription = $event->get_data();
		$this->remove_user_from_group( $subscription );
	}

	/**
	 * @param $old_status
	 * @param $new_status
	 * @param \MeprSubscription $sub
	 *
	 * @return void
	 */
	public function change_role_on_subscription_transition_status( $old_status, $new_status, $sub ) {
		if ($new_status === \MeprSubscription::$active_str ) {
			$this->add_user_to_group( $sub );
		} else {
			$this->remove_user_from_group( $sub );
		}
	}


	public function redirect_to_blueprint( $id ) {

		if ( ! get_field( 'wpd_override_thank_you_page', 'options' ) ) {
			return;
		}

		if ( ! isset( $_COOKIE[ DOLLIE_BLUEPRINTS_COOKIE ] ) || ! $_COOKIE[ DOLLIE_BLUEPRINTS_COOKIE ] ) {
			return;
		}

		$mepr_options = \MeprOptions::fetch();
		if ( ! isset( $mepr_options->thankyou_page_id ) || ! $mepr_options->thankyou_page_id ) {
			return;
		}

		$current_query = get_queried_object();
		if ( $current_query === null || ! get_queried_object()->ID ) {
			return;
		}

		if ( $current_query->ID === $mepr_options->thankyou_page_id ) {
			wp_redirect( dollie()->page()->get_launch_site_url() . '?payment-status=success&blueprint_id=' . $_COOKIE[ DOLLIE_BLUEPRINTS_COOKIE ] );
			exit;
		}
	}

	public function get_checkout_link( $args ) {

		if ( ! $args['product_id'] ) {
			return '';
		}

		$link = get_permalink( $args['product_id'] );
		if ( isset( $args['blueprint_id'] ) ) {
			$link = add_query_arg( [
				DOLLIE_BLUEPRINTS_COOKIE_PARAM => $args['blueprint_id']
			], $link );
		}

		return apply_filters( 'dollie/memberpress/checkout_link', $link, $args );
	}

	/**
	 * Require plugins
	 *
	 * @param array $plugins
	 *
	 * @return array
	 */
	public function required_plugins( $plugins ) {
		$plugins[] = array(
			'name'             => 'MemberPress',
			'slug'             => 'memberpress',
			'required'         => true,
			'version'          => '',
			'force_activation' => false,
		);

		return $plugins;
	}

	public function add_acf_fields( $field_group ) {

		$fields = [
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
					0 => 'memberpressproduct',
				),
				'elements'      => array(
					0 => 'featured_image',
				),
				'min'           => '',
				'max'           => 1,
				'return_format' => 'id',
			),
		];

		return dollie()->add_acf_fields_to_group( $field_group, $fields, 'group_5affdcd76c8d1', 'wpd_installation_blueprint_description', 'after' );

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

}
