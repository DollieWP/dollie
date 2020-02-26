<?php if (function_exists('acf_add_local_field_group')) :

	acf_add_local_field_group(array(
		'key' => 'group_5ada1549129fb',
		'title' => 'Dollie',
		'fields' => array(
			array(
				'key' => 'field_5adc6aca0a0b4',
				'label' => 'API Setup',
				'name' => '',
				'type' => 'tab',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'placement' => 'top',
				'endpoint' => 0,
			),
			array(
				'key' => 'field_5e2eb1ca86e3c',
				'label' => 'Status',
				'name' => 'wpd_dollie_status',
				'type' => 'true_false',
				'instructions' => 'In staging you can test out Dollie as long as you\'d like. <br>Ready to go live with Dollie? Make sure you have completed the <a href="https://partners.getdollie.com/go-live" target="_blank" >partner onboarding process </a> and our white glove onboarding team will provide you with the right settings. <br><strong>Making changes to these settings before you\'ve gone live will lead to failed deployments.</strong>',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'default_value' => 0,
				'ui' => 1,
				'ui_on_text' => 'Live',
				'ui_off_text' => 'Staging',
			),
			array(
				'key' => 'field_5ada15d5bc584',
				'label' => 'Email',
				'name' => 'wpd_api_email',
				'type' => 'text',
				'instructions' => 'The email address belonging to your Dollie admin account',
				'required' => 1,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_5e2eb1ca86e3c',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
			),
			array(
				'key' => 'field_5ada1676bc585',
				'label' => 'Password',
				'name' => 'wpd_api_password',
				'type' => 'password',
				'instructions' => 'The password belonging to your Dollie admin account.',
				'required' => 1,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_5e2eb1ca86e3c',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
			),
			array(
				'key' => 'field_5cf62cee6f366',
				'label' => 'Your Domain',
				'name' => 'wpd_api_domain',
				'type' => 'url',
				'instructions' => 'Which custom domain are you using for the sites deployed by you or your customers?',
				'required' => 1,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_5e2eb1ca86e3c',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => 'dollie.io',
				'placeholder' => '',
			),
			array(
				'key' => 'field_5ada182522c80',
				'label' => 'Custom Dollie Instance (advanced)',
				'name' => 'wpd_api_dashboard_url',
				'type' => 'url',
				'instructions' => 'The link to your custom dashboard (enterprise only)',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_5e2eb1ca86e3c',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'placeholder' => '',
			),
			array(
				'key' => 'field_5e5557cda4c7b',
				'label' => 'Page Setup',
				'name' => '',
				'type' => 'tab',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'placement' => 'top',
				'endpoint' => 0,
			),
			array(
				'key' => 'field_5e5557eda4c7c',
				'label' => 'Launch Site Page',
				'name' => 'wpd_launch_page_id',
				'type' => 'post_object',
				'instructions' => 'From which page do your customers launch their sites?',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'post_type' => array(
					0 => 'page',
				),
				'taxonomy' => '',
				'allow_null' => 0,
				'multiple' => 0,
				'return_format' => 'id',
				'ui' => 1,
			),
			array(
				'key' => 'field_5e555885a4c7e',
				'label' => 'Customer Dashboard Page',
				'name' => 'wpd_dashboard_page_id',
				'type' => 'post_object',
				'instructions' => 'From which page do your customers launch their sites?',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'post_type' => array(
					0 => 'page',
				),
				'taxonomy' => '',
				'allow_null' => 0,
				'multiple' => 0,
				'return_format' => 'id',
				'ui' => 1,
			),
			array(
				'key' => 'field_5e555864a4c7d',
				'label' => 'Customer Login Page',
				'name' => 'wpd_login_page_id',
				'type' => 'post_object',
				'instructions' => 'From which page do your customers launch their sites?',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'post_type' => array(
					0 => 'page',
				),
				'taxonomy' => '',
				'allow_null' => 0,
				'multiple' => 0,
				'return_format' => 'id',
				'ui' => 1,
			),
			array(
				'key' => 'field_5adc6af60a0b5',
				'label' => 'Branding',
				'name' => '',
				'type' => 'tab',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'placement' => 'top',
				'endpoint' => 0,
			),
			array(
				'key' => 'field_5adc7193ac968',
				'label' => 'Dashboard Logo',
				'name' => 'wpd_dashboard_logo',
				'type' => 'image',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '50',
					'class' => '',
					'id' => '',
				),
				'return_format' => 'url',
				'preview_size' => 'medium',
				'library' => 'all',
				'min_width' => '',
				'min_height' => '',
				'min_size' => '',
				'max_width' => '',
				'max_height' => '',
				'max_size' => '',
				'mime_types' => '',
			),
			array(
				'key' => 'field_5afd4d2021885',
				'label' => 'Dashboard Logo Inversed',
				'name' => 'wpd_dashboard_logo_inversed',
				'type' => 'image',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '50',
					'class' => '',
					'id' => '',
				),
				'return_format' => 'url',
				'preview_size' => 'medium',
				'library' => 'all',
				'min_width' => '',
				'min_height' => '',
				'min_size' => '',
				'max_width' => '',
				'max_height' => '',
				'max_size' => '',
				'mime_types' => '',
			),
			array(
				'key' => 'field_5adc6e0a0a0b8',
				'label' => 'Header Style',
				'name' => 'wpd_header_style',
				'type' => 'radio',
				'instructions' => 'Choose a pre-designed theme you\'d like to use',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'choices' => array(
					'glass-inverse' => 'Default',
					'modern' => 'Elegance',
					'inverse' => 'Inverse',
					'glass' => 'Glass',
				),
				'allow_null' => 1,
				'other_choice' => 0,
				'save_other_choice' => 0,
				'default_value' => 'glass-inverse',
				'layout' => 'vertical',
				'return_format' => 'value',
			),
			array(
				'key' => 'field_5adc6ce10a0b7',
				'label' => 'Sidebar Style',
				'name' => 'wpd_sidebar_style',
				'type' => 'true_false',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'default_value' => 0,
				'ui' => 1,
				'ui_on_text' => 'Inversed Sidebar',
				'ui_off_text' => 'Regular Sidebar',
			),
			array(
				'key' => 'field_5adc6b010a0b6',
				'label' => 'Theme Preset',
				'name' => 'wpd_theme',
				'type' => 'radio',
				'instructions' => 'Choose a pre-designed theme you\'d like to use',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'choices' => array(
					'default' => 'Default',
					'elegance' => 'Elegance',
					'pulse' => 'Pulse',
					'flat' => 'Flat',
					'corporate' => 'Corporate',
					'earth' => 'Earth',
				),
				'allow_null' => 1,
				'other_choice' => 0,
				'save_other_choice' => 0,
				'default_value' => '',
				'layout' => 'vertical',
				'return_format' => 'value',
			),
			array(
				'key' => 'field_583b2f85ff318',
				'label' => 'Brand Color',
				'name' => 'brand_color',
				'type' => 'color_picker',
				'instructions' => 'You can set a branding color that fits your business or product.',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '#4CA2BA',
			),
			array(
				'key' => 'field_5adf1b015c888',
				'label' => 'Newsfeed URL',
				'name' => 'wpd_feed_url',
				'type' => 'url',
				'instructions' => 'The link to the WordPress installation you want to show news from.',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'placeholder' => 'https://yoursite.com',
			),
			array(
				'key' => 'field_583b2eeaf884d',
				'label' => 'Default Screenshot Placeholder',
				'name' => 'default_screenshot',
				'type' => 'image',
				'instructions' => '<strong>Recommended size: 1200x900px</strong><br>
This is the default screenshot image shown on the site details header. It is only shown when the site owner has not set up their site yet.',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'return_format' => 'array',
				'preview_size' => 'medium',
				'library' => 'all',
				'min_width' => '',
				'min_height' => '',
				'min_size' => '',
				'max_width' => '',
				'max_height' => '',
				'max_size' => '',
				'mime_types' => '',
			),
			array(
				'key' => 'field_583b39c4b0648',
				'label' => 'Header Background Image',
				'name' => 'site_details_bg',
				'type' => 'image',
				'instructions' => 'This is the default background image shown inside the headers. We recommend to upload a scalable SVG pattern',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'return_format' => 'url',
				'preview_size' => 'thumbnail',
				'library' => 'all',
				'min_width' => '',
				'min_height' => '',
				'min_size' => '',
				'max_width' => '',
				'max_height' => '',
				'max_size' => '',
				'mime_types' => 'png, jpg,svg',
			),
			array(
				'key' => 'field_5b06a979537b3',
				'label' => 'Access Control',
				'name' => '',
				'type' => 'tab',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'placement' => 'top',
				'endpoint' => 0,
			),
			array(
				'key' => 'field_5b094ea3fe6ea',
				'label' => 'Enable Site Management Access?',
				'name' => 'wpd_allow_site_dashboard_access',
				'type' => 'true_false',
				'instructions' => 'Would you like to give your clients/customers the ability to manage their own sites or would you like to restrict this functionality to your team (Administrators) only?',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'default_value' => 1,
				'ui' => 1,
				'ui_on_text' => 'Yes',
				'ui_off_text' => 'No',
			),
			array(
				'key' => 'field_5b06c0124f11a',
				'label' => 'Available Site Management Features',
				'name' => 'available_sections',
				'type' => 'checkbox',
				'instructions' => 'Depending on the customers you bring in, you can choose to disable certain sections of the customer dashboard.',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_5b094ea3fe6ea',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'choices' => array(
					'dashboard' => 'Dashboard',
					'backups' => 'Backups',
					'updates' => 'Updates',
					'developers' => 'Developer Tools',
					'blueprint' => 'Blueprints',
					'domain' => 'Domains',
					'delete-site' => 'Site Deletion',
				),
				'allow_custom' => 0,
				'default_value' => array(
					0 => 'dashboard',
					1 => 'backups',
					2 => 'updates',
					3 => 'developers',
					4 => 'blueprint',
					5 => 'domain',
					6 => 'delete-site',
				),
				'layout' => 'vertical',
				'toggle' => 0,
				'return_format' => 'array',
				'save_custom' => 0,
			),
			array(
				'key' => 'field_583b2ff20aaad',
				'label' => 'Available Site Management Tabs',
				'name' => 'available_features',
				'type' => 'checkbox',
				'instructions' => 'Depending on the customers you bring in, you can choose to disable certain sections of the customer dashboard.',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_5b094ea3fe6ea',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'choices' => array(
					'plugins' => 'Plugins',
					'themes' => 'Themes',
					'emails' => 'Emails',
					'domains' => 'Domains',
					'cloudflare' => 'CloudFlare',
					'recommendations' => 'Recommendations',
				),
				'allow_custom' => 0,
				'save_custom' => 0,
				'default_value' => array(
					0 => 'plugins',
					1 => 'themes',
					2 => 'emails',
					3 => 'domains',
					4 => 'cloudflare',
					5 => 'recommendations',
				),
				'layout' => 'vertical',
				'toggle' => 0,
				'return_format' => 'array',
			),
			array(
				'key' => 'field_58861a3cc49b2',
				'label' => 'Available Developer Tabs',
				'name' => 'available_features_developers',
				'type' => 'checkbox',
				'instructions' => 'Depending on the customers you bring in, you can choose to disable certain sections of the developer dashboard.',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_5b094ea3fe6ea',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'choices' => array(
					'codiad' => 'Code Editor',
					'adminer' => 'Database',
					'shell' => 'WP-CLI',
					'performance' => 'Performance',
					'cloudflare' => 'CloudFlare',
					'php-debug' => 'WP Debug',
				),
				'allow_custom' => 0,
				'save_custom' => 0,
				'default_value' => array(
					0 => 'codiad',
					1 => 'adminer',
					2 => 'shell',
					3 => 'performance',
					4 => 'cloudflare',
					5 => 'tools',
					6 => 'php-debug',
				),
				'layout' => 'vertical',
				'toggle' => 0,
				'return_format' => 'array',
			),
			array(
				'key' => 'field_586f9fb413fd3',
				'label' => 'Help Scout Beacon ID',
				'name' => 'help_scout_beacon_id',
				'type' => 'text',
				'instructions' => 'If you are using the Help Scout Docs you can enable a Beacon to provide your customers with easy access to your knowledge base articles.',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_586f9f2513fd2',
							'operator' => '==',
							'value' => 'helpscout',
						),
					),
				),
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
			),
			array(
				'key' => 'field_5b094d86fe6e8',
				'label' => 'Blueprints',
				'name' => '',
				'type' => 'tab',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'placement' => 'top',
				'endpoint' => 0,
			),
			array(
				'key' => 'field_5b097b1dd50cc',
				'label' => 'Enable Site Preview',
				'name' => 'wpd_enable_site_preview',
				'type' => 'true_false',
				'instructions' => 'The site preview bar allows you to quickly showcase the available Blueprints you\'ve created. If you\'d like to enable this feature make sure you\'ve uploaded the Site Preview script to your root directory.',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'default_value' => 1,
				'ui' => 1,
				'ui_on_text' => 'Yes',
				'ui_off_text' => 'No',
			),
			array(
				'key' => 'field_5b094db7fe6e9',
				'label' => 'Site Preview Script Location',
				'name' => 'wpd_site_preview_url',
				'type' => 'url',
				'instructions' => 'The link to where your site preview script is hosted. By default the yoursite.com/preview path is used.',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'placeholder' => 'http://yoursite.com/preview',
			),
			array(
				'key' => 'field_5cb5cca213aae',
				'label' => 'Email Delivery',
				'name' => '',
				'type' => 'tab',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'placement' => 'top',
				'endpoint' => 0,
			),
			array(
				'key' => 'field_5cb5cccd13aaf',
				'label' => 'Email Delivery Address',
				'name' => 'wpd_delivery_email',
				'type' => 'text',
				'instructions' => 'The default transactional email address that\'s being used by the WordPress installation of your customers. Make sure this is properly set up and tested with SendGrid.',
				'required' => 1,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'placeholder' => 'notifications@yourdomain.com',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
			),
			array(
				'key' => 'field_5cb5cee44bc8e',
				'label' => 'SMTP Host',
				'name' => 'wpd_delivery_smtp_host',
				'type' => 'text',
				'instructions' => 'The default transactional email address that\'s being used by the WordPress installation of your customers. Make sure this is properly set up and tested with SendGrid.',
				'required' => 1,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => 'smtp.sendgrid.net',
				'placeholder' => 'smtp.sendgrid.net',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
			),
			array(
				'key' => 'field_5cb5cd6013ab0',
				'label' => 'SMTP Port',
				'name' => 'wpd_delivery_smtp',
				'type' => 'text',
				'instructions' => 'The port being used for SMTP delivery.',
				'required' => 1,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => 2525,
				'placeholder' => 'notifications@yourdomain.com',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
			),
			array(
				'key' => 'field_5cb5cd9413ab1',
				'label' => 'SendGrid Username',
				'name' => 'wpd_delivery_username',
				'type' => 'text',
				'instructions' => 'Your SendGrid username',
				'required' => 1,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
			),
			array(
				'key' => 'field_5cb5cda513ab2',
				'label' => 'API Key / Password',
				'name' => 'wpd_delivery_password',
				'type' => 'password',
				'instructions' => 'The password belonging to your SendGrid API account.',
				'required' => 1,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'readonly' => 0,
				'disabled' => 0,
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'options_page',
					'operator' => '==',
					'value' => 'wpd_platform_setup',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => true,
		'description' => '',
	));

endif;
