<?php

namespace Dollie\Core\Modules\Vip;

use Dollie\Core\Singleton;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class Services extends Singleton {

	/**
	 * Show VIP when enabled
	 *
	 * @param [type] $field
	 *
	 * @return void
	 */
	public function acf_field_vip_access( $field ) {
		acf_render_field_setting(
			$field,
			array(
				'label'        => __( 'Show when VIP Add-on Enabled' ),
				'instructions' => 'Only show this field when the VIP Add-on is enabled.',
				'name'         => 'dollie_vip_addon_enabled',
				'type'         => 'true_false',
				'ui'           => 1,
			),
			true
		);
	}

	/**
	 * Prepare VIP showing
	 */
	public function acf_field_vip_prepare_access( $field ) {
		$user_id          = get_current_user_id();
		$subscription_vip = dollie()->subscription()->has_vip( $user_id );
		$global_vip       = get_field( 'wpd_enable_global_vip_sites', 'options' );

		// bail early if no 'admin_only' setting.
		if ( empty( $field['dollie_vip_addon_enabled'] ) ) {
			return $field;
		}

		// return false if VIP is not allowed for user or hide field if Global VIP exist
		if ( ! $subscription_vip || $global_vip ) {
			echo '
				<style>
					.acf-field-' . substr( $field['key'], 6 ) . ' > .acf-label {display: none;}
				</style>';

			return false;
		}

		// return.
		return $field;
	}

	/**
	 * Add VIP option to deploy data.
	 *
	 * @param $deploy_data
	 *
	 * @return mixed
	 */
	public function add_vip_form_data( $deploy_data ) {

		$owner_id         = $deploy_data['owner_id'];
		$blueprint_id     = $deploy_data['blueprint_id'];
		$vip              = 0;
		$subscription_vip = dollie()->subscription()->has_vip( $owner_id );

		//Launch as VIP?
		$vip_checked = af_get_field( 'launch_as_vip' );

		//Is launch as VIP checked and does the user have a VIP subscription?
		if ( $vip_checked && $subscription_vip ) {
			$vip = 1;
		} // is global VIP enabled? If yes, launch as VIP even if user is no VIP
		elseif ( get_field( 'wpd_enable_global_vip_sites', 'options' ) ) {
			$vip = 1;
		}

		if ( get_post_meta( $blueprint_id, 'launch_blueprint_as_vip', true ) ) {
			$vip = 1;
		}

		$deploy_data['vip'] = $vip;

		return $deploy_data;

	}

	public function add_acf_fields( $field_group ) {

		global $pagenow;
		if (( $pagenow == 'post.php' ) && ($_GET['post_type'] == 'product')) {
			$user = 'a subscriber';
			$user_instructions = '';
		} else {
			$user = 'this customer';
			$user_instructions = '<br><br><strong> Set this to -1<strong/> to prevent this customer from launching more sites';
		}

		// general
		$fields = [
			array(
				'key'               => 'field_60a7974f69558',
				'label'             => __( 'VIP Sites', 'dollie' ),
				'name'              => 'wpd_enable_vip_sites',
				'type'              => 'true_false',
				'instructions'      => __( 'With VIP Sites you can enable additional resources, additional backups and priority support from our team for one or multiple sites with the click of a button.', 'dollie' ),
				'required'          => 0,
				'conditional_logic' => 0,
				'wrapper'           => array(
					'width' => '',
					'class' => 'add-on-wrapper',
					'id'    => '',
				),
				'hide_admin'        => 0,
				'message'           => '',
				'default_value'     => 1,
				'ui'                => 1,
				'ui_on_text'        => 'Enable',
				'ui_off_text'       => 'Disable',
			),
			array(
				'key'               => 'field_60a7974f2471a8',
				'label'             => __( 'Launch All Sites as VIP', 'dollie' ),
				'name'              => 'wpd_enable_global_vip_sites',
				'type'              => 'true_false',
				'instructions'      => __( 'If you would like to launch each site on your platform automatically as a VIP site, check this box.', 'dollie' ),
				'required'          => 0,
				'conditional_logic' => array(
					array(
						array(
							'field'    => 'field_60a7974f69558',
							'operator' => '==',
							'value'    => '1',
						),
					),
				),
				'wrapper'           => array(
					'width' => '',
					'class' => 'add-on-wrapper-end',
					'id'    => '',
				),
				'hide_admin'        => 0,
				'message'           => '',
				'default_value'     => 0,
				'ui'                => 1,
				'ui_on_text'        => 'Enable',
				'ui_off_text'       => 'Disable',
			),
		];

		$field_group = dollie()->add_acf_fields_to_group( $field_group, $fields, 'group_5ada1549129fb', 'wpd_enable_custom_backup', 'before' );

		// woo
		$fields = [
			array(
				'key'                      => 'field_5e2c1ac7246a2',
				'label'                    => __( 'VIP Sites', 'dollie' ),
				'name'                     => '',
				'type'                     => 'tab',
				'instructions'             => '',
				'required'                 => 0,
				'dollie_vip_addon_enabled' => 1,
				'conditional_logic'        => 0,
				'wrapper'                  => [
					'width' => '',
					'class' => '',
					'id'    => '',
				],
				'placement'                => 'top',
				'endpoint'                 => 0,
			),
			array(
				'key'                      => 'field_5fb3b46351578',
				'label'                    => __( 'Enable VIP Sites', 'dollie' ),
				'name'                     => '_wpd_woo_launch_as_vip',
				'type'                     => 'true_false',
				'instructions'      => sprintf( esc_html__( 'When enabled for %s all of their sites will automatically be VIP sites. Please look at the VIP Sites documentation to learn more.',  'dollie'), $user),
				'required'                 => 0,
				'wrapper'                  => array(
					'width' => '',
					'class' => '',
					'id'    => '',
				),
				'hide_admin'               => 0,
				'dollie_admin_only'        => 1,
				'dollie_vip_addon_enabled' => 1,
				'message'                  => '',
				'default_value'            => 0,
				'ui'                       => 1,
				'ui_on_text'               => '',
				'ui_off_text'              => '',
			)
		];

		$field_group = dollie()->add_acf_fields_to_group( $field_group, $fields, 'group_5afc7b8e22840', '_wpd_excluded_blueprints', 'after' );


		//forms
		$fields = [
			array(
				'key'               => 'field_5fb3b53ff744632',
				'label'             => __( 'Launch as VIP Site', 'dollie' ),
				'name'              => 'launch_as_vip',
				'type'              => 'true_false',
				'instructions'      => __( 'Launch this site as a VIP site', 'dollie' ),
				'required'          => 0,
				'conditional_logic' => array(
					array(
						array(
							'field'    => 'field_601a8d9bc4b42',
							'operator' => '==',
							'value'    => 'site',
						),
					),
				),
				'wrapper'           => array(
					'width' => '',
					'class' => '',
					'id'    => '',
				),
				'hide_admin'        => 0,
				'dollie_vip_addon_enabled' => 1,
				'message'           => '',
				'default_value'     => 0,
				'ui'                => 1,
				'ui_on_text'        => '',
				'ui_off_text'       => '',
			)
		];
		$field_group = dollie()->add_acf_fields_to_group( $field_group, $fields, 'group_5e6a176c384ee', 'advanced_settings', 'before' );

		$fields = [
			array(
				'key'               => 'field_5fb3b53ff7gty4463djgj2',
				'label'             => __( 'Always Launch as VIP Site', 'dollie' ),
				'name'              => 'launch_blueprint_as_vip',
				'type'              => 'true_false',
				'instructions'      => __( 'When someone launches a site based on this Blueprint it will be automatically marked as VIP', 'dollie' ),
				'required'          => 0,
				'wrapper'           => array(
					'width' => '',
					'class' => '',
					'id'    => '',
				),
				'hide_admin'        => 0,
				'dollie_vip_addon_enabled' => 1,
				'message'           => '',
				'default_value'     => 0,
				'ui'                => 1,
				'ui_on_text'        => '',
				'ui_off_text'       => '',
			),
			array(
				'key'               => 'field_5fb3bsa53ff7gty4463djgj2',
				'label'             => __( 'Only show to VIP Users', 'dollie' ),
				'name'              => 'show_blueprint_to_vip',
				'type'              => 'true_false',
				'instructions'      => __( 'Only show this Blueprint to users who have VIP access. Note - The Blueprint will still be shown in your Blueprint Listings', 'dollie' ),
				'required'          => 0,
				'wrapper'           => array(
					'width' => '',
					'class' => '',
					'id'    => '',
				),
				'hide_admin'        => 0,
				'dollie_vip_addon_enabled' => 1,
				'message'           => '',
				'default_value'     => 0,
				'ui'                => 1,
				'ui_on_text'        => '',
				'ui_off_text'       => '',
			),
		];
		$field_group = dollie()->add_acf_fields_to_group( $field_group, $fields, 'group_5affdcd76c8d1', 'wpd_blueprint_custom_image', 'after' );

		return $field_group;

	}


}
