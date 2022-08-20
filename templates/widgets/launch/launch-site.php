<?php
$subscription_vip = dollie()->subscription()->has_vip(get_current_user_id());
$global_vip = get_field( 'wpd_enable_global_vip_sites', 'options' );
$user = dollie()->get_user();

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
					wc_get_account_endpoint_url('orders')
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



if ( $subscription_vip && ! $global_vip && ! $_GET['payment-status'] )  {
	dollie()->load_template(
		'notice',
		[
			'type'         => 'success',
			'icon'         => 'fas fa-gem',
			'title'        => __( 'You can launch VIP Sites!', 'dollie' ),
			'message'      => __( 'If you would like to launch a site with additional resources, backups and priority support from our team, mark your site as VIP.', 'dollie' ),
			'bottom_space' => true,
		],
		true
	);
}

if ( $global_vip && $user->can_manage_all_sites() ) {
	dollie()->load_template(
		'notice',
		[
			'type'         => 'info',
			'icon'         => 'fas fa-gem',
			'title'        => __( 'Admin Info - VIP Sites Enabled for All Sites ', 'dollie' ),
			'message'      => __( 'You have configured VIP Sites to be enabled for each site that is being launched on your platform.', 'dollie' ),
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
