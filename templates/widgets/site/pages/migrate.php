<?php

if ( ! isset( $container ) ) {
	$container = dollie()->get_container();
}

if ( get_option( 'wpd_onboarding_migrate_site_url') ) {
	$migration_location =  get_option( 'wpd_onboarding_migrate_site_url', true);
	$migration_site = '<strong>' . get_option( 'wpd_onboarding_migrate_site_url', true) .'</strong>';
} else {
	$migration_location = 'your existing WordPress site';
	$migration_site = 'the <strong>site you would like to migrate</strong> (i.e yoursite.com)';
}

dollie()->load_template(
	'notice',
	[
		'type'         => 'success',
		'icon'         => 'fas fa-truck',
		'title'        => esc_html__( 'Ready for the easiest migration ever?', 'dollie' ),
		'message'      => wp_kses_post(
			sprintf(
				__( 'We are going to create an exact copy of <strong>%s</strong> into <strong>%s</strong><br><br>Once this is done and you confirmed everything is as it should be, you complete the migration by following our Domain Wizard. Let\'s get started right away!', 'dollie' ),
				$migration_location, $container->get_url(),
			)
		),
		'bottom_space' => true,
	],
	true
);

$credentials = $container->get_credentials();

?>

<p>
	<span class="alert alert-info"><?php _e( 'Do not worry; your live site will not be touched or modified in any way!', 'dollie' ); ?></span>
</p>

<h4 class="dol-font-bold dol-mb-2 dol-mt-2 dol-text-xl"><?php _e( 'Step 1 - Install the Migrate Guru Plugin', 'dollie' ); ?></h4>

<div class="dol-flex dol-flex-wrap sm:dol-mx-auto sm:dol-mb-2 dol--mx-2 dol-mt-5 dol-pt-2 dol-pb-4">
	<div class="dol-p-0 dol-w-full">
		<div class="dol-bg-gray-50 dol-flex dol-p-2 dol-h-full dol-items-center">
			<svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
				 class="dol-text-indigo-500 dol-w-5 dol-h-5 dol-flex-shrink-0 dol-mr-4" viewBox="0 0 24 24">
				<path d="M22 11.08V12a10 10 0 11-5.93-9.14"></path>
				<path d="M22 4L12 14.01l-3-3"></path>
			</svg>
			<span class="title-font font-s dol-text-sm"><?php echo wp_kses_post( __( 'Login to the WordPress Admin of', 'dollie' ) ); ?> <?php echo $migration_site;?></span>
		</div>
	</div>
	<div class="dol-p-0 dol-w-full">
		<div class="dol-bg-gray-50 dol-flex dol-p-2 dol-h-full dol-items-center">
			<svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
				 class="dol-text-indigo-500 dol-w-5 dol-h-5 dol-flex-shrink-0 dol-mr-4" viewBox="0 0 24 24">
				<path d="M22 11.08V12a10 10 0 11-5.93-9.14"></path>
				<path d="M22 4L12 14.01l-3-3"></path>
			</svg>
			<span class="title-font font-s dol-text-sm"><?php echo wp_kses_post( __( 'Go to <strong>Plugins > Add New </strong> and search for "Migrate Guru"', 'dollie' ) ); ?></span>
		</div>
	</div>
	<div class="dol-p-0 dol-w-full">
		<div class="dol-bg-gray-50 dol-flex dol-p-2 dol-h-full dol-items-center">
			<svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
				 class="dol-text-indigo-500 dol-w-5 dol-h-5 dol-flex-shrink-0 dol-mr-4" viewBox="0 0 24 24">
				<path d="M22 11.08V12a10 10 0 11-5.93-9.14"></path>
				<path d="M22 4L12 14.01l-3-3"></path>
			</svg>
			<span class="title-font font-s dol-text-sm"><?php echo wp_kses_post( __( 'Press the <strong>Install Now</strong> button.', 'dollie' ) ); ?></span>
		</div>
	</div>
	<div class="dol-p-0 dol-w-full">
		<div class="dol-bg-gray-50 dol-flex dol-p-2 dol-h-full dol-items-center">
			<svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
				 class="dol-text-indigo-500 dol-w-5 dol-h-5 dol-flex-shrink-0 dol-mr-4" viewBox="0 0 24 24">
				<path d="M22 11.08V12a10 10 0 11-5.93-9.14"></path>
				<path d="M22 4L12 14.01l-3-3"></path>
			</svg>
			<span class="title-font font-s dol-text-sm"><?php _e( 'Activate the plugin', 'dollie' ); ?></span></div>
	</div>
	<div class="dol-p-0 dol-w-full">
		<div class="dol-bg-gray-50 dol-flex dol-p-2 dol-h-full dol-items-center">
			<svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
				 class="dol-text-indigo-500 dol-w-5 dol-h-5 dol-flex-shrink-0 dol-mr-4" viewBox="0 0 24 24">
				<path d="M22 11.08V12a10 10 0 11-5.93-9.14"></path>
				<path d="M22 4L12 14.01l-3-3"></path>
			</svg>
			<span class="title-font font-s dol-text-sm"> <?php _e( 'Click on the <strong>Migrate Guru Menu</strong> link in the WordPress Admin', 'dollie' ); ?></span>
		</div>
	</div>
