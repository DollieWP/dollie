<?php
var_dump(dollie()->has_partner_credits());
if ( ! dollie()->has_partner_subscription() || ! dollie()->has_partner_credits() || ( dollie()->is_partner_subscription_trial() && 0 === dollie()->get_partner_subscription_credits() ) ) {
	dollie()->load_template(
		'notice',
		[
			'type'    => 'warning',
			'icon'    => 'fas fa-exclamation-circle',
			'title'   => __( 'Sorry, Launching a new site is currently not possible.', 'dollie' ),
			'message' => __( 'We are hard at work at our infrastructure, please come back to this page at a later time.', 'dollie' ),
		],
		true
	);

	return;
}

if ( current_user_can( 'manage_options' ) || dollie()->has_subscription() ) {
	if ( ! dollie()->site_limit_reached() && ! dollie()->size_limit_reached() && ! dollie()->staging_limit_reached() ) {
		$form_type         = $settings['type'] ?? 'site';
		$button_text       = isset( $settings['button_text'] ) && $settings['button_text'] ? $settings['button_text'] : esc_html__( 'Launch New Site', 'dollie' );
		$advanced_settings = (bool) $settings['advanced_settings'];

		echo do_shortcode(
			'[dollie_form form="form_dollie_launch_site" ' .
				'values="site_type:' . $form_type . ',advanced_settings:' . $advanced_settings . '" ' .
				'submit_text="' . $button_text . '"]'
		);
	}

	return;
}

dollie()->load_template(
	'notice',
	[
		'type'    => 'error',
		'icon'    => 'fas fa-exclamation-circle',
		'title'   => __( 'Sorry, You have no active subscription', 'dollie' ),
		'message' => __( 'Please sign-up for a one of our subscriptions to launch a new website!', 'dollie' ),
	],
	true
);



