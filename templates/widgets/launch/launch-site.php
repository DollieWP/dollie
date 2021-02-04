<?php
if ( isset( $_GET['payment-status'] ) ) {

	\Dollie\Core\Utils\Tpl::load(
		'notice',
		[
			'type'         => 'success',
			'icon'         => 'fas fa-check',
			'title'        => __( 'Your First Payment was successful!', 'dollie' ),
			'message'      => __(
				sprintf(
					'You can find your payment details in your <a href="%s">account overview</a> and in your email inbox. Now let\'s continue setting up your site...',
					get_site_url() . '/my-account/orders'
				),
				'dollie'
			),
			'bottom_space' => true,
		],
		true
	);

}

if ( current_user_can( 'manage_options' ) && ! dollie()->is_live() ) {

	\Dollie\Core\Utils\Tpl::load(
		'notice',
		[
			'type'         => 'error',
			'icon'         => 'fas fa-exclamation-circle',
			'title'        => __( 'Dollie is not connected!', 'dollie' ),
			'message'      => __( 'You need to connect your site to Dollie so you can start deploying sites.', 'dollie' ),
			'links'        => [
				[
					'title' => __( 'Connect with Dollie', 'dollie' ),
					'url'   => \Dollie\Core\Plugin::instance()->get_api_access_url(),
				],
			],
			'bottom_space' => true,
		],
		true
	);

}

if ( dollie()->size_limit_reached() ) {
	\Dollie\Core\Utils\Tpl::load(
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

if ( dollie()->site_limit_reached() ) {
	\Dollie\Core\Utils\Tpl::load(
		'notice',
		[
			'type'         => 'error',
			'icon'         => 'fas fa-exclamation-circle',
			'title'        => __( 'You have reached your site limit', 'dollie' ),
			'message'      => __( 'You have reached the maximum amount of sites you can launch. Please upgrade your plan to add more sites.', 'dollie' ),
			'bottom_space' => true,
		],
		true
	);
}

// Load form
\Dollie\Core\Utils\Tpl::load( 'widgets/launch/form', [ 'settings' => $settings ], true );
