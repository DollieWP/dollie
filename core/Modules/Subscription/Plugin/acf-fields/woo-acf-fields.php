<?php

if ( function_exists( 'acf_add_local_field_group' ) ) :

	acf_add_local_field_group(
		[
			'key'                   => 'group_5afc7b8e22840',
			'title'                 => 'Dollie Product Setup',
			'fields'                => [
				[
					'key'               => 'field_5e2c1a97c1541',
					'label'             => __( 'Basic', 'dollie' ),
					'name'              => '',
					'type'              => 'tab',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => [
						'width' => '',
						'class' => '',
						'id'    => '',
					],
					'placement'         => 'top',
					'endpoint'          => 0,
				],
				[
					'key'               => 'field_5afc7bad022a8',
					'label'             => __( 'Number of Sites', 'dollie' ),
					'name'              => '_wpd_installs',
					'type'              => 'number',
					'instructions'      => __( 'How many sites can a customer deploy when subscribed to this product?', 'dollie' ),
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => [
						'width' => '',
						'class' => '',
						'id'    => '',
					],
					'hide_admin'        => 0,
					'default_value'     => 1,
					'placeholder'       => '',
					'prepend'           => '',
					'append'            => '',
					'min'               => '',
					'max'               => '',
					'step'              => '',
				],
				[
					'key'               => 'field_5afc7c0a022a9',
					'label'             => __( 'Disk Space', 'dollie' ),
					'name'              => '_wpd_max_size',
					'type'              => 'number',
					'instructions'      => __( 'The amount of space customer can use when subscribed to this product.', 'dollie' ),
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => [
						'width' => '',
						'class' => '',
						'id'    => '',
					],
					'hide_admin'        => 0,
					'default_value'     => '',
					'placeholder'       => 2,
					'prepend'           => '',
					'append'            => 'GB',
					'min'               => '',
					'max'               => '',
					'step'              => '',
				],
				[
					'key'               => 'field_5e2c1ac7c1542',
					'label'             => __( 'Blueprints', 'dollie' ),
					'name'              => '',
					'type'              => 'tab',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => [
						'width' => '',
						'class' => '',
						'id'    => '',
					],
					'placement'         => 'top',
					'endpoint'          => 0,
				],
				[
					'key'               => 'field_5e2c1adcc1543',
					'label'             => __( 'Included Blueprints', 'dollie' ),
					'name'              => '_wpd_included_blueprints',
					'type'              => 'relationship',
					'instructions'      => __( 'Select which of your Blueprints are allowed to be deployed when a customer has an active subscription to this product.', 'dollie' ),
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => [
						'width' => '',
						'class' => '',
						'id'    => '',
					],
					'post_type'         => [
						0 => 'container',
					],
					'taxonomy'          => '',
					'filters'           => '',
					'elements'          => '',
					'min'               => '',
					'max'               => '',
					'return_format'     => 'id',
				],
				[
					'key'               => 'field_5e2c1b94c1544',
					'label'             => __( 'Excluded Blueprints', 'dollie' ),
					'name'              => '_wpd_excluded_blueprints',
					'type'              => 'relationship',
					'instructions'      => __( 'Select which of your Blueprints can not be chosen when a customer has an active subscription to this product. Use this setting if you have a lot of blueprints and only want to restrict access to a couple of them.', 'dollie' ),
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => [
						'width' => '',
						'class' => '',
						'id'    => '',
					],
					'post_type'         => [
						0 => 'container',
					],
					'taxonomy'          => '',
					'filters'           => '',
					'elements'          => '',
					'min'               => '',
					'max'               => '',
					'return_format'     => 'id',
				],
			],
			'location'              => [
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'product',
					],
				],
			],
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen'        => '',
			'active'                => true,
			'description'           => '',
		]
	);

endif;