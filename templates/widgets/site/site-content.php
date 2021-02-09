<?php the_content(); ?>

<?php $status = \Dollie\Core\Modules\Container::instance()->get_status( get_the_ID() ); ?>

<?php if ( 'stop' === $status ) : ?>
	<?php $undeploy_at = get_post_meta( get_the_ID(), 'wpd_undeploy_container_at', true ); ?>
	<div class="dol-bg-orange-700 dol-text-center dol-p-8 md:dol-p-10 lg:dol-p-20 dol-rounded">
		<h2 class="dol-text-xl md:dol-text-3xl dol-mb-4 dol-text-white">
			<i class="fab fa-wordpress"></i>
			<?php esc_html_e( 'Hosting for this site is currently paused.', 'dollie' ); ?>
		</h2>

		<?php if ( ! dollie()->has_subscription() ) : ?>

		<?php endif; ?>
	</div>
	<div class="hero-section">
		<div class="hero-inner">
			<div class="content content-blank">
				<div class="text-center py-30">
					<div class="mb-20">
						<h2 class="text-white">
							<i class="fab fa-wordpress"></i> Hosting for this site is currently paused.
						</h2>
					</div>
					<p class="mt-20 mb-20 pl-100 pr-100 h5 font-size-large text-gray">
						If you have cancelled your service subscription by accident please re-activate your
						<a href="<?php echo get_site_url(); ?>/my-account/subscriptions" class="text-white">subscription</a>
						before <strong><?php echo date( 'F j, Y', $undeploy_at ); ?></strong> to prevent this site from
						being removed completely.
					</p>
					<p class="pl-100 pr-100 text-gray text-muted">
						Stopped this site by mistake or think something went wrong? <br>Please reach out to our team and
						we'll get back to you as soon as we can!
					</p>
				</div>
			</div>
		</div>
	</div>
<?php elseif ( $status === 'failed' ) : ?>
	<div class="hero-section">
		<div class="hero-inner">
			<div class="content content-blank">
				<div class="text-center py-30">
					<?php if ( current_user_can( 'manage_options' ) ) : ?>
						<div class="mb-20">
							<h2 class="text-white">Admin Notice - This site has failed to launch</h2>
						</div>
						<p class="mt-20 mb-20 pl-100 pr-100 h5 font-size-large text-gray">
							When a site fails to deploy it usually means there is a misconfiguration in your
							Dollie API settings. Please double check your email, password and domain
							<a class="text-white" href="<?php echo admin_url( 'admin.php?page=wpd_platform_setup' ); ?>" data-clear="text-white">settings</a>.
							Finally you can also check the Dollie Logs or reach out to the Dollie Support
							team
							via the <a class="text-white" href="https://partners.getdollie.com">Partner
								Dashboard</a>
						</p>
					<?php else : ?>
						<div class="mb-20">
							<h2 class="text-white">
								<i class="fab fa-wordpress"></i>
								Sorry, there was an error launching your site.
							</h2>
						</div>
						<p class="mt-20 mb-20 pl-100 pr-100 h5 font-size-large text-gray">
							It seems like we were unable to launch your new site on our platform at this time.
							Our team is notified and will be looking into this issue immediately and will reach out to
							you as soon as we can.
						</p>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
