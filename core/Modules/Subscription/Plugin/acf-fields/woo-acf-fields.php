<?php
global $pagenow;
if (( $pagenow == 'post.php' ) && ($_GET['post_type'] == 'product')) {
	$user = 'a subscriber';
	$user_instructions = '';
} else {
	$user = 'this customer';
	$user_instructions = '<br><br><strong> Set this to -1<strong/> to prevent this customer from launching more sites';
}

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
					'instructions'      => sprintf( esc_html__( 'How many sites can %s launch?', 'dollie' ), $user) . $user_instructions,
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
					'instructions'      => sprintf( esc_html__( 'How much space can %s use for all their sites combined?',  'dollie'), $user),
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => [
						'width' => '',
						'class' => '',
						'id'    => '',
					],
					'hide_admin'        => 0,
					'default_value'     => '',
					'placeholder'       => '',
					'prepend'           => '',
					'append'            => 'GB',
					'min'               => '',
					'max'               => '',
					'step'              => '',
				],
				[
					'key' => 'field_60ace0aedf140',
					'label' => __('Staging Sites', 'dollie'),
					'name' => '_wpd_staging_installs',
					'type' => 'number',
					'instructions'      => sprintf( esc_html__( 'How many staging sites can %s launch?',  'dollie'), $user),
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'hide_admin' => 0,
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'min' => '',
					'max' => '',
					'step' => '',
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
					'label'             => __( 'Allowed Blueprints', 'dollie' ),
					'name'              => '_wpd_included_blueprints',
					'type'              => 'relationship',
					'instructions'      => sprintf( 'Select which of your Blueprints are <strong>allowed</strong> to be launched by %s',  $user),
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
					'label'             => __( 'Disallowed Blueprints', 'dollie' ),
					'name'              => '_wpd_excluded_blueprints',
					'type'              => 'relationship',
					'instructions'     	=> sprintf( 'Select which of your Blueprints can <strong>not allowed</strong> by %s. Use this setting if you have a lot of blueprints and only want to restrict access to a couple of them.', $user),
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
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'download',
					],
				],
				[
					[
						'param'    => 'user_form',
						'operator' => '==',
						'value'    => 'edit',
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
