<?php
if( function_exists('acf_add_local_field_group') ):

	acf_add_local_field_group(array(
		'key' => 'group_5e8243b7b70c4',
		'title' => '[Form] [Domain] Connect',
		'fields' => array(
			array(
				'key' => 'field_5e847fa49d718',
				'label' => 'Intro message',
				'name' => '',
				'type' => 'message',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => 'acf-hide-title',
					'id' => '',
				),
				'hide_admin' => 0,
				'message' => '[dollie_blockquote type="success" icon="fa fa-globe" title="Let\'s link up your custom domain {dollie_user_display_name}!"]
We\'ll walk you through all the steps required to link your own domain to your site. Let\'s get started shall we?
[/dollie_blockquote]',
				'new_lines' => '',
				'esc_html' => 0,
			),
			array(
				'key' => 'field_5e824503392c5',
				'label' => 'Have you registered a domain name?',
				'name' => 'is_domain_registered',
				'type' => 'radio',
				'instructions' => '',
				'required' => 1,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'hide_admin' => 0,
				'choices' => array(
					'yes' => 'Yes, I have registered a domain',
					'no' => 'No, I still need a domain name',
				),
				'allow_null' => 0,
				'other_choice' => 0,
				'default_value' => '',
				'layout' => 'vertical',
				'return_format' => 'value',
				'save_other_choice' => 0,
			),
			array(
				'key' => 'field_5e82459f392c6',
				'label' => 'Does this domain have an (active) website linked to it?',
				'name' => 'has_active_site',
				'type' => 'radio',
				'instructions' => 'Are you currently using this domain to host a website?',
				'required' => 1,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_5e824503392c5',
							'operator' => '==',
							'value' => 'yes',
						),
					),
				),
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'hide_admin' => 0,
				'choices' => array(
					'no' => 'No, it\'s a new domain registered for this site!',
					'yes_live' => 'Yes, it\'s a "live site" with content already being present.',
					'yes_ready' => 'Yes, but there is nothing important there, I\'m ready to point it to this site!',
				),
				'allow_null' => 0,
				'other_choice' => 0,
				'default_value' => '',
				'layout' => 'vertical',
				'return_format' => 'value',
				'save_other_choice' => 0,
			),
			array(
				'key' => 'field_5e824686392c8',
				'label' => 'Migration Instructions',
				'name' => '',
				'type' => 'message',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_5e82459f392c6',
							'operator' => '==',
							'value' => 'yes_live',
						),
					),
				),
				'wrapper' => array(
					'width' => '',
					'class' => 'acf-hide-title',
					'id' => '',
				),
				'hide_admin' => 0,
				'message' => '{dollie_migration_instructions}',
				'new_lines' => '',
				'esc_html' => 0,
			),
			array(
				'key' => 'field_5e82fbd6e3032',
				'label' => 'Have you completed the data migration?',
				'name' => 'is_data_moved',
				'type' => 'radio',
				'instructions' => '',
				'required' => 1,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_5e82459f392c6',
							'operator' => '==',
							'value' => 'yes_live',
						),
					),
				),
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'hide_admin' => 0,
				'choices' => array(
					'yes' => 'Yes, I\'ve done this successfully!',
					'no' => 'No, I have not done this yet.',
				),
				'allow_null' => 1,
				'other_choice' => 0,
				'default_value' => '',
				'layout' => 'vertical',
				'return_format' => 'value',
				'save_other_choice' => 0,
			),
			array(
				'key' => 'field_5e82fc3ee3033',
				'label' => 'Great! So here\'s what you need to know',
				'name' => '',
				'type' => 'message',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_5e82fbd6e3032',
							'operator' => '==',
							'value' => 'yes',
						),
					),
				),
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'hide_admin' => 0,
				'message' => '<p class="font-size-smaller box-brand-primary lighten padding-small">
				1. Do not worry about new content/files that were added after you have done the migration. We will get to this later.<br>
				2. Please keep the Migration Plugin installed and active on your current live website.<br>
				3. At a later stage during this wizard we will sync your existing content/database one final time.
