<?php
if ( isset( $_GET['payment-status'] ) ) {
	dollie()->load_template(
		'notice',
		[
			'type'         => 'success',
			'icon'         => 'fas fa-check',
			'title'        => __( 'Your First Payment was successful!', 'dollie' ),
			'message'      => __(
				sprintf(
					'You can find your payment details in your <a href="%s">account overview</a> and in your email inbox. Now let\'s continue setting up your site...',
					wc_get_account_endpoint_url()
				),
				'dollie'
			),
			'bottom_space' => true,
		],
		true
	);
}

if ( current_user_can( 'manage_options' ) && ! dollie()->is_live() ) {
	dollie()->load_template(
		'notice',
		[
			'type'         => 'error',
			'icon'         => 'fas fa-exclamation-circle',
			'title'        => __( 'Not Connected to Dollie', 'dollie' ),
			'message'      => __( 'Please connect your Hub to the Dollie Cloud so you can start launching sites on your platform.', 'dollie' ),
			'links'        => [
				[
					'title' => __( 'Connect to Dollie Cloud', 'dollie' ),
					'url'   => \Dollie\Core\Services\AuthService::instance()->get_auth_link(),
				],
			],
			'bottom_space' => true,
		],
		true
	);
}

if ( dollie()->subscription()->size_limit_reached() ) {
	dollie()->load_template(
		'notice',
		[
			'type'         => 'error',
			'icon'         => 'fas fa-exclamation-circle',
			'title'        => __( 'You have reached your Disk Space limit', 'dollie' ),
			'message'      => __( 'You have reached the maximum amount of disk space you can use. Please upgrade your plan to add more disk space.', 'dollie' ),
			'bottom_space' => true,
		],
		true
	);
}

if ( dollie()->subscription()->site_limit_reached() ) {
	dollie()->load_template(
		'notice',
		[
			'type'         => 'error',
			'icon'         => 'fas fa-exclamation-circle',
			'title'        => __( 'You have reached your site limit', 'dollie' ),
			'message'      => sprintf( esc_html__( 'You have reached the maximum amount of %s you can launch. Upgrade your plan or reach out to our team directly.', 'dollie' ), dollie()->string_variants()->get_site_type_string() ),
			'bottom_space' => true,
		],
		true
	);
}

// Load form
dollie()->load_template( 'widgets/launch/form', [ 'settings' => $settings ], true );
