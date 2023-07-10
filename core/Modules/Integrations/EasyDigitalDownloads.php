<?php

namespace dollie\core\Modules\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Modules\AccessGroups\AccessGroups;
use Dollie\Core\Singleton;

/**
 * Class EasyDigitalDownloads
 *
 * @package Dollie\Core\Modules\Integrations
 */
class EasyDigitalDownloads extends Singleton implements IntegrationsInterface {

	protected $name = 'EasyDigitalDownloads';

	/**
	 * MemberPress constructor
	 */
	public function __construct() {

		add_action( 'template_redirect', [ $this, 'redirect_to_blueprint' ] );

		add_action( 'edd_update_payment_status', [ $this, 'on_payment_status_change' ], 10, 3 );

		add_filter( 'acf/fields/relationship/query/key=field_5e2c1adcc1543', array( $this, 'modify_query' ), 10, 3 );
		add_filter( 'acf/fields/relationship/query/key=field_5e2c1b94c1544', array( $this, 'modify_query' ), 10, 3 );

		// Add UI for MemberPress
		add_filter( 'dollie/required_plugins', array( $this, 'required_plugins' ) );
		add_filter( 'acf/prepare_field_group_for_import', array( $this, 'add_acf_fields' ) );
	}
	public function on_payment_status_change( $payment_id, $new_status, $old_status ) {
		if ( $new_status === $old_status ) {
			return;
		}

		// Basic payment meta.
		$payment_meta = edd_get_payment_meta( $payment_id );

		// Cart details
		$cart_items = edd_get_payment_meta_cart_details( $payment_id );
		$user_id = $payment_meta['user_info']['id'];

		foreach ( $cart_items as $item ) {

			if ( $new_status === 'complete' ) {
				$this->add_user_to_group( $user_id, $item['id'] );
			} else {
				$this->remove_user_from_group( $user_id, $item['id'] );
			}
		}

	}

	/**
	 * @param $user_id
	 * @param $product_id
	 *
	 * @return void
	 */
	private function add_user_to_group( $user_id, $product_id ) {

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

	private function remove_user_from_group( $user_id, $product_id ) {

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

	public function redirect_to_blueprint( $id ) {
		if ( edd_is_success_page() && isset( $_COOKIE[ DOLLIE_BLUEPRINTS_COOKIE ] ) && $_COOKIE[ DOLLIE_BLUEPRINTS_COOKIE ] ) {
			wp_redirect( dollie()->page()->get_launch_site_url() . '?payment-status=success&blueprint_id=' . $_COOKIE[ DOLLIE_BLUEPRINTS_COOKIE ] );
			exit;
		}
	}

	public function get_checkout_link( $args ) {
		$download_id = '';

		if ( $args['product_id'] ) {
			$download_id = $args['product_id'];
		} elseif ( $args['blueprint_id'] ) {
			$download_id = $args['blueprint_id'];
		}

		$checkout_params = [];

		if ( $download_id ) {
			$checkout_params = [
				'edd_action'                   => 'add_to_cart',
				'download_id'                  => $download_id,
				DOLLIE_BLUEPRINTS_COOKIE_PARAM => $download_id,
			];
		}

		return apply_filters( 'dollie/edd/checkout_link', edd_get_checkout_uri( $checkout_params ), $args );
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
			'name'             => 'Easy Digital Downloads
',
			'slug'             => 'easy-digital-downloads',
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
					0 => 'download',
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
