<?php

dollie()->load_template( 'widgets/launch/before-message', [], true );
$subscription = dollie()->subscription();

if ( ! $subscription->has_partner_credits() || 0 === $subscription->get_partner_deploy_limit() && dollie()->is_live() ) {
	$message = esc_html__( 'Sorry, We are currently hard at work at our platform and temporary disabled launching new sites. Please come back to this page at a later time.', 'dollie' );

	if ( dollie()->get_user()->can_manage_all_sites() ) {
		$message = sprintf(
			__( 'Your Dollie Hub has an issue which prevents the launching of new sites. Please check if you connected this Hub to Dollie Private Cloud and verify your subscription in  your <a href="%s">Dollie Control HQ</a>.', 'dollie' ),
			'https://cloud.getdollie.com'
		);
	}

	if ( dollie()->get_partner_status() === 'trial'&& dollie()->get_user()->can_manage_all_sites() ) {
		dollie()->load_template(
			'notice',
			[
				'type'    => 'notice',
				'icon'    => 'fas fa-exclamation-circle',
				'title'   => 'Please Start Your Subscription to Launch More Sites',
				'message' => dollie()->load_template( 'admin/notices/subscription-missing', [] ),
			],
			true
		);
	} else {
		dollie()->load_template(
			'notice',
			[
				'type'    => 'error',
				'icon'    => 'fas fa-exclamation-circle',
				'title'   => __( 'Sorry, Launching a new site is currently not possible.', 'dollie' ),
				'message' => $message,
			],
			true
		);
	}

	return;
}

if ( dollie()->get_user()->can_manage_all_sites() || $subscription->has_subscription() ) {
	if ( ! $subscription->site_limit_reached() && ! $subscription->size_limit_reached() ) {
		$form_type         = $settings['type'] ?? 'site';
		$button_text       = isset( $settings['button_text'] ) && $settings['button_text'] ? $settings['button_text'] : sprintf( esc_html__( 'Launch %s', 'dollie' ), dollie()->string_variants()->get_site_type_string() );
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