</p>',
				'new_lines' => '',
				'esc_html' => 0,
			),
			array(
				'key' => 'field_5e82fcb1e3034',
				'label' => 'Migration issues',
				'name' => '',
				'type' => 'message',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_5e82fbd6e3032',
							'operator' => '==',
							'value' => 'no',
						),
					),
				),
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'hide_admin' => 0,
				'message' => 'If you have encounter problems please stop this wizard and reach our <a href="{dollie_support_link}">support team</a> so we can help.',
				'new_lines' => '',
				'esc_html' => 0,
			),
			array(
				'key' => 'field_5e82fd0de3035',
				'label' => 'Register Your Domain Name',
				'name' => '',
				'type' => 'message',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_5e824503392c5',
							'operator' => '==',
							'value' => 'no',
						),
					),
				),
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'hide_admin' => 0,
				'message' => '<div class="box-light padding-half">
<p>We are not selling domains directly, all our efforts go to perfecting WordPress site hosting.</p> 
If you have not registered your own domain yet, this is the time to do so! We recommend <a href="https://wefoster.co/go/namecheap/" target="_blank">NameCheap</a> because of their easy to use domain manager and very low prices, but you are free to choose any other domain registrar. 
<strong>Go ahead, register your domain and come back to this form to continue the domain setup!</strong>
</div>',
				'new_lines' => '',
				'esc_html' => 0,
			),
			array(
				'key' => 'field_5e82fd44e3036',
				'label' => 'Registered Your New Domain?',
				'name' => 'is_new_domain_registered',
				'type' => 'true_false',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_5e824503392c5',
							'operator' => '==',
							'value' => 'no',
						),
					),
				),
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'hide_admin' => 0,
				'message' => 'Yes, I have registered my domain!',
				'default_value' => 0,
				'ui' => 0,
				'ui_on_text' => '',
				'ui_off_text' => '',
			),
			array(
				'key' => 'field_5e82fd8ee3037',
				'label' => 'Your Domain Name',
				'name' => 'domain_name',
				'type' => 'text',
				'instructions' => 'Please type your domain name <strong>without www. or http(s)://</strong>',
				'required' => 1,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_5e824503392c5',
							'operator' => '==',
							'value' => 'yes',
						),
					),
					array(
						array(
							'field' => 'field_5e82fd44e3036',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'wrapper' => array(
					'width' => '50',
					'class' => '',
					'id' => '',
				),
				'hide_admin' => 0,
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
			),
			array(
				'key' => 'field_5e82fdefe303a',
				'label' => 'Confirm Your Domain',
				'name' => 'confirm_domain_name',
				'type' => 'text',
				'instructions' => 'Please retype your domain name.',
				'required' => 1,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_5e824503392c5',
							'operator' => '==',
							'value' => 'yes',
						),
					),
					array(
						array(
							'field' => 'field_5e82fd44e3036',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'wrapper' => array(
					'width' => '50',
					'class' => '',
					'id' => '',
				),
				'hide_admin' => 0,
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'af_form',
					'operator' => '==',
					'value' => 'form_dollie_domain_connect',
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

	acf_add_local_field_group(array(
		'key' => 'group_5e8f3c854b006',
		'title' => '[Form] [Domain] DNS & SSL',
		'fields' => array(
			array(
				'key' => 'field_5e9764c6208eb',
				'label' => 'Domain',
				'name' => '',
				'type' => 'page',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'hide_admin' => true,
				'show_numbering' => 1,
				'previous_text' => '',
				'next_text' => '',
			),
			array(
				'key' => 'field_5e9066a2e2677',
				'label' => 'DNS Instructions',
				'name' => '',
				'type' => 'message',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => 'acf-hide-title',
					'id' => '',
				),
				'hide_admin' => 0,
				'message' => '{dollie_tpl_link_domain}',
				'new_lines' => '',
				'esc_html' => 0,
			),
			array(
				'key' => 'field_5e975b2e00edc',
				'label' => 'Did you make the required DNS change?',
				'name' => 'is_dns_changed',
				'type' => 'radio',
				'instructions' => '',
				'required' => 1,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'hide_admin' => 0,
				'choices' => array(
					'Yes' => 'Yes',
					'No, I\'m unsure what to do...' => 'No, I\'m unsure what to do...',
				),
				'allow_null' => 1,
				'other_choice' => 0,
				'default_value' => '',
				'layout' => 'vertical',
				'return_format' => 'value',
				'save_other_choice' => 0,
			),
			array(
				'key' => 'field_5e975c5800ede',
				'label' => 'DNS Check',
				'name' => '',
				'type' => 'message',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_5e975b2e00edc',
							'operator' => '==',
							'value' => 'Yes',
						),
					),
				),
				'wrapper' => array(
					'width' => '',
					'class' => 'acf-hide-title',
					'id' => '',
				),
				'hide_admin' => 0,
				'message' => '<div class="box-light padding-half">
<h3>Great job!</h3>
 This usually takes anywhere between a couple of minutes to one hour to propagate over the internet. Now please wait a few minutes and you can click to button to complete DNS Setup.
</div>',
				'new_lines' => '',
				'esc_html' => 0,
			),
			array(
				'key' => 'field_5e975bfe00edd',
				'label' => 'Support Request',
				'name' => '',
				'type' => 'message',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_5e975b2e00edc',
							'operator' => '==',
							'value' => 'No, I\'m unsure what to do...',
						),
					),
				),
				'wrapper' => array(
					'width' => '',
					'class' => 'acf-hide-title',
					'id' => '',
				),
				'hide_admin' => 0,
				'message' => '<div class="box-light padding-half">
