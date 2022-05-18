<?php

$subscription = dollie()->subscription();

if ( ! $subscription->has_partner_credits() || 0 === $subscription->get_partner_deploy_limit() ) {

	$message = esc_html__( 'We are hard at work at our infrastructure, please come back to this page at a later time.', 'dollie' );

	if ( current_user_can( 'manage_options' ) ) {
		$message = sprintf(
			__( 'Your Dollie subscription is inactive. Please check your subscription status on our <a href="%s">partners website</a>.', 'dollie' ),
			'https://partners.getdollie.com'
		);
	}

	dollie()->load_template(
		'notice',
		[
			'type'    => 'warning',
			'icon'    => 'fas fa-exclamation-circle',
			'title'   => __( 'Sorry, Launching a new site is currently not possible.', 'dollie' ),
			'message' => $message,
		],
		true
	);

	return;
}

if ( current_user_can( 'manage_options' ) || $subscription->has_subscription() ) {
	if ( ! $subscription->site_limit_reached() && ! $subscription->size_limit_reached() && ! $subscription->staging_sites_limit_reached() ) {
		$form_type         = $settings['type'] ?? 'site';
		$button_text       = isset( $settings['button_text'] ) && $settings['button_text'] ? $settings['button_text'] : esc_html__( 'Launch New ', 'dollie' );
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
