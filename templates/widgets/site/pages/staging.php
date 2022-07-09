<?php return; ?>

<div id="dol-staging-panel">
	<?php

	if ( ! dollie()->is_elementor_editor() ) {
		wp_enqueue_script( 'dollie-site-content' );
	}

	$container       = get_post( get_the_ID() );
	$staging_enabled = get_field( 'wpd_enable_staging', 'options' );

	if ( ! $staging_enabled ) {
		return;
	}

	$staging_data = get_post_meta( get_the_ID(), '_wpd_staging_data', true );
	$staging_url  = get_post_meta( get_the_ID(), '_wpd_staging_url', true );
	$deploy_job   = get_post_meta( get_the_ID(), '_wpd_staging_deploy_job', true );

	$staging_status = 'disabled';

	if ( $staging_url ) {
		$staging_status = $staging_data[ $staging_url ]['status'];
	}

	$status_title   = __( 'Staging is disabled', 'dollie' );
	$status_message = sprintf( __( 'Here you can set up a staging area for <strong>%1$s</strong>. Staging allows you to safely  test out anything you\'d like on an exacy copy of your live %2$s.', 'dollie' ), $container->post_name, dollie()->string_variants()->get_site_type_string() );

	if ( 'pending' === $staging_status ) {
		$status_title   = __( 'Staging Area is being created', 'dollie' );
		$status_message = sprintf( __( 'Please be patient while we create a staging area for <strong>%s</strong>. The process might take up to 5 minutes.', 'dollie' ), $container->post_name );
	} elseif ( 'live' === $staging_status ) {
		$status_title   = sprintf( __( 'Staging area: %s', 'dollie' ), '<a class="dol-text-white" target="_blank" href="https://' . $staging_url . '">' . $staging_url . '</a>' );
		$status_message = sprintf( __( 'Here you can manage the staging area for <strong>%1$s</strong>. Staging allows you to safely test out anything you\'d like on an exacy copy of your live %2$s.', 'dollie' ), '<a target="_blank" href="https://' . $staging_url . '">' . $container->post_name . '</a>', dollie()->string_variants()->get_site_type_string() );
	}

	dollie()->load_template(
		'notice',
		[
			'type'    => 'info',
			'icon'    => 'fas fa-clone',
			'title'   => $status_title,
			'message' => $status_message,
		],
		true
	);

	?>

	<?php if ( 'pending' === $staging_status ) : ?>
		<?php dollie()->load_template( 'parts/site-deploying-checker', [], true ); ?>
	<?php elseif ( 'live' === $staging_status ) : ?>
		<?php
		$execution = dollie()->get_execution( get_the_ID(), dollie()->get_api()::EXECUTION_STAGING_SYNC );
		?>

		<?php if ( ! $execution || 0 !== $execution['status'] ) : ?>
			<?php if ( $execution && 1 === $execution['status'] ) : ?>
				<div class="dol-my-3 dol-bg-green-500 dol-text-white dol-rounded dol-px-6 dol-py-3">
					<?php esc_html_e( 'Staging has been successfully synced.', 'dollie' ); ?>
				</div>
			<?php elseif ( $execution && 2 === $execution['status'] ) : ?>
				<div class="dol-my-3 dol-bg-red-500 dol-text-white dol-rounded dol-px-6 dol-py-3">
					<?php esc_html_e( 'Syncing failed. Please try again.', 'dollie' ); ?>
				</div>
			<?php endif; ?>

			<?php dollie()->remove_execution( get_the_ID(), dollie()->get_api()::EXECUTION_STAGING_SYNC ); ?>

			<h2 class="dol-text-gray-500 text-s dol-font-small dol-uppercase dol-tracking-wide dol-mb-5 dol-mt-5 dol-text-xl">
				<?php esc_html_e( 'Staging Management', 'dollie' ); ?></h2>

			<?php
			$details = $staging_data[ $staging_url ]['data'];

			dollie()->load_template(
				'notice',
				[
					'type'         => 'info',
					'icon'         => 'fas fa-bookmark',
					'title'        => esc_html__( 'Your Staging SFTP Details', 'dollie' ),
					'message'      => '
					Address: <strong>' . $details['name'] . '</strong><br>
					Username: <strong>' . $details['containerSshUsername'] . '</strong><br>
					Password: <strong>' . $details['containerSshPassword'] . '</strong><br>
					Port: <strong>' . $details['containerSshPort'] . '<br><br>
					<a class="dol-text-brand-500 hover:dol-text-brand-600" href="sftp://' . $details['containerSshUsername'] . ':' . $details['containerSshPassword'] . '@' . $details['name'] . ':' . $details['containerSshPort'] . '">' .
						dollie()->icon()->arrow_right() . esc_html__( 'Quick Connect!', 'dollie' ) . '</a>',
					'bottom_space' => true,
				],
				true
			);
			?>

			<ul class="dol-grid dol-grid-cols-1 dol-gap-4 sm:dol-gap-4 sm:dol-grid-cols-2 dol-mt-3 dol-p-0 dol-m-0">
				<form id="dol-sync-staging" action="" method="post" class="dol-inline-block">
					<li class="dol-relative dol-col-span-1 dol-flex dol-bg-white dol-shadow dol-rounded-md dol-widget-custom">
						<div class="dol-flex-shrink-0 dol-flex dol-items-center dol-justify-center dol-w-16 dol-bg-green-500 dol-text-white dol-text-sm dol-leading-5 dol-font-medium dol-rounded-l-md">
							<?php echo dollie()->icon()->refresh( 'dol-text-white dol-text-xl' ); ?>
						</div>
						<div class="dol-flex-1 dol-flex dol-items-center dol-justify-between dol-border-t dol-border-r dol-border-b dol-border-gray-200 dol-rounded-r-md dol-truncate">
							<div class="dol-flex-1 dol-p-6 dol-text-sm dol-leading-5 dol-truncate">
								<a href="#" class="dol-text-gray-900 dol-font-medium hover:dol-text-gray-600 dol-transition dol-ease-in-out dol-duration-150">
								</a>
								<span class="dol-text-gray-500 dol-block dol-mt-1">
									<input type="hidden" name="sync_staging" value="1">
									<button type="submit" class="dol-bg-green-500 hover:dol-bg-green-600 focus:dol-bg-green-600 dol-text-white hover:dol-text-white focus:dol-outline-none dol-border-0 dol-rounded dol-px-4 dol-py-2">
										<?php esc_html_e( 'Sync from Staging to Live', 'dollie' ); ?>
									</button>
									<?php wp_nonce_field( 'wpd_staging_sync' ); ?>
								</span>
							</div>
						</div>
					</li>
				</form>

				<li class="dol-relative dol-col-span-1 dol-flex dol-bg-white dol-shadow dol-rounded-md dol-widget-customs">
					<div class="dol-flex-shrink-0 dol-flex dol-items-center dol-justify-center dol-w-16 dol-bg-red-500 dol-text-white dol-text-sm dol-leading-5 dol-font-medium dol-rounded-l-md">
						<?php echo dollie()->icon()->delete( 'dol-text-white dol-text-xl' ); ?>
					</div>
					<div class="dol-flex-1 dol-flex dol-items-center dol-justify-between dol-border-t dol-border-r dol-border-b dol-border-gray-200 dol-rounded-r-md dol-truncate">
						<div class="dol-flex-1 dol-p-6 dol-text-sm dol-leading-5 dol-truncate">
							<a href="#" class="dol-text-gray-900 dol-font-medium hover:dol-text-gray-600 dol-transition dol-ease-in-out dol-duration-150">
							</a>
							<span class="dol-text-gray-500 dol-block dol-mt-1">
								<form action="" method="post" class="dol-inline-block" id="dol-delete-staging">
									<input type="hidden" name="undeploy_staging" value="1">

									<button type="submit" class="dol-bg-red-500 hover:dol-bg-red-600 focus:dol-bg-red-600 dol-text-white hover:dol-text-white focus:dol-outline-none dol-border-0 dol-rounded dol-px-4 dol-py-2">
										<?php
										printf( esc_html__( 'Delete Your Staging %s', 'dollie' ), dollie()->string_variants()->get_site_type_string() );
										?>
									</button>
									<?php wp_nonce_field( 'wpd_staging_undeploy' ); ?>
								</form>
							</span>
						</div>
					</div>
				</li>

			</ul>

			<div class="dol-mt-6">

				<a href="<?php echo dollie()->get_customer_login_url( get_the_ID(), null, true ); ?>" target="_blank" class="dol-inline-block dol-bg-gray-500 hover:dol-bg-gray-600 focus:dol-bg-gray-600 dol-text-white hover:dol-text-white focus:dol-outline-none dol-border-0 dol-rounded dol-px-4 dol-py-2">
					<?php echo dollie()->icon()->site_login( 'dol-mr-1' ); ?>
					<?php
					printf( esc_html__( 'Login to Staging %s', 'dollie' ), dollie()->string_variants()->get_site_type_string() );
					?>
				</a>

			</div>
		<?php else : ?>
			<div class="dol-hidden" id="dol-execution-check" data-ajax-url="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>" data-container="<?php echo esc_attr( get_the_ID() ); ?>" data-type="<?php echo esc_attr( dollie()->get_api()::EXECUTION_STAGING_SYNC ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'dollie_check_execution' ) ); ?>">
			</div>
			<div class="dol-my-3 dol-bg-primary-500 dol-text-white dol-rounded dol-px-6 dol-py-3">
				<?php esc_html_e( 'Syncing is in process. You will be able to see your changes on the production site in a few minutes.', 'dollie' ); ?>
			</div>
		<?php endif; ?>
	<?php elseif ( 'disabled' === $staging_status ) : ?>
		<?php if ( dollie()->staging_sites_limit_reached() ) : ?>
			<div class="dol-mt-6">
				<?php
				dollie()->load_template(
					'notice',
					[
						'icon'  => dollie()->icon()->alert(),
						'type'  => 'error',
						'title' => esc_html__( 'You have reached your staging area limit. Please upgrade your subscription!', 'dollie' ),
					],
					true
				);

				return;
				?>
			</div>
		<?php else : ?>
			<div class="dol-mt-6">
				<form action="" method="post">
					<input type="hidden" name="create_staging" value="1">

					<button type="submit" class="dol-bg-primary-500 hover:dol-bg-primary-600 focus:dol-bg-primary-600 dol-text-white hover:dol-text-white focus:dol-outline-none dol-border-0 dol-rounded dol-px-4 dol-py-2">
						<?php echo dollie()->icon()->launch( 'dol-text-white dol-mr-1' ); ?>
						<?php
						printf( esc_html__( 'Create Staging %s', 'dollie' ), dollie()->string_variants()->get_site_type_string() );
						?>
					</button>
					<?php wp_nonce_field( 'wpd_staging_create' ); ?>
				</form>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</div>