<h3>No worries , we\'re here to help!</h3>

Please create a support ticket so our team can assist you in making the required DNS changes. In order for us to do this, you need to give us temporary access to your CloudFlare account. Please include these details with your support ticket.

<a href="{dollie_support_link}">Create A Support Ticket</a>
</div>',
				'new_lines' => '',
				'esc_html' => 0,
			),
			array(
				'key' => 'field_5e9764d3208ec',
				'label' => 'SSL',
				'name' => '',
				'type' => 'page',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'hide_admin' => true,
				'show_numbering' => 1,
				'previous_text' => '',
				'next_text' => '',
			),
			array(
				'key' => 'field_5e976050068d4',
				'label' => 'How would you like to set up your SSL certificate?',
				'name' => 'ssl_certificate_type',
				'type' => 'radio',
				'instructions' => 'Having a SSL certificate is crucial for any site for security and SEO. Luckily we make it extremely easy for you!',
				'required' => 1,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'hide_admin' => 0,
				'choices' => array(
					'letsencrypt' => 'Automatically generate a free LetsEncrypt certificate',
					'cloudflare' => 'Use my CloudFlare account',
				),
				'allow_null' => 0,
				'other_choice' => 0,
				'default_value' => '',
				'layout' => 'vertical',
				'return_format' => 'value',
				'save_other_choice' => 0,
			),
			array(
				'key' => 'field_5e9760d33619c',
				'label' => 'CloudFlare Email',
				'name' => 'cloudflare_email',
				'type' => 'email',
				'instructions' => 'The email you use to login to your CloudFlare account.',
				'required' => 1,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_5e976050068d4',
							'operator' => '==',
							'value' => 'cloudflare',
						),
					),
				),
				'wrapper' => array(
					'width' => '50',
					'class' => '',
					'id' => '',
				),
				'hide_admin' => 0,
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
			),
			array(
				'key' => 'field_5e9760fc3619d',
				'label' => 'CloudFlare API Key',
				'name' => 'cloudflare_api_key',
				'type' => 'text',
				'instructions' => 'See the instructions below to see how you can find your CloudFlare API key.',
				'required' => 1,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_5e976050068d4',
							'operator' => '==',
							'value' => 'cloudflare',
						),
					),
				),
				'wrapper' => array(
					'width' => '50',
					'class' => '',
					'id' => '',
				),
				'hide_admin' => 0,
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
			),
			array(
				'key' => 'field_5e9761323619e',
				'label' => 'CloudFlare API Key Instructions',
				'name' => '',
				'type' => 'message',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_5e976050068d4',
							'operator' => '==',
							'value' => 'cloudflare',
						),
					),
				),
				'wrapper' => array(
					'width' => '',
					'class' => 'acf-hide-title',
					'id' => '',
				),
				'hide_admin' => 0,
				'message' => '<div class="box-light padding-half mt-4">
