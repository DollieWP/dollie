<?php the_content(); ?>

<?php $status = \Dollie\Core\Modules\Container::instance()->get_status( get_the_ID() ); ?>

<?php

if ( 'stop' === $status ) :
	?>
	<?php $undeploy_at = get_post_meta( get_the_ID(), 'wpd_undeploy_container_at', true ); ?>
	<div class="dol-bg-orange-700 dol-text-center dol-p-8 md:dol-p-10 lg:dol-p-20 dol-rounded">
		<h2 class="dol-text-xl md:dol-text-3xl dol-mb-4 dol-text-white">
			<i class="fab fa-wordpress"></i>
			<?php esc_html_e( 'Hosting for this site is currently paused.', 'dollie' ); ?>
		</h2>
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

	<?php if ( get_field( 'wpd_custom_launch_splash', 'option' ) == true ) : ?>

		<?php
		wp_enqueue_script( 'swiper' );
		wp_enqueue_script( 'jquery-fitvids' );
		wp_print_styles( 'swiper' );
		?>

		<div class="dol-swiper-wrapper dol-relative">

			<!-- Slider main container -->
			<div class="swiper-container">
				<!-- Additional required wrapper -->
				<div class="swiper-wrapper">

					<?php if ( get_field( 'wpd_launch_step_1', 'option' ) ) : ?>
						<div class="swiper-slide dol-p-24 dol-items-center">
							<?php the_field( 'wpd_launch_step_1', 'option' ); ?>
						</div>
					<?php endif; ?>
					<?php if ( get_field( 'wpd_launch_step_2', 'option' ) ) : ?>
						<div class="swiper-slide dol-p-24 dol-items-center">
							<?php the_field( 'wpd_launch_step_2', 'option' ); ?>
						</div>
					<?php endif; ?>
					<?php if ( get_field( 'wpd_launch_step_3', 'option' ) ) : ?>
						<div class="swiper-slide dol-p-24 dol-items-center">
							<?php the_field( 'wpd_launch_step_3', 'option' ); ?>
						</div>
					<?php endif; ?>
					<?php if ( get_field( 'wpd_launch_step_4', 'option' ) ) : ?>
						<div class="swiper-slide dol-p-24 dol-items-center">
							<?php the_field( 'wpd_launch_step_4', 'option' ); ?>
						</div>
					<?php endif; ?>

				</div>
				<!-- If we need pagination -->
				<div class="swiper-pagination"></div>

				<!-- If we need navigation buttons -->
				<div class="swiper-button-prev"></div>
				<div class="swiper-button-next"></div>

			</div>
			<style>
				.swiper-container {
					max-width: 800px;
					height: 400px;
				}
			</style>

			<script>
				jQuery(document).ready(function() {

					const swiper = new Swiper('.swiper-container', {
						// Optional parameters
						loop: false,

						// If we need pagination
						pagination: {
							el: '.swiper-pagination',
						},

						// Navigation arrows
						navigation: {
							nextEl: '.swiper-button-next',
							prevEl: '.swiper-button-prev',
						},

					});

					jQuery(".swiper-slide").filter(function() {
						return !jQuery(this).find('.ast-oembed-container');
					}).fitVids();
				});
			</script>

		</div>

	<?php else : ?>
		<div class="dol-mt-1 dol-text-md dol-font-semibold div-loader-wrap dol-text-l dol-text-center dol-p-6">
			<div class="dol-py-8 dol-flex dol-flex-col dol-items-center dol-justify-center">
				<svg class="dol-animate-spin dol-h-16 dol-w-16 dol-text-flame-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
					<circle class="dol-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
					<path class="dol-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
				</svg>
			</div>

			<?php if ( dollie()->is_blueprint( $current_id ) ) : ?>
				<div class="dol-font-bold dol-mt-6 dol-text-2xl dol-uppercase">
					<?php esc_html_e( 'Launching New Blueprint', 'dollie' ); ?>
				</div>

				<div class="dol-mt-1 dol-text-md dol-font-semibold dol-text-gray-500">
					<?php esc_html_e( 'Once your Blueprint has been launched you can go ahead and install your favorite plugins and themes, to make the perfect pre-made site for your customers.', 'dollie' ); ?>
				</div>
			<?php else : ?>
				<div class="dol-font-bold dol-mt-6 dol-text-2xl dol-uppercase">
					<?php esc_html_e( 'Launching Your New Site', 'dollie' ); ?>
				</div>

				<div class="dol-mt-1 dol-text-md dol-font-semibold dol-text-gray-500">
					<?php esc_html_e( 'Your site will be ready to go in just a moment.', 'dollie' ); ?>
				</div>

				<div class="dol-text-gray-500 dol-text-sm dol-mt-6">
					<?php esc_html_e( 'Don\'t worry, we\'ll automatically reload this page once it\'s ready.', 'dollie' ); ?>
				</div>
				<div class="dol-text-gray-500 dol-text-sm">
					<?php esc_html_e( 'If you don\'t want to wait for a few seconds, you can navigate away. We got it covered!', 'dollie' ); ?>
				</div>
			<?php endif; ?>


		</div>

	<?php endif; ?>

<?php else : ?>

	<?php

	$post_id = get_the_ID();
	$install = get_queried_object()->post_name;

	$data     = \Dollie\Core\Modules\Container::instance()->get_container_details( get_the_ID() );
	$sub_page = get_query_var( 'sub_page' );

	// Include ACF form head.
	if ( in_array( $sub_page, [ 'blueprints' ] ) ) {
		acf_form_head();
	}

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
		if ( ! \Elementor\Plugin::instance()->editor->is_edit_mode() ) {
			wp_enqueue_script( 'dollie-site-content' );
		}

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
	} elseif ( 'staging' === $sub_page && dollie()->has_staging() ) {
		\Dollie\Core\Utils\Tpl::load(
			'widgets/site/pages/staging',
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
