<?php
global $pagenow;


if ( $pagenow === 'user-edit.php' ) {
		$user                     = 'this customer';
		$user_instructions        = '<br><br><strong> Set this to -1 to prevent this customer from launching more sites.</strong>';
		$dol_title                = 'Hub Access Settings for this User';
		$description              = 'These settings allow you to modify the default access settings for this customer based on their Access Group(s). Watch the video below to learn more about Access Groups.';
		$add_to_group_description = 'Add this customer to an Access Group. This will override the default Access Group(s) for this customer.';
} else {
		$user                     = 'a user in this group';
		$user_instructions        = '';
		$dol_title                = 'Hub Access Settings for this Group';
		$description              = 'Control which Hub features are available to the users in this group. Watch the video below to learn more about Access Groups.';
		$add_to_group_description = 'Choose the Access Groups you would like to connect. Once connected users will be added/removed based on specific triggers inside your integration.';
}

if ( function_exists( 'acf_add_local_field_group' ) ) :

	if ( $pagenow === 'user-edit.php' ) {
		// Register Fields
		$user_fields = array(
			array(
				'key'               => 'field_612616dc483456frerf24rnjgnjk64',
				'label'             => 'Overwrite Access Settings for this User',
				'name'              => 'wpd_enable_user_access_overwrite',
				'type'              => 'true_false',
				'instructions'      => 'Enable this to overwrite the default Access Group(s) for ' . $user . '. This will overwrite all access settings for this user and only allow access to the features you select below.',
				'required'          => 0,
				'conditional_logic' => 0,
				'wrapper'           => array(
					'width' => '',
					'class' => 'dol-always-show',
					'id'    => '',
				),
				'message'           => '',
				'default_value'     => 0,
				'ui'                => 1,
				'ui_on_text'        => 'Enabled',
				'ui_off_text'       => 'Disabled',
			),
		);

	}

		$fields = isset( $user_fields ) ? $user_fields : array();

	$fields = array_merge(
		$fields,
		array(
			array(
				'key'               => 'field_612616dc483456f24rnjgnjk64',
				'label'             => dollie()->show_helper_video( 'access-groups', 'TIf53hl-O9U', 'Watch Video', 'Hub Access Groups' ),
				'name'              => 'dollie_show_video',
				'type'              => 'message',
				'instructions'      => '',
				'required'          => 0,
				'conditional_logic' => 0,
				'wrapper'           => array(
					'width' => '',
					'class' => 'dollie-field-break',
					'id'    => '',
				),
				'hide_admin'        => 0,
				'message'           => $description,
				'new_lines'         => '',
				'esc_html'          => 0,
			),
			array(
				'key'               => 'field_5e2c1a97c1541',
				'label'             => __( 'Sites', 'dollie' ),
				'name'              => '',
				'type'              => 'tab',
				'instructions'      => '',
				'required'          => 0,
				'conditional_logic' => 0,
				'wrapper'           => array(
					'width' => '',
					'class' => '',
					'id'    => '',
				),
				'placement'         => 'top',
				'endpoint'          => 0,
			),
			array(
				'key'               => 'field_5afc7bad022a8',
				'label'             => __( 'Number of Sites', 'dollie' ),
				'name'              => '_wpd_installs',
				'type'              => 'number',
				'instructions'      => sprintf( esc_html__( 'How many sites can % s launch ? ', 'dollie' ), $user ) . $user_instructions,
				'required'          => 0,
				'conditional_logic' => 0,
				'wrapper'           => array(
					'width' => '',
					'class' => '',
					'id'    => '',
				),
				'hide_admin'        => 0,
				'default_value'     => 1,
				'placeholder'       => '',
				'prepend'           => '',
				'append'            => '',
				'min'               => '',
				'max'               => '',
				'step'              => '',
			),
			array(
				'key'               => 'field_5afc7c0a022a9',
				'label'             => __( 'Disk Space', 'dollie' ),
				'name'              => '_wpd_max_size',
				'type'              => 'number',
				'instructions'      => sprintf( esc_html__( 'How much space can % s use for all their sites combined ? ', 'dollie' ), $user ),
				'required'          => 0,
				'conditional_logic' => 0,
				'wrapper'           => array(
					'width' => '',
					'class' => '',
					'id'    => '',
				),
				'hide_admin'        => 0,
				'default_value'     => '',
				'placeholder'       => '',
				'prepend'           => '',
				'append'            => 'GB',
				'min'               => '',
				'max'               => '',
				'step'              => '',
			),
			array(
				'key'               => 'field_60ace0aedf140',
				'label'             => __( 'Staging Sites', 'dollie' ),
				'name'              => '_wpd_staging_installs',
				'type'              => 'number',
				'instructions'      => sprintf( esc_html__( 'How many staging sites can % s launch ? ', 'dollie' ), $user ),
				'required'          => 0,
				'conditional_logic' => 0,
				'wrapper'           => array(
					'width' => '',
					'class' => '',
					'id'    => '',
				),
				'hide_admin'        => 0,
				'default_value'     => '',
				'placeholder'       => '',
				'prepend'           => '',
				'append'            => '',
				'min'               => '',
				'max'               => '',
				'step'              => '',
			),
			array(
				'key'               => 'field_5e2c1ac7c1542',
				'label'             => __( 'Blueprints', 'dollie' ),
				'name'              => '',
				'type'              => 'tab',
				'instructions'      => '',
				'required'          => 0,
				'conditional_logic' => 0,
				'wrapper'           => array(
					'width' => '',
					'class' => '',
					'id'    => '',
				),
				'placement'         => 'top',
				'endpoint'          => 0,
			),
			array(
				'key'               => 'field_5e2c1adcc1543',
				'label'             => __( 'Allowed Blueprints', 'dollie' ),
				'name'              => '_wpd_included_blueprints',
				'type'              => 'relationship',
				'instructions'      => sprintf( 'Select which of your Blueprints are < strong > allowed < / strong > to be launched by % s', $user ),
				'required'          => 0,
				'conditional_logic' => 0,
				'wrapper'           => array(
					'width' => '',
					'class' => '',
					'id'    => '',
				),
				'post_type'         => array(
					0 => 'container',
				),
				'taxonomy'          => '',
				'filters'           => '',
				'elements'          => '',
				'min'               => '',
				'max'               => '',
				'return_format'     => 'id',
			),
			array(
				'key'               => 'field_5e2c1b94c1544',
				'label'             => __( 'Disallowed Blueprints', 'dollie' ),
				'name'              => '_wpd_excluded_blueprints',
				'type'              => 'relationship',
				'instructions'      => sprintf( 'Select which of your Blueprints can < strong > not allowed < / strong > by % s . use this setting if you have a lot of blueprints and only want to restrict access to a couple of them . ', $user ),
				'required'          => 0,
				'conditional_logic' => 0,
				'wrapper'           => array(
					'width' => '',
					'class' => '',
					'id'    => '',
				),
				'post_type'         => array(
					0 => 'container',
				),
				'taxonomy'          => '',
				'filters'           => '',
				'elements'          => '',
				'min'               => '',
				'max'               => '',
				'return_format'     => 'id',
			),
			array(
				'key'               => 'field_5e2c1a97c154dkfky1',
				'label'             => __( 'Available Hub Features', 'dollie' ),
				'name'              => '',
				'type'              => 'tab',
				'instructions'      => '',
				'required'          => 0,
				'conditional_logic' => 0,
				'wrapper'           => array(
					'width' => '',
					'class' => '',
					'id'    => '',
				),
				'placement'         => 'top',
				'endpoint'          => 0,
			),
			array(
				'key'               => 'field_5b094ea3fe6ea',
				'label'             => sprintf( esc_html__( 'Enable', 'dollie-setup' ), 'Clients', 'Sites' ),
				'name'              => 'wpd_allow_site_dashboard_access',
				'type'              => 'true_false',
				'instructions'      => sprintf( esc_html__( 'Would you like to give youor would you like to restrict this functionality to your team only ? ', 'dollie-setup' ), 'Clients', 'Sites' ),
				'required'          => 0,
				'conditional_logic' => 0,
				'wrapper'           => array(
					'width' => '',
					'class' => '',
					'id'    => '',
				),
				'message'           => '',
				'default_value'     => 1,
				'ui'                => 1,
				'ui_on_text'        => 'Yes',
				'ui_off_text'       => 'No',
			),
			array(
				'key'               => 'field_5b06c0124f11a',
				'label'             => sprintf( esc_html__( ' % s Dashboard Features', 'dollie-setup' ), 'Sites' ),
				'name'              => 'available_sections',
				'type'              => 'checkbox',
				'instructions'      => sprintf( esc_html__( 'Depending on the type . ', 'dollie-setup' ), 'customers', 'Site' ),
				'required'          => 0,
				'conditional_logic' => array(
					array(
						array(
							'field'    => 'field_5b094ea3fe6ea',
							'operator' => ' == ',
							'value'    => '1',
						),
					),
				),
				'wrapper'           => array(
					'width' => '',
					'class' => '',
					'id'    => '',
				),
				'hide_admin'        => 0,
				'choices'           => array(
					'dashboard'       => 'Dashboard',
					'plugins'         => 'Plugins',
					'themes'          => 'Themes',
					'domains'         => 'Domains',
					'migrate'         => 'Migrate',
					'backups'         => 'Backups',
					'updates'         => 'Updates',
					'developer-tools' => 'Developer Tools',
					'delete'          => 'Site Deletion',
				),
				'allow_custom'      => 0,
				'default_value'     => 0,
				'layout'            => 'vertical',
				'toggle'            => 0,
				'return_format'     => 'value',
				'save_custom'       => 0,
			),
			array(
				'key'               => 'field_58861a3cc49b2',
				'label'             => __( 'Available Developer Features', 'dollie' ),
				'name'              => 'available_features_developers',
				'type'              => 'checkbox',
				'instructions'      => sprintf( esc_html__( 'Disable certain( advanced ) features of the % s Developer Tools . ', 'dollie-setup' ), 'Site' ),
				'required'          => 0,
				'conditional_logic' => array(
					array(
						array(
							'field'    => 'field_5b094ea3fe6ea',
							'operator' => ' == ',
							'value'    => '1',
						),
					),
				),
				'wrapper'           => array(
					'width' => '',
					'class' => '',
					'id'    => '',
				),
				'hide_admin'        => 0,
				'choices'           => array(
					'code-editor' => 'Code Editor',
					'database'    => 'Database',
					'shell'       => 'WP-CLI',
				),
				'allow_custom'      => 0,
				'default_value'     => 0,
				'layout'            => 'vertical',
				'toggle'            => 0,
				'return_format'     => 'value',
				'save_custom'       => 0,
			),
		)
	);



				// Add more fields here as required
				acf_add_local_field_group(
					array(
						'key'                   => 'group_5afc7b8e22840',
						'title'                 => 'Dollie Access Settings',
						'fields'                => $fields,
						'location'              => array(
							array(
								array(
									'param'    => 'post_type',
									'operator' => '==',
									'value'    => 'dollie-access-groups',
								),
							),
							array(
								array(
									'param'    => 'user_form',
									'operator' => '==',
									'value'    => 'edit',
								),
							),
						),
						'menu_order'            => 0,
						'position'              => 'normal',
						'style'                 => 'default',
						'label_placement'       => 'top',
						'instruction_placement' => 'label',
						'hide_on_screen'        => '',
						'active'                => true,
						'description'           => '',
					)
				);



	if ( function_exists( 'acf_add_local_field_group' ) ) :

		acf_add_local_field_group(
			array(
				'key'                   => 'group_631b44d92d7fd',
				'title'                 => 'Access Management',
				'fields'                => array(
					array(
						'key'                      => 'field_631b450b42c59',
						'label'                    => __( 'Group Users', 'dollie' ),
						'name'                     => 'wpd_group_users',
						'aria-label'               => '',
						'type'                     => 'user',
						'instructions'             => __( 'The users in this group . ', 'dollie' ),
						'required'                 => 0,
						'conditional_logic'        => 0,
						'wrapper'                  => array(
							'width' => '',
							'class' => '',
							'id'    => '',
						),
						'dollie_vip_addon_enabled' => 0,
						'dollie_admin_only'        => 1,
						'acfe_save_meta'           => 0,
						'hide_admin'               => 0,
						'role'                     => '',
						'return_format'            => 'id',
						'multiple'                 => 1,
						'acfe_bidirectional'       => array(
							'acfe_bidirectional_enabled' => true,
							'acfe_bidirectional_related' => array(
								0 => 'field_631b46736f8dc',
							),
						),
						'allow_null'               => 1,
					),
					array(
						'key'                           => 'field_649420e3e3fb7',
						'label'                         => __( 'Integrations Added to this Group', 'dollie' ),
						'name'                          => 'wpd_registered_integrations',
						'aria-label'                    => '',
						'type'                          => 'repeater',
						'instructions'                  => '',
						'required'                      => 0,
						'conditional_logic'             => 0,
						'wrapper'                       => array(
							'width' => '',
							'class' => '',
							'id'    => '',
						),
						'dollie_vip_addon_enabled'      => 0,
						'dollie_admin_only'             => 0,
						'acfe_save_meta'                => 0,
						'hide_admin'                    => 0,
						'acfe_repeater_stylised_button' => 0,
						'layout'                        => 'table',
						'pagination'                    => 0,
						'min'                           => 0,
						'max'                           => 0,
						'collapsed'                     => '',
						'button_label'                  => 'Add Row',
						'rows_per_page'                 => 20,
						'sub_fields'                    => array(
							array(
								'key'                      => 'field_649420ffe3fb8',
								'label'                    => __( 'Name', 'dollie' ),
								'name'                     => 'name',
								'aria-label'               => '',
								'type'                     => 'text',
								'instructions'             => '',
								'required'                 => 0,
								'conditional_logic'        => 0,
								'wrapper'                  => array(
									'width' => '',
									'class' => '',
									'id'    => '',
								),
								'dollie_vip_addon_enabled' => 0,
								'dollie_admin_only'        => 0,
								'acfe_save_meta'           => 0,
								'hide_admin'               => 0,
								'default_value'            => '',
								'maxlength'                => '',
								'placeholder'              => '',
								'prepend'                  => '',
								'append'                   => '',
								'parent_repeater'          => 'field_649420e3e3fb7',
							),
							array(
								'key'                      => 'field_64942109e3fb9',
								'label'                    => __( 'Actions', 'dollie' ),
								'name'                     => 'actions',
								'aria-label'               => '',
								'type'                     => 'text',
								'instructions'             => '',
								'required'                 => 0,
								'conditional_logic'        => 0,
								'wrapper'                  => array(
									'width' => '',
									'class' => '',
									'id'    => '',
								),
								'dollie_vip_addon_enabled' => 0,
								'dollie_admin_only'        => 0,
								'acfe_save_meta'           => 0,
								'hide_admin'               => 0,
								'default_value'            => '',
								'maxlength'                => '',
								'placeholder'              => '',
								'prepend'                  => '',
								'append'                   => '',
								'parent_repeater'          => 'field_649420e3e3fb7',
							),
						),
					),
				),
				'location'              => array(
					array(
						array(
							'param'    => 'post_type',
							'operator' => ' == ',
							'value'    => 'dollie-access-groups',
						),
					),
					array(
						array(
							'param'    => 'taxonomy',
							'operator' => ' == ',
							'value'    => 'all',
						),
					),
				),
				'menu_order'            => 0,
				'position'              => 'normal',
				'style'                 => 'default',
				'label_placement'       => 'left',
				'instruction_placement' => 'label',
				'hide_on_screen'        => '',
				'active'                => true,
				'description'           => '',
				'show_in_rest'          => 0,
				'acfe_display_title'    => '',
				'acfe_autosync'         => '',
				'acfe_form'             => 0,
				'acfe_meta'             => '',
				'acfe_note'             => '',
			)
		);

		acf_add_local_field_group(
			array(
				'key'                   => 'group_631b463ce5739',
				'title'                 => $dol_title,
				'fields'                => array(
					array(
						'key'                      => 'field_64934006e7e54',
						'label'                    => 'How it works',
						'name'                     => '',
						'aria-label'               => '',
						'type'                     => 'message',
						'instructions'             => '',
						'required'                 => 0,
						'conditional_logic'        => 0,
						'wrapper'                  => array(
							'width' => '',
							'class' => '',
							'id'    => '',
						),
						'dollie_vip_addon_enabled' => 0,
						'dollie_admin_only'        => 0,
						'acfe_save_meta'           => 0,
						'hide_admin'               => 0,
						'message'                  => __( 'With Hub Access Groups you can easily control what your customers / client can do inside your Hub . for example you can control the amount of sites they can launch, the blueprints they can have access too, or which areas of their Site Dashboard are available . < br > < br > ' . dollie()->show_helper_video( 'access-groups', 'TIf53hl-O9U', 'Watch Video', 'Hub Access Groups' ), 'dollie' ),
						'new_lines'                => 'wpautop',
						'esc_html'                 => 0,
					),
					array(
						'key'                      => 'field_631b46736f8dc',
						'label'                    => __( 'Connect to Hub Access Groups', 'dollie' ),
						'name'                     => 'wpd_group_users',
						'aria-label'               => '',
						'type'                     => 'relationship',
						'instructions'             => $add_to_group_description,
						'required'                 => 0,
						'conditional_logic'        => 0,
						'wrapper'                  => array(
							'width' => '',
							'class' => '',
							'id'    => '',
						),
						'dollie_vip_addon_enabled' => 0,
						'dollie_admin_only'        => 0,
						'acfe_save_meta'           => 0,
						'hide_admin'               => 0,
						'post_type'                => array(
							0 => 'dollie-access-groups',
						),
						'post_status'              => '',
						'taxonomy'                 => '',
						'filters'                  => array(
							0 => 'search',
							1 => 'post_type',
							2 => 'taxonomy',
						),
						'return_format'            => 'id',
						'acfe_bidirectional'       => array(
							'acfe_bidirectional_enabled' => '1',
							'acfe_bidirectional_related' => array(
								0 => 'field_631b450b42c59',
							),
						),
						'min'                      => '',
						'max'                      => '',
						'elements'                 => '',
					),
				),
				'location'              => array(
					array(
						array(
							'param'    => 'user_form',
							'operator' => ' == ',
							'value'    => 'edit',
						),
					),
					array(
						array(
							'param'    => 'post_type',
							'operator' => ' == ',
							'value'    => 'product',
						),
					),
					array(
						array(
							'param'    => 'post_type',
							'operator' => ' == ',
							'value'    => 'product_variation',
						),
					),
				),
				'menu_order'            => 0,
				'position'              => 'normal',
				'style'                 => 'default',
				'label_placement'       => 'left',
				'instruction_placement' => 'label',
				'hide_on_screen'        => '',
				'active'                => true,
				'description'           => '',
				'show_in_rest'          => 0,
				'acfe_display_title'    => '',
				'acfe_autosync'         => '',
				'acfe_form'             => 0,
				'acfe_meta'             => '',
				'acfe_note'             => '',
			)
		);


endif;

endif;