<h4>Finding Your CloudFlare API Key</h4>
<a target="_blank" href="https://support.cloudflare.com/hc/en-us/articles/200167836-Where-do-I-find-my-Cloudflare-API-key-">Please visit the following page to find instructions on getting your CloudFlare API key</a>
</div>',
				'new_lines' => '',
				'esc_html' => 0,
			),
			array(
				'key' => 'field_5e97635277fcb',
				'label' => 'Your CloudFlare Zone ID',
				'name' => 'cloudflare_zone_id',
				'type' => 'text',
				'instructions' => 'To enable Site Analytics paste in your Zone ID in the field below. See the instructions below for more info.',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_5e976050068d4',
							'operator' => '==',
							'value' => 'cloudflare',
						),
					),
				),
				'wrapper' => array(
					'width' => '50',
					'class' => '',
					'id' => '',
				),
				'hide_admin' => 0,
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
			),
			array(
				'key' => 'field_5e97637c77fcd',
				'label' => 'CloudFlare Zone ID Instructions',
				'name' => '',
				'type' => 'message',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_5e976050068d4',
							'operator' => '==',
							'value' => 'cloudflare',
						),
					),
				),
				'wrapper' => array(
					'width' => '',
					'class' => 'acf-hide-title',
					'id' => '',
				),
				'hide_admin' => 0,
				'message' => '<div class="box-light padding-half">
<h4>Finding Your Domains Zone ID</h4>

<ol>
<li>Login to your CloudFlare account</li>
<li>Click on the Domain Name belonging your site</li>
<li>Scroll down to the <strong>Domain Summary</strong> section and copy and paste the Zone ID.
</ol>
<img class="wysiwyg-text-align-center" src="/wp-content/plugins/dollie/assets/img/cloudflare-zone-id.png" alt="Cloudflare zone id" width="50%" height="auto">
</div>',
				'new_lines' => '',
				'esc_html' => 0,
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'af_form',
					'operator' => '==',
					'value' => 'form_dollie_domain_dns_ssl',
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

	acf_add_local_field_group(array(
		'key' => 'group_5e8f3ec547353',
		'title' => '[Form] [Domain] Update URL',
		'fields' => array(
			array(
				'key' => 'field_5e982b4528956',
				'label' => 'Site URL',
				'name' => '',
				'type' => 'page',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'hide_admin' => true,
				'show_numbering' => 1,
				'previous_text' => '',
				'next_text' => '',
			),
			array(
				'key' => 'field_5e84b1a8dd383',
				'label' => 'Intro Message',
				'name' => '',
				'type' => 'message',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => 'acf-hide-title',
					'id' => '',
				),
				'hide_admin' => 0,
				'message' => '[dollie_blockquote title="Last step to complete domain setup"]
Let\'s continue with our domain wizard by changing the url on your new site.
[/dollie_blockquote]',
				'new_lines' => '',
				'esc_html' => 0,
			),
			array(
				'key' => 'field_5e84b1d8512de',
				'label' => 'Do you want to have www. in the URL?',
				'name' => 'domain_with_www',
				'type' => 'radio',
				'instructions' => 'When you visit your domain do you want it to show <strong>www.</strong> in the address bar?',
				'required' => 1,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'hide_admin' => 0,
				'choices' => array(
					'yes' => 'Yes',
					'no' => 'No',
					'unknown' => 'I don\'t know...',
				),
				'allow_null' => 1,
				'other_choice' => 0,
				'default_value' => '',
				'layout' => 'vertical',
				'return_format' => 'value',
				'save_other_choice' => 0,
			),
			array(
				'key' => 'field_5e84b21d512df',
				'label' => 'Unsure www domain',
				'name' => '',
				'type' => 'message',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_5e84b1d8512de',
							'operator' => '==',
							'value' => 'unknown',
						),
					),
				),
				'wrapper' => array(
					'width' => '',
					'class' => 'acf-hide-title',
					'id' => '',
				),
				'hide_admin' => 0,
				'message' => '<div class="box-light padding-half">