</div>
<h4 class="dol-font-bold dol-mb-2 dol-mt-2 dol-text-xl"><?php _e( 'Step 2 - Choosing Your Migration Method', 'dollie' ); ?></h4>
<div class="dol-flex dol-flex-wrap sm:dol-mx-auto sm:dol-mb-2 dol--mx-2 dol-mt-5 dol-pt-2 dol-pb-4">
	<div class="dol-p-0 dol-w-full">
		<div class="dol-bg-gray-50 dol-flex dol-p-2 dol-h-full dol-items-center">
			<svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
				 class="dol-text-indigo-500 dol-w-5 dol-h-5 dol-flex-shrink-0 dol-mr-4" viewBox="0 0 24 24">
				<path d="M22 11.08V12a10 10 0 11-5.93-9.14"></path>
				<path d="M22 4L12 14.01l-3-3"></path>
			</svg>
			<span class="title-font font-s dol-text-sm"> <?php _e( 'Fill in your email address then click "Migrate Site" to continue.', 'dollie' ); ?></span>
		</div>
	</div>
	<div class="dol-p-0 dol-w-full">
		<div class="dol-bg-gray-50 dol-flex dol-p-2 dol-h-full dol-items-center">
			<svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
				 class="dol-text-indigo-500 dol-w-5 dol-h-5 dol-flex-shrink-0 dol-mr-4" viewBox="0 0 24 24">
				<path d="M22 11.08V12a10 10 0 11-5.93-9.14"></path>
				<path d="M22 4L12 14.01l-3-3"></path>
			</svg>
			<span class="title-font font-s dol-text-sm"> <?php echo wp_kses_post( __( 'Now choose <strong>FTP</strong> as your migration method, at the bottom right of the screen.', 'dollie' ) ); ?></span>
		</div>
	</div>
	<div class="dol-p-0 dol-w-full">
		<div class="dol-bg-gray-50 dol-flex dol-p-2 dol-h-full dol-items-center">
			<svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
				 class="dol-text-indigo-500 dol-w-5 dol-h-5 dol-flex-shrink-0 dol-mr-4" viewBox="0 0 24 24">
				<path d="M22 11.08V12a10 10 0 11-5.93-9.14"></path>
				<path d="M22 4L12 14.01l-3-3"></path>
			</svg>
			<span class="title-font font-s dol-text-sm"><?php _e( 'Finally fill in the following settings for your migration.', 'dollie' ); ?></span>
		</div>
	</div>

</div>

<h4 class="dol-font-bold dol-mb-2 dol-mt-2 dol-text-xl">
	<?php _e( 'Step 3 - Fill in the Connection Details', 'dollie' ); ?>
</h4>