<?php elseif ( 'pending' === $status || isset( $_GET['launch-splash-preview'] ) ) : ?>

	<?php

	if ( ! \Elementor\Plugin::instance()->editor->is_edit_mode() ) {
		wp_enqueue_script( 'dollie-site-content' );
	}

	if ( get_field( 'wpd_custom_launch_splash', 'option' ) === true ) {
		wp_enqueue_script( 'dollie-custom-launch' );
	}

	?>

	<div id="dol-deploying-site" class="dol-hidden" data-container="<?php echo esc_attr( get_the_ID() ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'check_deploy_nonce' ) ); ?>" data-ajax-url="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>"></div>
	<div class="dol-py-32 dol-flex dol-flex-col dol-items-center dol-justify-center">
		<svg class="dol-animate-spin dol-h-16 dol-w-16 dol-text-flame-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
			<circle class="dol-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
			<path class="dol-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
		</svg>

		<div class="dol-mt-1 dol-text-md dol-font-semibold div-loader-wrap dol-text-l dol-text-center dol-p-6">
			<?php if ( get_field( 'wpd_custom_launch_splash', 'option' ) == true ) { ?>
				<div class="block-content mt-50 text-align-center pb-30 pl-50 pr-50 nice-copy-story dol-px-20">
					<div id="dollie-content-1">
						<?php the_field( 'wpd_launch_step_1', 'option' ); ?>
					</div>
					<div id="dollie-content-2" style="display: none;">
						<?php the_field( 'wpd_launch_step_2', 'option' ); ?>
					</div>

					<div id="dollie-content-3" style="display: none;">
						<?php the_field( 'wpd_launch_step_3', 'option' ); ?>
					</div>

					<div id="dollie-content-4" style="display: none;">
						<?php the_field( 'wpd_launch_step_4', 'option' ); ?>
					</div>
				</div>
			<?php } else { ?>
				<div class="dol-font-bold dol-mt-6 dol-text-2xl dol-uppercase">
					<?php esc_html_e( 'Launching Your New Site', 'dollie' ); ?>
				</div>
				<div class="dol-mt-1 dol-text-md dol-font-semibold dol-text-gray-500">
					<?php esc_html_e( 'Your site will be ready to go in just a moment.', 'dollie' ); ?>
			<?php } ?>
				</div>

				<div class="dol-text-gray-500 dol-text-sm dol-mt-6">
					<?php esc_html_e( 'Don\'t worry, we\'ll automatically reload this page once it\'s ready.', 'dollie' ); ?>
				</div>
				<div class="dol-text-gray-500 dol-text-sm">
					<?php esc_html_e( 'If you don\'t want to wait for a few seconds, you can navigate away. We got it covered!', 'dollie' ); ?>
				</div>
		</div>
	<?php else : ?>

		<?php

		$post_id = get_the_ID();
		$install = get_queried_object()->post_name;

		// Include ACF editor for blueprints
		if ( get_query_var( 'blueprints' ) || current_user_can( 'edit_pages' ) ) {
			acf_form_head();
		}

		$data     = \Dollie\Core\Modules\Container::instance()->get_container_details( get_the_ID() );
		$sub_page = get_query_var( 'sub_page' );

		if ( 'plugins' === $sub_page ) {
			\Dollie\Core\Utils\Tpl::load(
				'widgets/site/pages/plugins',
				[
					'data'    => $data,
					'install' => $install,
				],
				true
			);
		} elseif ( 'themes' === $sub_page ) {
			\Dollie\Core\Utils\Tpl::load(
				'widgets/site/pages/themes',
				[
					'data' => $data,
				],
				true
			);
		} elseif ( 'domains' === $sub_page ) {
			\Dollie\Core\Utils\Tpl::load(
				'widgets/site/pages/domains',
				[
					'data' => $data,
				],
				true
			);
		} elseif ( 'migrate' === $sub_page ) {
			\Dollie\Core\Utils\Tpl::load(
				'widgets/site/pages/migrate',
				[
					'data' => $data,
				],
				true
			);
		} elseif ( 'backups' === $sub_page ) {
			\Dollie\Core\Utils\Tpl::load(
				'widgets/site/pages/backups',
				[
					'data' => $data,
				],
				true
			);
		} elseif ( 'updates' === $sub_page ) {
			\Dollie\Core\Utils\Tpl::load(
				'widgets/site/pages/updates',
				[
					'data' => $data,
				],
				true
			);
		} elseif ( 'developer-tools' === $sub_page ) {
			\Dollie\Core\Utils\Tpl::load(
				'widgets/site/pages/developer-tools',
				[
					'data' => $data,
				],
				true
			);
		} elseif ( 'blueprints' === $sub_page ) {
			\Dollie\Core\Utils\Tpl::load(
				'widgets/site/pages/blueprints',
				[
					'data'    => $data,
					'post_id' => $post_id,
				],
				true
			);
		} elseif ( 'delete' === $sub_page ) {
			\Dollie\Core\Utils\Tpl::load(
				'widgets/site/pages/delete',
				[
					'data' => $data,
				],
				true
			);
		} else {
			\Dollie\Core\Utils\Tpl::load(
				'widgets/site/pages/dashboard',
				[
					'data'    => $data,
					'install' => $install,
					'post_id' => $post_id,
				],
				true
			);
		}

		?>

		<?php
endif;