<h4>No problemo!</h4>
The recommended domain setup these days is to remove <em>www.</em> from the URL. So we will continue setting up your domain without the www. prefix.
</div>',
				'new_lines' => '',
				'esc_html' => 0,
			),
			array(
				'key' => 'field_5e982b9628957',
				'label' => 'Migration',
				'name' => '',
				'type' => 'page',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'hide_admin' => true,
				'show_numbering' => 1,
				'previous_text' => '',
				'next_text' => '',
			),
			array(
				'key' => 'field_5e84b2c3cd5d7',
				'label' => 'Check Your Domain Migration',
				'name' => '',
				'type' => 'message',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => 'acf-hide-title',
					'id' => '',
				),
				'hide_admin' => 0,
				'message' => '[dollie_blockquote title="Check Your Domain Migration"]
We have now updated your domain!
[/dollie_blockquote]',
				'new_lines' => '',
				'esc_html' => 0,
			),
			array(
				'key' => 'field_5e84b2dbcd5d8',
				'label' => 'Is your site now using your custom domain?',
				'name' => 'is_domain_active',
				'type' => 'radio',
				'instructions' => '',
				'required' => 1,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'hide_admin' => 0,
				'choices' => array(
					'yes' => 'Yes, it does!',
					'no' => 'No, it still shows the temporary URL..',
					'yes_problems' => 'Yes, but my site is broken or behaving weirdly..',
				),
				'allow_null' => 1,
				'other_choice' => 0,
				'default_value' => '',
				'layout' => 'vertical',
				'return_format' => 'value',
				'save_other_choice' => 0,
			),
			array(
				'key' => 'field_5e84b416b355e',
				'label' => 'Migration error',
				'name' => '',
				'type' => 'message',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_5e84b2dbcd5d8',
							'operator' => '==',
							'value' => 'yes_problems',
						),
					),
				),
				'wrapper' => array(
					'width' => '',
					'class' => 'acf-hide-title',
					'id' => '',
				),
				'hide_admin' => 0,
				'message' => 'Please <a href="{dollie_support_link}">create a support ticket</a> so our migration team can get to the bottom of this!',
				'new_lines' => '',
				'esc_html' => 0,
			),
			array(
				'key' => 'field_5e84b459b355f',
				'label' => 'Domain not migrated',
				'name' => '',
				'type' => 'message',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_5e84b2dbcd5d8',
							'operator' => '==',
							'value' => 'no',
						),
					),
				),
				'wrapper' => array(
					'width' => '',
					'class' => 'acf-hide-title',
					'id' => '',
				),
				'hide_admin' => 0,
				'message' => '<div class="box-light padding-half">