<div class="dol-flex dol-flex-wrap sm:dol-mx-auto sm:dol-mb-2 dol--mx-2 dol-mt-5 dol-pt-2 dol-pb-4">
	<div class="dol-p-0 sm:dol-w-1/2 dol-w-full">
		<div class="dol-bg-gray-50 dol-rounded dol-flex dol-p-4 dol-h-full dol-items-center">
				<span class="title-font dol-font-medium">
					<?php _e( 'Destination Site URL', 'dollie' ); ?>
				<br><strong>https://<?php echo $container->get_url(); ?></strong></span></div>
	</div>
	<div class="dol-p-0 sm:dol-w-1/2 dol-w-full">
		<div class="dol-bg-gray-50 dol-rounded dol-flex dol-p-4 dol-h-full dol-items-center"><span
					class="title-font dol-font-medium"><?php _e( 'FTP Type', 'dollie' ); ?>
				<br><strong>SFTP</strong></span></div>
	</div>
	<div class="dol-p-0 sm:dol-w-1/2 dol-w-full">
		<div class="dol-bg-gray-50 dol-rounded dol-flex dol-p-4 dol-h-full dol-items-center">
				<span class="title-font dol-font-medium">
					<?php _e( 'Destination Server IP/FTP Address', 'dollie' ); ?>
				<br><strong><?php echo $credentials['ip']; ?></strong></span>
		</div>
	</div>
	<div class="dol-p-0 sm:dol-w-1/2 dol-w-full">
		<div class="dol-bg-gray-50 dol-rounded dol-flex dol-p-4 dol-h-full dol-items-center"><span
					class="title-font dol-font-medium"><?php _e( 'Port:', 'dollie' ); ?>
				<br><strong><?php echo $credentials['port']; ?></strong></span></div>
	</div>
	<div class="dol-p-0 sm:dol-w-1/2 dol-w-full">
		<div class="dol-bg-gray-50 dol-rounded dol-flex dol-p-4 dol-h-full dol-items-center"><span
					class="title-font dol-font-medium"><?php _e( 'FTP Username:', 'dollie' ); ?>
			<br><strong><?php echo $credentials['username']; ?></strong></span></div>
	</div>
	<div class="dol-p-0 sm:dol-w-1/2 dol-w-full">
		<div class="dol-bg-gray-50 dol-rounded dol-flex dol-p-4 dol-h-full dol-items-center"><span
					class="title-font dol-font-medium"><?php _e( 'FTP Password:', 'dollie' ); ?>
				<br><strong><?php echo $credentials['password']; ?></strong><br></span></div>
	</div>
	<div class="dol-p-0 dol-rounded dol-w-full">
		<div class="dol-bg-gray-50 dol-flex dol-p-2 dol-h-full dol-items-center"><span
					class="title-font dol-font-medium dol-p-2">
				<?php _e( 'Directory Path:', 'dollie' ); ?>
				<br><strong>
					/usr/src/app
				</strong><br></span>
		</div>
	</div>
	<div class="dol-p-0 dol-w-full">
		<div class="dol-bg-gray-50 dol-flex dol-p-2 dol-h-full dol-items-center">
			<svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
				 class="dol-text-indigo-500 dol-w-5 dol-h-5 dol-flex-shrink-0 dol-mr-4" viewBox="0 0 24 24">
				<path d="M22 11.08V12a10 10 0 11-5.93-9.14"></path>
				<path d="M22 4L12 14.01l-3-3"></path>
			</svg>
			<span class="title-font font-s dol-text-sm"> <?php echo wp_kses_post( __( 'Now click the "Migrate" button', 'dollie' ) ); ?></span>
		</div>
	</div>
</div>

<h4 class="dol-font-bold dol-mb-2 dol-mt-2 dol-text-xl"><?php _e( 'Step 4 - Sit back and enjoy the show!', 'dollie' ); ?></h4>

<div class="dol-my-6">
	<?php echo wp_kses_post( __( 'Press <strong>Migrate</strong> and sit back and enjoy the show. Depending on the size of your site and the speed of your current host this process could take up to a couple of hours. Do not worry, this is completely normal! Migrate Guru will send you an email when the migration has completed so you can easily continue to this setup wizard.', 'dollie' ) ); ?>
</div>

<h4 class="dol-font-bold dol-mb-2 dol-mt-2 dol-text-xl"><?php _e( 'Step 5 - Test your migrated website and confirm that your migration was successfull', 'dollie' ); ?></h4>

<div class="dol-my-6">
	<?php echo wp_kses_post( __( 'Now you can login to the WordPress Admin of your newly migrated site using the same login details. So visit ', 'dollie' ) ); ?><a href="<?php echo $migration_site;?>"><?php echo $migration_site;?></a> <?php echo wp_kses_post( __( 'and verify that your migration was successfull! Sometimes some of your plugins might show notices or you run into other smaller issues.', 'dollie' ) ); ?>
</div>
