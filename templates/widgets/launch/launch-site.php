<?php
global $wp_query;
$subscription_vip = dollie()->subscription()->has_vip(get_current_user_id());
$global_vip = get_field( 'wpd_enable_global_vip_sites', 'options' );
$user = dollie()->get_user();

$site_launch = dollie()->page()->get_launch_site_id();
$blueprint_launch   = dollie()->page()->get_launch_blueprint_id();

if ( $wp_query->post->ID == $site_launch ) {
	dollie()->show_helper_video('launching-sites', '593H4SuXlKs', 'Hub Tour - Launching Sites', 'Launching Your First Site', true);
}
if ( $wp_query->post->ID == $blueprint_launch ) {
	dollie()->show_helper_video('launching-blueprints', 'VemKlUaqB2Q', 'Hub Tour - Launching Blueprints', 'Launching Your First Blueprint', true);
}

if ( ! is_user_logged_in() && ( is_page( $launch_id ) || is_page( $sites_id ) ) ) {
	wp_redirect( get_permalink( $dash_id ) );
	exit();
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



if ( $subscription_vip && ! $global_vip && ! isset($_GET['payment-status']) && $wp_query->post->ID == $site_launch )  {
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

if ( isset( $_COOKIE[ DOLLIE_BLUEPRINTS_COOKIE ] ) && ! is_admin() && $wp_query->post->ID == $site_launch || isset( $_GET['payment-status'] ) ) {
	//Custom Form Layout when launching a specific blueprint
	dollie()->load_template( 'widgets/launch/blueprint-launch', [ 'settings' => $settings ], true );
	return;
}

// Load form
dollie()->load_template( 'widgets/launch/form', [ 'settings' => $settings ], true );
