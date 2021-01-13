<?php

if ( ! dollie()->has_partner_subscription() || ( dollie()->is_partner_subscription_trial() && 0 === dollie()->get_partner_subscription_credits() ) ) {
	\Dollie\Core\Utils\Tpl::load(
		'notice',
		[
			'type'    => 'warning',
			'icon'    => 'fal fa-exclamation-circle',
			'title'   => __( 'Deployments are currently unavailable', 'dollie' ),
			'message' => __( 'Please try again later or contact the website administrator!', 'dollie' ),
		],
		true
	);

	return;
}

if ( current_user_can( 'manage_options' ) || dollie()->has_subscription() ) {
	if ( ! dollie()->site_limit_reached() && ! dollie()->size_limit_reached() && ! dollie()->staging_limit_reached() ) {
		echo do_shortcode( '[dollie_form form="form_dollie_launch_site"]' );
	}
	return;
}

\Dollie\Core\Utils\Tpl::load(
	'notice',
	[
		'type'    => 'error',
		'icon'    => 'fal fa-exclamation-circle',
		'title'   => __( 'You have no active subscription', 'dollie' ),
		'message' => __( 'Please sign-up for a subscription to launch a new website!', 'dollie' ),
	],
	true
);