<h4>Please wait a few more minutes...</h4>
If you have a large database the update might take a while. Please hold on a couple of minutes and if nothing has changed please <a href="{dollie_support_link}">get in touch </a>with our support team so we can look into the issue.
</div>',
				'new_lines' => '',
				'esc_html' => 0,
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'af_form',
					'operator' => '==',
					'value' => 'form_dollie_domain_update_url',
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

	acf_add_local_field_group(array(
		'key' => 'group_5e729a60cde61',
		'title' => '[Form] After Launch Wizard',
		'fields' => array(
			array(
				'key' => 'field_5e729d5994745',
				'label' => 'Page 1',
				'name' => '',
				'type' => 'page',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'hide_admin' => true,
				'show_numbering' => 1,
				'previous_text' => '',
				'next_text' => '',
			),
			array(
				'key' => 'field_5e729cf629ee9',
				'label' => 'What would you like to do?',
				'name' => 'what_to_do',
				'type' => 'radio',
				'instructions' => '',
				'required' => 1,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'hide_admin' => 0,
				'choices' => array(
					'setup' => 'Continue setting up my new site',
					'migrate' => 'Migrate an existing WordPress site to this new install',
				),
				'allow_null' => 0,
				'other_choice' => 0,
				'default_value' => 'setup',
				'layout' => 'vertical',
				'return_format' => 'array',
				'save_other_choice' => 0,
			),
			array(
				'key' => 'field_5e729ebfdbdc2',
				'label' => 'Page 2',
				'name' => '',
				'type' => 'page',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'hide_admin' => true,
				'show_numbering' => 1,
				'previous_text' => '',
				'next_text' => '',
			),
			array(
				'key' => 'field_5e729de911000',
				'label' => 'Site Name',
				'name' => 'site_name',
				'type' => 'text',
				'instructions' => '',
				'required' => 1,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_5e729cf629ee9',
							'operator' => '==',
							'value' => 'setup',
						),
					),
				),
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
				'maxlength' => '',
			),
			array(
				'key' => 'field_5e72a094ba6a8',
				'label' => 'Site Description',
				'name' => 'site_description',
				'type' => 'text',
				'instructions' => 'Depending on your WordPress theme your description might be shown in several areas across your site. It\'s also shown in the visitors browser window title.',
				'required' => 1,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_5e729cf629ee9',
							'operator' => '==',
							'value' => 'setup',
						),
					),
				),
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
				'maxlength' => '',
			),
			array(
				'key' => 'field_5e7363c04012a',
				'label' => 'Admin Email',
				'name' => 'admin_email',
				'type' => 'email',
				'instructions' => 'This address is used for admin purposes, like new user notifications. In most cases you probably want to use the same address you used when creating your account.',
				'required' => 1,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_5e729cf629ee9',
							'operator' => '==',
							'value' => 'setup',
						),
					),
				),
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
			),
			array(
				'key' => 'field_5e72a0bbba6a9',
				'label' => 'Admin Username',
				'name' => 'admin_username',
				'type' => 'text',
				'instructions' => 'The username you use to login to your WordPress admin.',
				'required' => 1,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_5e729cf629ee9',
							'operator' => '==',
							'value' => 'setup',
						),
					),
				),
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
				'maxlength' => '',
			),
			array(
				'key' => 'field_5e72a0f6ba6aa',
				'label' => 'Admin Password',
				'name' => 'admin_password',
				'type' => 'text',
				'instructions' => '',
				'required' => 1,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_5e729cf629ee9',
							'operator' => '==',
							'value' => 'setup',
						),
					),
				),
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
				'maxlength' => '',
			),
			array(
				'key' => 'field_5e7339f6f73f8',
				'label' => 'Migration Instructions',
				'name' => '',
				'type' => 'message',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_5e729cf629ee9',
							'operator' => '==',
							'value' => 'migrate',
						),
					),
				),
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'hide_admin' => 0,
				'message' => '{dollie_migration_instructions}',
				'new_lines' => '',
				'esc_html' => 0,
			),
			array(
				'key' => 'field_5e7348ccc4e8c',
				'label' => 'How did your migration go?',
				'name' => 'migration_status',
				'type' => 'radio',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_5e729cf629ee9',
							'operator' => '==',
							'value' => 'migrate',
						),
					),
				),
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'hide_admin' => 0,
				'choices' => array(
					'great' => 'Great, It worked perfectly!',
					'issues' => 'The migration completed, but my site has issues',
					'failed' => 'The migration failed completely :-(',
				),
				'allow_null' => 0,
				'other_choice' => 0,
				'default_value' => '',
				'layout' => 'vertical',
				'return_format' => 'value',
				'save_other_choice' => 0,
			),
			array(
				'key' => 'field_5e7349b4c4e8e',
				'label' => 'Contact Support',
				'name' => '',
				'type' => 'message',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_5e729cf629ee9',
							'operator' => '==',
							'value' => 'migrate',
						),
						array(
							'field' => 'field_5e7348ccc4e8c',
							'operator' => '!=',
							'value' => 'great',
						),
					),
				),
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'hide_admin' => 0,
				'message' => '<div class="box-light padding-half">
<h4>No worries , we\'re here to help!</h4>

