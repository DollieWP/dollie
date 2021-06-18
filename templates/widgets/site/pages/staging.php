<div id="dol-staging-panel">
	<?php

	if ( ! \Elementor\Plugin::instance()->editor->is_edit_mode() ) {
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

	$status_title   = __( 'Staging site is disabled', 'dollie' );
	$status_message = sprintf( __( 'Here you can easily manage your staging site for <strong>%s</strong>. Staging allows you to conduct different tests before applying them to your live site.', 'dollie' ), $container->post_name );

	if ( 'pending' === $staging_status ) {
		$status_title   = __( 'Staging site is being created', 'dollie' );
		$status_message = sprintf( __( 'Please pe patient while we create a staging site for <strong>%s</strong>.', 'dollie' ), $container->post_name );

	} elseif ( 'live' === $staging_status ) {
		$status_title = sprintf( __( 'Staging site: %s', 'dollie' ), $staging_url );
	}

	\Dollie\Core\Utils\Tpl::load(
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
		<div id="dol-deploying-site" class="dol-hidden" data-container="<?php echo esc_attr( get_the_ID() ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'check_deploy_nonce' ) ); ?>" data-ajax-url="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>" data-staging="1"></div>
	<?php elseif ( 'live' === $staging_status ) : ?>
		<div class="dol-mt-6">
			<button class="dol-staging-btn" data-container="<?php echo esc_attr( get_the_ID() ); ?>" data-ajax-url="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>" data-action="dollie_sync_staging" data-nonce="<?php echo esc_attr( wp_create_nonce( 'sync_staging_nonce' ) ); ?>">
				<?php esc_html_e( 'Publish Staging to Live', 'dollie' ); ?>
			</button>
			<a href="<?php echo dollie()->get_customer_login_url( get_the_ID(), null, true ); ?>">
				<?php esc_html_e( 'Admin Panel', 'dollie' ); ?>
			</button>
			<form action="" method="post">
				<input type="hidden" name="undeploy_staging" value="1">

				<button type="submit" id="staging-form-submit">
					<?php esc_html_e( 'Remove Staging Site', 'dollie' ); ?>
				</button>
				<?php wp_nonce_field( 'wpd_staging_undeploy' ); ?>
			</form>
		</div>
	<?php elseif ( 'disabled' === $staging_status ) : ?>
		<?php if ( dollie()->staging_sites_limit_reached() ) : ?>
			<div class="dol-mt-6">
				<?php
					\Dollie\Core\Utils\Tpl::load(
						'notice',
						[
							'icon'  => 'fas fa-exclamation-circle',
							'type'  => 'error',
							'title' => esc_html__( 'You have reached your staging sites limit. Please upgrade your subscription!', 'dollie' ),
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

					<button type="submit" id="staging-form-submit">
						<?php esc_html_e( 'Create Staging Site', 'dollie' ); ?>
					</button>
					<?php wp_nonce_field( 'wpd_staging_create' ); ?>
				</form>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</div>
