<?php

class AF_Pro {
	

	function __construct() {

		$this->initialize();
		
		// Register options page
		add_action( 'acf/init', array( $this, 'register_options_page' ), 10, 0 );

	}


	/**
	 * Initializes the plugin and makes sure ACF is installed
	 *
	 * @since 1.0.0
	 *
	 */
	function initialize() {

		// Set global object Pro flag
		AF()->pro = true;

		// EDD Integration
		include( plugin_dir_path( __FILE__ ) . '/edd/AF_EDD_SL_Plugin_Updater.php' );
		AF()->classes['pro_edd'] = include( plugin_dir_path( __FILE__ ) . '/edd/edd-integration.php' );
		
		// API
		include( plugin_dir_path( __FILE__ ) . '/api/api-integrations.php' );
		
		// Core
		AF()->classes['pro_core_editing'] = include( plugin_dir_path( __FILE__ ) . '/core/core-editing.php' );
		AF()->classes['pro_core_slack'] = include( plugin_dir_path( __FILE__ ) . '/core/integrations/core-slack.php' );
		AF()->classes['pro_core_mailchimp'] = include( plugin_dir_path( __FILE__ ) . '/core/integrations/core-mailchimp.php' );
		AF()->classes['pro_core_zapier'] = include( plugin_dir_path( __FILE__ ) . '/core/integrations/core-zapier.php' );
		AF()->classes['pro_core_calculated'] = include( plugin_dir_path( __FILE__ ) . '/core/core-calculated.php' );

		// ACF additions
		include( plugin_dir_path( __FILE__ ) . '/acf/fields/calculated.php' );
		include( plugin_dir_path( __FILE__ ) . '/acf/fields/calculated-admin.php' );

		// Admin
		AF()->classes['pro_admin_editing'] = include( plugin_dir_path( __FILE__ ) . '/admin/admin-editing.php' );
		AF()->classes['pro_admin_integrations'] = include( plugin_dir_path( __FILE__ ) . '/admin/admin-integrations.php' );
		AF()->classes['pro_admin_calculated'] = include( plugin_dir_path( __FILE__ ) . '/admin/admin-calculated.php' );

	}
	
	
	function register_options_page() {
		
		// Add options page under CPT
		acf_add_options_sub_page(array(
			'page_title' => 'Advanced Forms',
			'menu_title' => 'Settings',
			'parent_slug' => 'edit.php?post_type=af_form',
		));
		
		
		$settings_field_group = array(
			'key' => 'group_5957d1766a514',
			'title' => 'Form settings',
			'fields' => array (),
			'location' => array (
				array (
					array (
						'param' => 'options_page',
						'operator' => '==',
						'value' => 'acf-options-settings',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => 1,
			'description' => '',
		);
		
		
		$settings_field_group = apply_filters( 'af/settings_fields', $settings_field_group );
		
		acf_add_local_field_group( $settings_field_group );
		
	}


}


return new AF_Pro();