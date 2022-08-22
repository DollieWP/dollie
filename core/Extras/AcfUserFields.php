<?php
if ( function_exists( 'acf_add_local_field_group' ) ) :
	acf_add_local_field_group(
		array(
			'key'                   => 'group_5b0ea59dbbb22',
			'title'                 => 'Dollie Customer Data(do not edit)',
			'fields'                => array(
				array(
					'key'               => 'field_5b0ea5c46ffbb',
					'label'             => __( 'Active Dollie Subscription', 'dollie' ),
					'name'              => 'wpd_active_subscription',
					'type'              => 'text',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => 'acf-hidden',
						'id'    => '',
					),
					'hide_admin'        => 0,
					'default_value'     => '',
					'placeholder'       => '',
					'prepend'           => '',
					'append'            => '',
					'maxlength'         => '',
				),
				array(
					'key'               => 'field_5b0eaaa52ec57',
					'label'             => __( 'Stop Container at..', 'dollie' ),
					'name'              => 'wpd_stop_container_at',
					'type'              => 'text',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => 'acf-hidden',
						'id'    => '',
					),
					'default_value'     => '',
					'placeholder'       => '',
					'prepend'           => '',
					'append'            => '',
					'maxlength'         => '',
				),
				array(
					'key'               => 'field_5b0eacb73940a',
					'label'             => __( 'Containers undeployed?', 'dollie' ),
					'name'              => 'wpd_all_containers_undeployed',
					'type'              => 'text',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => 'acf-hidden',
						'id'    => '',
					),
					'default_value'     => '',
					'placeholder'       => '',
					'prepend'           => '',
					'append'            => '',
					'maxlength'         => '',
				),
			),
			'location'              => array(
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
			'active'                => false,
			'description'           => '',
		)
	);

	acf_add_local_field_group(
		array(
			'key'                   => 'group_5efc4bbc3849b',
			'title'                 => 'Dollie Customer Info',
			'fields'                => array(
				array(
					'key'               => 'field_5efc4bbc3d814',
					'label'             => __( 'Client Deployed Site Permissions', 'dollie' ),
					'name'              => 'wpd_client_site_permissions',
					'type'              => 'button_group',
					'dollie_admin_only' => 1,
					'instructions'      => __( 'Choose the user role given to the user when deploying a site. Default inherits from Dollie - Settings - Access Control', 'dollie' ),
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'hide_admin'        => 0,
					'choices'           => array(
						'default'       => 'Default Dollie Setting',
						'administrator' => 'Administrator',
						'editor'        => 'Editor',
					),
					'allow_null'        => 0,
					'default_value'     => 'default',
					'layout'            => 'horizontal',
					'return_format'     => 'value',
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'user_form',
						'operator' => '==',
						'value'    => 'edit',
					),
				),
			),
			'menu_order'            => -9999,
			'position'              => 'high',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen'        => '',
			'active'                => true,
			'description'           => '',
		)
	);

endif;
