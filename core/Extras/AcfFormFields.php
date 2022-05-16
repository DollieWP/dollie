<?php
if ( function_exists( 'acf_add_local_field_group' ) ) :

	acf_add_local_field_group(
		array(
			'key'                   => 'group_5e8243b7b70c4',
			'title'                 => '[Form] [Domain] Connect',
			'fields'                => array(
				array(
					'key'               => 'field_5e847fa49d718',
					'label'             => __( 'Intro message', 'dollie' ),
					'name'              => '',
					'type'              => 'message',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => 'acf-hide-title',
						'id'    => '',
					),
					'hide_admin'        => 0,
					'message'           => __(
						'[dollie-blockquote type="success" icon="fal fa-globe" title="Let\'s link up your custom domain {dollie_user_display_name}!"]
We\'ll walk you through all the steps required to link your own domain to your site. Let\'s get started shall we?
[/dollie-blockquote]',
						'dollie'
					),
					'new_lines'         => '',
					'esc_html'          => 0,
				),
				array(
					'key'               => 'field_5e824503392c5',
					'label'             => __( 'Have you registered a domain name?', 'dollie' ),
					'name'              => 'is_domain_registered',
					'type'              => 'radio',
					'instructions'      => '',
					'required'          => 1,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'hide_admin'        => 0,
					'choices'           => array(
						'yes' => 'Yes, I have registered a domain',
						'no'  => 'No, I still need a domain name',
					),
					'allow_null'        => 0,
					'other_choice'      => 0,
					'default_value'     => '',
					'layout'            => 'vertical',
					'return_format'     => 'value',
					'save_other_choice' => 0,
				),
				array(
					'key'               => 'field_5e824686392c8',
					'label'             => '',
					'name'              => '',
					'type'              => 'message',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'field_5e824503392c5',
								'operator' => '==',
								'value'    => 'yes',
							),
						),
					),
					'wrapper'           => array(
						'width' => '',
						'class' => 'acf-hide-title',
						'id'    => '',
					),
					'hide_admin'        => 0,
					'message'           => __(
						'<div class="dol-bg-gray-100 dol-py-2 dol-px-4 dol-rounded">
<h5 class="dol-text-base">Migration Tip</h5>
<p class="dol-text-sm">Do you already have an active site on this domain and want to keep the content? Head on to the Migration tab and follow the instructions to migrate your existing site. After that you can start the domain connect wizard.</p>
</div>',
						'dollie'
					),
					'new_lines'         => '',
					'esc_html'          => 0,
				),
				array(
					'key'               => 'field_5e82fd0de3035',
					'label'             => '',
					'name'              => '',
					'type'              => 'message',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'field_5e824503392c5',
								'operator' => '==',
								'value'    => 'no',
							),
						),
					),
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'hide_admin'        => 0,
					'message'           => __(
						'<h5>Register your domain name</h5>
<p>We are not selling domains directly.</p>
<p>If you have not registered your own domain yet, this is the time to do so! We recommend <a href="https://namecheap.com" target="_blank">NameCheap</a> because of their easy to use domain manager and very low prices, but you are free to choose any other domain registrar.
<strong>Go ahead, register your domain and come back to this form to continue the domain setup!</strong></p>',
						'dollie'
					),
					'new_lines'         => '',
					'esc_html'          => 0,
				),
				array(
					'key'               => 'field_5e82fd8ee3037',
					'label'             => __( 'Your Domain Name', 'dollie' ),
					'name'              => 'domain_name',
					'type'              => 'text',
					'instructions'      => __( 'Please type your domain name without "www." or "http(s)://"', 'dollie' ),
					'required'          => 1,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '50',
						'class' => 'dollie-domain',
						'id'    => '',
					),
					'hide_admin'        => 0,
					'default_value'     => '',
					'placeholder'       => 'example.com',
					'prepend'           => '',
					'append'            => '',
					'maxlength'         => '',
				),
				array(
					'key'               => 'field_616554b36fd74',
					'label'             => __( 'Allow us to manage your DNS records?', 'dollie' ),
					'name'              => 'allow_dns',
					'type'              => 'radio',
					'instructions'      => '',
					'required'          => 1,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'hide_admin'        => 0,
					'choices'           => array(
						'yes' => 'Yes',
						'no'  => 'No',
					),
					'allow_null'        => 0,
					'other_choice'      => 0,
					'default_value'     => 'no',
					'layout'            => 'vertical',
					'return_format'     => 'value',
					'save_other_choice' => 0,
				),
				array(
					'key'               => 'field_5ff5bf04953b2',
					'label'             => __( 'message', 'dollie' ),
					'name'              => '',
					'type'              => 'message',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'field_616554b36fd74',
								'operator' => '==',
								'value'    => 'no',
							),
						),
					),
					'wrapper'           => array(
						'width' => '',
						'class' => 'acf-hide-title',
						'id'    => '',
					),
					'hide_admin'        => 0,
					'message'           => __( '{dollie_tpl_domain_not_managed}', 'dollie' ),
					'new_lines'         => '',
					'esc_html'          => 0,
				),
				array(
					'key'               => 'field_61655e54d3940',
					'label'             => __( 'message (copy)', 'dollie' ),
					'name'              => '',
					'type'              => 'message',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'field_616554b36fd74',
								'operator' => '==',
								'value'    => 'yes',
							),
						),
					),
					'wrapper'           => array(
						'width' => '',
						'class' => 'acf-hide-title',
						'id'    => '',
					),
					'hide_admin'        => 0,
					'message'           => __( '{dollie_tpl_domain_managed}', 'dollie' ),
					'new_lines'         => '',
					'esc_html'          => 0,
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'af_form',
						'operator' => '==',
						'value'    => 'form_dollie_domain_connect',
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

	acf_add_local_field_group(
		array(
			'key'                   => 'group_5e833a614568d',
			'title'                 => '[Form] Delete Site',
			'fields'                => array(
				array(
					'key'               => 'field_5e8358f2d129b',
					'label'             => __( 'Confirm Site Name', 'dollie' ),
					'name'              => 'confirm_site_name',
					'type'              => 'text',
					'instructions'      => __( 'Please type the name of the site to confirm deletion, this can not be undone.', 'dollie' ),
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
					'maxlength'         => '',
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'af_form',
						'operator' => '==',
						'value'    => 'form_dollie_delete_site',
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

	acf_add_local_field_group(
		array(
			'key'                   => 'group_5e6a176c384ee',
			'title'                 => '[Form] Launch Site',
			'fields'                => array(
				array(
					'key'               => 'field_5e6a1773d54c4',
					'label'             => __( 'Choose Your URL', 'dollie' ),
					'name'              => 'site_url',
					'type'              => 'text',
					'instructions'      => __( 'Please choose a temporary URL for your site. This will be the place where you can work on your site used until you are ready to go live and connect your own domain.', 'dollie' ),
					'required'          => 1,
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
					'maxlength'         => '',
				),
				array(
					'key'               => 'field_601a8d9bc4b42',
					'label'             => __( 'Site Type', 'dollie' ),
					'name'              => 'site_type',
					'type'              => 'select',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => 'acf-hide-title acf-hidden',
						'id'    => '',
					),
					'hide_admin'        => 1,
					'choices'           => array(
						'site'      => 'site',
						'blueprint' => 'blueprint',
					),
					'default_value'     => 'site',
					'allow_null'        => 0,
					'multiple'          => 0,
					'ui'                => 0,
					'return_format'     => 'value',
					'ajax'              => 0,
					'placeholder'       => '',
				),
				array(
					'key'               => 'field_5e6a221a065b8',
					'label'             => __( 'Select a Blueprint (optional)', 'dollie' ),
					'name'              => 'site_blueprint',
					'type'              => 'radio',
					'instructions'      => __( 'Carefully crafted site designs made by our team which you can use as a starting point for your new site.', 'dollie' ),
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
					'choices'           => array(),
					'allow_null'        => 0,
					'other_choice'      => 0,
					'default_value'     => '',
					'layout'            => 'vertical',
					'return_format'     => 'value',
					'save_other_choice' => 0,
				),
				array(
					'key'               => 'field_5fb3b53ff7445',
					'label'             => __( 'Advanced Settings', 'dollie' ),
					'name'              => 'advanced_settings',
					'type'              => 'true_false',
					'instructions'      => __( 'Configure site details like default admin username and password.', 'dollie' ),
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
					'message'           => '',
					'default_value'     => 0,
					'ui'                => 1,
					'ui_on_text'        => '',
					'ui_off_text'       => '',
				),
				array(
					'key'               => 'field_5e6a1861b9025',
					'label'             => __( 'Admin Email', 'dollie' ),
					'name'              => 'site_admin_email',
					'type'              => 'email',
					'instructions'      => __( 'This is the email address you use to login to your WordPress admin.', 'dollie' ),
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
				),
				array(
					'key'               => 'field_620f5526c41eb',
					'label'             => __( 'Assign to Customer', 'dollie' ),
					'name'              => 'assign_to_customer',
					'type'              => 'user',
					'instructions'      => __( 'Directly link this new site to one of your existing customers after it\'s been launched.', 'dollie' ),
					'dollie_admin_only' => 1,
					'required'          => 0,
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'field_5fb3b53ff7445',
								'operator' => '==',
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
					'role'              => '',
					'allow_null'        => 0,
					'multiple'          => 0,
					'return_format'     => 'id',
				),
				array(
					'key'               => 'field_5e72a0bbba6a9',
					'label'             => __( 'Admin Username', 'dollie' ),
					'name'              => 'admin_username',
					'type'              => 'text',
					'instructions'      => __( 'The username you use to login to your WordPress admin.', 'dollie' ),
					'required'          => 0,
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'field_5fb3b53ff7445',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
					'wrapper'           => array(
						'width' => '50',
						'class' => '',
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
					'key'               => 'field_5e72a0f6ba6aa',
					'label'             => __( 'Admin Password', 'dollie' ),
					'name'              => 'admin_password',
					'type'              => 'password',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'field_5fb3b53ff7445',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
					'wrapper'           => array(
						'width' => '50',
						'class' => '',
						'id'    => '',
					),
					'hide_admin'        => 0,
					'placeholder'       => '',
					'prepend'           => '',
					'append'            => '',
				),
				array(
					'key'               => 'field_5e729de911000',
					'label'             => __( 'Site Name', 'dollie' ),
					'name'              => 'site_name',
					'type'              => 'text',
					'instructions'      => __( 'You can always change this later.', 'dollie' ),
					'required'          => 0,
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'field_5fb3b53ff7445',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
					'wrapper'           => array(
						'width' => '50',
						'class' => '',
						'id'    => '',
					),
					'hide_admin'        => 0,
					'default_value'     => 'My New Site',
					'placeholder'       => '',
					'prepend'           => '',
					'append'            => '',
					'maxlength'         => '',
				),
				array(
					'key'               => 'field_5e72a094ba6a8',
					'label'             => __( 'Site Description', 'dollie' ),
					'name'              => 'site_description',
					'type'              => 'text',
					'instructions'      => __( 'This is shown in several areas across your site. It\'s also shown in the visitors browser window title. You can always change it later.', 'dollie' ),
					'required'          => 0,
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'field_5fb3b53ff7445',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
					'wrapper'           => array(
						'width' => '50',
						'class' => '',
						'id'    => '',
					),
					'hide_admin'        => 0,
					'default_value'     => 'The best website in the world',
					'placeholder'       => '',
					'prepend'           => '',
					'append'            => '',
					'maxlength'         => '',
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'af_form',
						'operator' => '==',
						'value'    => 'form_dollie_launch_site',
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

	acf_add_local_field_group(
		array(
			'key'                   => 'group_5e7255fadcb82',
			'title'                 => '[Form] List Site Backups',
			'fields'                => array(
				array(
					'key'               => 'field_5e72562baf79a',
					'label'             => __( 'Available Backups', 'dollie' ),
					'name'              => 'site_backup',
					'type'              => 'radio',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'hide_admin'        => 0,
					'choices'           => array(),
					'allow_null'        => 0,
					'other_choice'      => 0,
					'default_value'     => '',
					'layout'            => 'vertical',
					'return_format'     => 'array',
					'save_other_choice' => 0,
				),
				array(
					'key'               => 'field_5e7256abaf79b',
					'label'             => __( 'What would you like to restore?', 'dollie' ),
					'name'              => 'what_to_restore',
					'type'              => 'select',
					'instructions'      => '',
					'required'          => 1,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'hide_admin'        => 0,
					'choices'           => array(
						'full'          => 'Everything (Files & Database)',
						'files-only'    => 'Files Only',
						'database-only' => 'Database Only',
					),
					'default_value'     => array(),
					'allow_null'        => 0,
					'multiple'          => 0,
					'ui'                => 0,
					'return_format'     => 'array',
					'ajax'              => 0,
					'placeholder'       => '',
				),
				array(
					'key'               => 'field_5e729515bb78b',
					'label'             => __( 'You are about to restore your site', 'dollie' ),
					'name'              => 'final_message',
					'type'              => 'calculated',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'hide_admin'        => 0,
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'af_form',
						'operator' => '==',
						'value'    => 'form_dollie_list_backups',
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

	acf_add_local_field_group(
		array(
			'key'                   => 'group_5e836154347c4',
			'title'                 => '[Form] Performance',
			'fields'                => array(
				array(
					'key'               => 'field_5e836164a3819',
					'label'             => __( 'Choose Your Caching Method', 'dollie' ),
					'name'              => 'caching_method',
					'type'              => 'radio',
					'instructions'      => __(
						'<strong>PoweredCache</strong> - Recommended. Control cache settings on the WordPress level via the PoweredCache page in your WP admin. <br>
<strong>WPRocket</strong> - Recommended if you\'re using WPRocket.',
						'dollie'
					),
					'required'          => 1,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'hide_admin'        => 0,
					'choices'           => array(
						'simple'   => 'PoweredCache',
						'wprocket' => 'WPRocket',
					),
					'allow_null'        => 0,
					'other_choice'      => 0,
					'default_value'     => '',
					'layout'            => 'vertical',
					'return_format'     => 'value',
					'save_other_choice' => 0,
				),
				array(
					'key'               => 'field_5e836202a381a',
					'label'             => __( 'PHP Version', 'dollie' ),
					'name'              => 'php_version',
					'type'              => 'radio',
					'instructions'      => '',
					'required'          => 1,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'hide_admin'        => 0,
					'choices'           => array(
						'php-7' => 'PHP 7.0',
						'php-5' => 'PHP 5.6',
					),
					'allow_null'        => 0,
					'other_choice'      => 0,
					'default_value'     => '',
					'layout'            => 'vertical',
					'return_format'     => 'value',
					'save_other_choice' => 0,
				),
				array(
					'key'               => 'field_5e836250a381b',
					'label'             => '',
					'name'              => '',
					'type'              => 'message',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'field_5e836202a381a',
								'operator' => '==',
								'value'    => 'php-7',
							),
						),
					),
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'hide_admin'        => 0,
					'message'           => __(
						'<div class="alert alert-danger">
Whilst PHP7 gives your site a big performance boost not all plugins and themes are yet compatible with PHP7. Please make sure to test your site functionality before you decide to use PHP7 </div>',
						'dollie'
					),
					'new_lines'         => '',
					'esc_html'          => 0,
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'af_form',
						'operator' => '==',
						'value'    => 'form_dollie_performance',
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

	acf_add_local_field_group(
		array(
			'key'                   => 'group_5e8300c978315',
			'title'                 => '[Form] Plugin Updates',
			'fields'                => array(
				array(
					'key'               => 'field_5e8300e4930f6',
					'label'             => __( 'Plugins to Update', 'dollie' ),
					'name'              => 'plugins_to_update',
					'type'              => 'checkbox',
					'instructions'      => '',
					'required'          => 1,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'hide_admin'        => 0,
					'choices'           => array(),
					'allow_custom'      => 0,
					'default_value'     => array(),
					'layout'            => 'vertical',
					'toggle'            => 0,
					'return_format'     => 'value',
					'save_custom'       => 0,
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'af_form',
						'operator' => '==',
						'value'    => 'form_dollie_plugin_updates',
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

	acf_add_local_field_group(
		array(
			'key'                   => 'group_5e85d59a48243',
			'title'                 => '[Form] Quick Launch',
			'fields'                => array(
				array(
					'key'               => 'field_5e85d5ab2410b',
					'label'             => __( 'Your Name', 'dollie' ),
					'name'              => 'client_name',
					'type'              => 'text',
					'instructions'      => __( 'Please enter your name', 'dollie' ),
					'required'          => 1,
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
					'maxlength'         => '',
				),
				array(
					'key'               => 'field_5e85d5b42410c',
					'label'             => __( 'Email', 'dollie' ),
					'name'              => 'client_email',
					'type'              => 'email',
					'instructions'      => __( 'We need your email to launch the site', 'dollie' ),
					'required'          => 1,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '50',
						'class' => '',
						'id'    => '',
					),
					'hide_admin'        => 0,
					'default_value'     => '',
					'placeholder'       => '',
					'prepend'           => '',
					'append'            => '',
				),
				array(
					'key'               => 'field_5e85d5ec2410d',
					'label'             => __( 'Password', 'dollie' ),
					'name'              => 'client_password',
					'type'              => 'text',
					'instructions'      => __( 'Set a password to also create an account.', 'dollie' ),
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '50',
						'class' => '',
						'id'    => '',
					),
					'hide_admin'        => 0,
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
						'param'    => 'af_form',
						'operator' => '==',
						'value'    => 'form_dollie_quick_launch',
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

	acf_add_local_field_group(
		array(
			'key'                   => 'group_626681d4e39e8',
			'title'                 => 'Agency Onboarding',
			'fields'                => array(
				array(
					'key'               => 'field_6267d55edab0a',
					'label'             => __( 'About Your Agency', 'dollie' ),
					'name'              => '',
					'type'              => 'message',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => 'dollie-field-break',
						'id'    => '',
					),
					'dollie_admin_only' => 0,
					'hide_admin'        => 0,
					'message'           => '',
					'new_lines'         => 'wpautop',
					'esc_html'          => 0,
				),
				array(
					'key'               => 'field_6267df499bce9',
					'label'             => __( 'What is your Agency called?', 'dollie' ),
					'name'              => 'wpd_onboarding_partner_business_name',
					'type'              => 'text',
					'instructions'      => __( 'We only use this info inside your dashboard for customisation.', 'dollie' ),
					'required'          => 1,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'dollie_admin_only' => 0,
					'hide_admin'        => 0,
					'default_value'     => '',
					'placeholder'       => '',
					'prepend'           => '',
					'append'            => '',
					'maxlength'         => '',
				),
				array(
					'key'               => 'field_626682f91629b',
					'label'             => __( 'Does your Agency have a boilerplate for new client projects?', 'dollie' ),
					'name'              => 'wpd_onboarding_enable_blueprint',
					'type'              => 'true_false',
					'instructions'      => __( 'Does your Agency have a suite of standard plugins and themes you use for every new client project?', 'dollie' ),
					'required'          => 1,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'dollie_admin_only' => 0,
					'hide_admin'        => 0,
					'message'           => '',
					'default_value'     => 0,
					'ui'                => 1,
					'ui_on_text'        => 'Yes',
					'ui_off_text'       => 'No',
				),
				array(
					'key'               => 'field_626684951629c',
					'label'             => __( 'Do you manage everything for your clients, or do (some of them) also work on their own sites?', 'dollie' ),
					'name'              => 'wpd_onboarding_developer_tools',
					'type'              => 'radio',
					'instructions'      => __( 'With Dollie you can enable powerful self-service features for your clients that gives them easy access to tools like SFTP, a Code Editor, Database Manager and DNS management. You can enable these tools for them easily, or decide to keep them only available for your team.', 'dollie' ),
					'required'          => 1,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'dollie_admin_only' => 0,
					'hide_admin'        => 0,
					'choices'           => array(
						'white-glove'  => 'We do everything for the client, they don\'t use developer tools.',
						'self-service' => 'Our clients use some developer tools themselves to work on their site.',
					),
					'allow_null'        => 0,
					'other_choice'      => 0,
					'default_value'     => '',
					'layout'            => 'vertical',
					'return_format'     => 'value',
					'save_other_choice' => 0,
				),
				array(
					'key'               => 'field_6267bb31934d0',
					'label'             => __( 'Import Example Content', 'dollie' ),
					'name'              => '',
					'type'              => 'message',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => 'dollie-field-break',
						'id'    => '',
					),
					'dollie_admin_only' => 0,
					'hide_admin'        => 0,
					'message'           => '',
					'new_lines'         => 'wpautop',
					'esc_html'          => 0,
				),
				array(
					'key'               => 'field_6267bb63934d1',
					'label'             => __( 'Dollie Core Pages', 'dollie' ),
					'name'              => 'wpd_onboarding_core_pages',
					'type'              => 'true_false',
					'instructions'      => __( 'The core Dollie pages.', 'dollie' ),
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => 'dollie-field-small',
						'id'    => '',
					),
					'dollie_admin_only' => 0,
					'hide_admin'        => 0,
					'message'           => '',
					'default_value'     => 1,
					'ui'                => 1,
					'ui_on_text'        => '',
					'ui_off_text'       => '',
				),
				array(
					'key'               => 'field_6267ba5558c2d',
					'label'             => __( 'WooCommerce Products', 'dollie' ),
					'name'              => 'wpd_onboarding_example_products',
					'type'              => 'true_false',
					'instructions'      => __( 'Some example WooCommerce products configured for Dollie.', 'dollie' ),
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => 'dollie-field-small',
						'id'    => '',
					),
					'dollie_admin_only' => 0,
					'hide_admin'        => 0,
					'message'           => '',
					'default_value'     => 1,
					'ui'                => 1,
					'ui_on_text'        => '',
					'ui_off_text'       => '',
				),
				array(
					'key'               => 'field_6267bac558c2e',
					'label'             => __( 'Landing Pages', 'dollie' ),
					'name'              => 'wpd_onboarding_example_landing',
					'type'              => 'true_false',
					'instructions'      => __( 'Some example Elementor landing pages to promote your Agency services', 'dollie' ),
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => 'dollie-field-small',
						'id'    => '',
					),
					'dollie_admin_only' => 0,
					'hide_admin'        => 0,
					'message'           => '',
					'default_value'     => 1,
					'ui'                => 1,
					'ui_on_text'        => '',
					'ui_off_text'       => '',
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'af_form',
						'operator' => '==',
						'value'    => 'form_dollie_agency_onboarding',
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
			'show_in_rest'          => 0,
		)
	);

endif;
