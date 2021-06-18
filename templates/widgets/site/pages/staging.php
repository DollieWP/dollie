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
		$status_message = sprintf( __( 'Please pe patient while we create a staging site for <strong>%s</strong>. The process might take up to 5 minutes.', 'dollie' ), $container->post_name );

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

	<?php if ( isset( $_GET['sync'] ) ) : ?>
		<?php if ( 'success' === $_GET['sync'] ) : ?>
			<div class="dol-my-3 dol-bg-green-500 dol-text-white dol-rounded dol-px-6 dol-py-3">
				<?php esc_html_e( 'Syncing is in process. You will be able to see your changes on the production site in a few minutes.', 'dollie' ); ?>
			</div>
		<?php elseif ( 'failed' === $_GET['sync'] ) : ?>
			<div class="dol-my-3 dol-bg-red-500 dol-text-white dol-rounded dol-px-6 dol-py-3">
				<?php esc_html_e( 'Syncing failed. Please try again.', 'dollie' ); ?>
			</div>
		<?php endif; ?>
	<?php endif; ?>

	<?php if ( 'pending' === $staging_status ) : ?>
		<div id="dol-deploying-site" class="dol-hidden" data-container="<?php echo esc_attr( get_the_ID() ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'check_deploy_nonce' ) ); ?>" data-ajax-url="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>" data-staging="1"></div>
	<?php elseif ( 'live' === $staging_status ) : ?>
		<div class="dol-mt-6">
			<form action="" method="post" class="dol-inline-block">
				<input type="hidden" name="sync_staging" value="1">

				<button type="submit" class="dol-bg-green-500 hover:dol-bg-green-600 focus:dol-bg-green-600 dol-text-white hover:dol-text-white focus:dol-outline-none dol-border-0 dol-rounded dol-px-4 dol-py-2">
					<i class="fas fa-sync dol-text-white dol-mr-1"></i>
					<?php esc_html_e( 'Publish To Live', 'dollie' ); ?>
				</button>
				<?php wp_nonce_field( 'wpd_staging_sync' ); ?>
			</form>
			<a href="<?php echo dollie()->get_customer_login_url( get_the_ID(), null, true ); ?>" target="_blank" class="dol-inline-block dol-bg-gray-500 hover:dol-bg-gray-600 focus:dol-bg-gray-600 dol-text-white hover:dol-text-white focus:dol-outline-none dol-border-0 dol-rounded dol-px-4 dol-py-2">
				<i class="fas fa-tools dol-mr-1"></i>
				<?php esc_html_e( 'Admin', 'dollie' ); ?>
			</a>
			<form action="" method="post" class="dol-inline-block">
				<input type="hidden" name="undeploy_staging" value="1">

				<button type="submit" class="dol-bg-red-500 hover:dol-bg-red-600 focus:dol-bg-red-600 dol-text-white hover:dol-text-white focus:dol-outline-none dol-border-0 dol-rounded dol-px-4 dol-py-2">
					<i class="fas fa-times dol-text-white dol-mr-1"></i>
					<?php esc_html_e( 'Delete', 'dollie' ); ?>
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

					<button type="submit" class="dol-bg-primary-500 hover:dol-bg-primary-600 focus:dol-bg-primary-600 dol-text-white hover:dol-text-white focus:dol-outline-none dol-border-0 dol-rounded dol-px-4 dol-py-2">
						<i class="fas fa-rocket dol-text-white dol-mr-1"></i>
						<?php esc_html_e( 'Create Staging Site', 'dollie' ); ?>
					</button>
					<?php wp_nonce_field( 'wpd_staging_create' ); ?>
				</form>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</div>