Please create a support ticket so our team can assist you in completing your content migration.

</div>',
				'new_lines' => '',
				'esc_html' => 0,
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'af_form',
					'operator' => '==',
					'value' => 'form_dollie_after_launch',
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

	acf_add_local_field_group(array(
		'key' => 'group_5e833a614568d',
		'title' => '[Form] Delete Site',
		'fields' => array(
			array(
				'key' => 'field_5e8358f2d129b',
				'label' => 'Confirm Site Name',
				'name' => 'confirm_site_name',
				'type' => 'text',
				'instructions' => 'Please type the name of the site to confirm deletion, this can not be undone.',
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
				'maxlength' => '',
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'af_form',
					'operator' => '==',
					'value' => 'form_dollie_delete_site',
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

	acf_add_local_field_group(array(
		'key' => 'group_5e6a176c384ee',
		'title' => '[Form] Launch Site',
		'fields' => array(
			array(
				'key' => 'field_5e6a1773d54c4',
				'label' => 'Choose Your URL',
				'name' => 'site_url',
				'type' => 'text',
				'instructions' => 'Please choose a temporary URL for your site. This will be the place where you can work on your site used until you are ready to go live and connect your own domain.',
				'required' => 1,
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
				'maxlength' => '',
			),
			array(
				'key' => 'field_5e6a1861b9025',
				'label' => 'Admin Email',
				'name' => 'site_admin_email',
				'type' => 'email',
				'instructions' => 'This is the email address you use to login to your WordPress admin.',
				'required' => 1,
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
			),
			array(
				'key' => 'field_5e6a221a065b8',
				'label' => 'Select a Blueprint (optional)',
				'name' => 'site_blueprint',
				'type' => 'radio',
				'instructions' => 'Carefully crafted site designs made by our team which you can use as a starting point for your new site.',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'hide_admin' => 0,
				'choices' => array(
				),
				'allow_null' => 0,
				'other_choice' => 0,
				'default_value' => '',
				'layout' => 'vertical',
				'return_format' => 'value',
				'save_other_choice' => 0,
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'af_form',
					'operator' => '==',
					'value' => 'form_dollie_launch_site',
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

	acf_add_local_field_group(array(
		'key' => 'group_5e7255fadcb82',
		'title' => '[Form] List Site Backups',
		'fields' => array(
			array(
				'key' => 'field_5e72562baf79a',
				'label' => 'Available Backups',
				'name' => 'site_backup',
				'type' => 'radio',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'hide_admin' => 0,
				'choices' => array(
				),
				'allow_null' => 0,
				'other_choice' => 0,
				'default_value' => '',
				'layout' => 'vertical',
				'return_format' => 'array',
				'save_other_choice' => 0,
			),
			array(
				'key' => 'field_5e7256abaf79b',
				'label' => 'What would you like to restore?',
				'name' => 'what_to_restore',
				'type' => 'select',
				'instructions' => '',
				'required' => 1,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'hide_admin' => 0,
				'choices' => array(
					'full' => 'Everything (Files & Database)',
					'files-only' => 'Files Only',
					'database-only' => 'Database Only',
				),
				'default_value' => array(
				),
				'allow_null' => 0,
				'multiple' => 0,
				'ui' => 0,
				'return_format' => 'array',
				'ajax' => 0,
				'placeholder' => '',
			),
			array(
				'key' => 'field_5e729515bb78b',
				'label' => 'You are about to restore your site',
				'name' => 'final_message',
				'type' => 'calculated',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'hide_admin' => 0,
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'af_form',
					'operator' => '==',
					'value' => 'form_dollie_list_backups',
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

	acf_add_local_field_group(array(
		'key' => 'group_5e836154347c4',
		'title' => '[Form] Performance',
		'fields' => array(
			array(
				'key' => 'field_5e836164a3819',
				'label' => 'Choose Your Caching Method',
				'name' => 'caching_method',
				'type' => 'radio',
				'instructions' => '<strong>PoweredCache</strong> - Recommended. Control cache settings on the WordPress level via the PoweredCache page in your WP admin. <br>
<strong>WPRocket</strong> - Recommended if you\'re using WPRocket.',
				'required' => 1,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'hide_admin' => 0,
				'choices' => array(
					'simple' => 'PoweredCache',
					'wprocket' => 'WPRocket',
				),
				'allow_null' => 0,
				'other_choice' => 0,
				'default_value' => '',
				'layout' => 'vertical',
				'return_format' => 'value',
				'save_other_choice' => 0,
			),
			array(
				'key' => 'field_5e836202a381a',
				'label' => 'PHP Version',
				'name' => 'php_version',
				'type' => 'radio',
				'instructions' => '',
				'required' => 1,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'hide_admin' => 0,
				'choices' => array(
					'php-7' => 'PHP 7.0',
					'php-5' => 'PHP 5.6',
				),
				'allow_null' => 0,
				'other_choice' => 0,
				'default_value' => '',
				'layout' => 'vertical',
				'return_format' => 'value',
				'save_other_choice' => 0,
			),
			array(
				'key' => 'field_5e836250a381b',
				'label' => '',
				'name' => '',
				'type' => 'message',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_5e836202a381a',
							'operator' => '==',
							'value' => 'php-7',
						),
					),
				),
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'hide_admin' => 0,
				'message' => '<div class="alert alert-danger">
Whilst PHP7 gives your site a big performance boost not all plugins and themes are yet compatible with PHP7. Please make sure to test your site functionality before you decide to use PHP7 </div>',
				'new_lines' => '',
				'esc_html' => 0,
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'af_form',
					'operator' => '==',
					'value' => 'form_dollie_performance',
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

	acf_add_local_field_group(array(
		'key' => 'group_5e8300c978315',
		'title' => '[Form] Plugin Updates',
		'fields' => array(
			array(
				'key' => 'field_5e8300e4930f6',
				'label' => 'Plugins to Update',
				'name' => 'plugins_to_update',
				'type' => 'checkbox',
				'instructions' => '',
				'required' => 1,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'hide_admin' => 0,
				'choices' => array(
				),
				'allow_custom' => 0,
				'default_value' => array(
				),
				'layout' => 'vertical',
				'toggle' => 0,
				'return_format' => 'value',
				'save_custom' => 0,
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'af_form',
					'operator' => '==',
					'value' => 'form_dollie_plugin_updates',
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

	acf_add_local_field_group(array(
		'key' => 'group_5e85d59a48243',
		'title' => '[Form] Quick Launch',
		'fields' => array(
			array(
				'key' => 'field_5e85d5ab2410b',
				'label' => 'Your Name',
				'name' => 'client_name',
				'type' => 'text',
				'instructions' => 'Please enter your name',
				'required' => 1,
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
				'maxlength' => '',
			),
			array(
				'key' => 'field_5e85d5b42410c',
				'label' => 'Email',
				'name' => 'client_email',
				'type' => 'email',
				'instructions' => 'We need your email to launch the site',
				'required' => 1,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '50',
					'class' => '',
					'id' => '',
				),
				'hide_admin' => 0,
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
			),
			array(
				'key' => 'field_5e85d5ec2410d',
				'label' => 'Password',
				'name' => 'client_password',
				'type' => 'text',
				'instructions' => 'Set a password to also create an account.',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '50',
					'class' => '',
					'id' => '',
				),
				'hide_admin' => 0,
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'af_form',
					'operator' => '==',
					'value' => 'form_dollie_quick_launch',
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

	acf_add_local_field_group(array(
		'key' => 'group_5e821cfd8136f',
		'title' => '[Forms] Create New Blueprint',
		'fields' => array(
			array(
				'key' => 'field_5e821d165e20a',
				'label' => 'Confirmation',
				'name' => 'confirmation',
				'type' => 'true_false',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'hide_admin' => 0,
				'message' => 'Yes, create a new blueprint!',
				'default_value' => 0,
				'ui' => 0,
				'ui_on_text' => '',
				'ui_off_text' => '',
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'af_form',
					'operator' => '==',
					'value' => 'form_dollie_create_blueprint',
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