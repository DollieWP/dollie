<?php the_content(); ?>

<?php

$container = dollie()->get_container( dollie()->get_current_post_id() );

if ( is_wp_error( $container ) || is_wp_error( $container->get_details() ) ) {
	dollie()->load_template(
		'widgets/site/pages/error',
		[
			'container'         => $container,
			'invalid_container' => is_wp_error( $container ),
			'missing_details'   => ! is_wp_error( $container ) && is_wp_error( $container->get_details() ),
		],
		true
	);

	return;
}

?>

<?php if ( $container->is_scheduled_for_deletion() ) : ?>
    <div class="dol-rounded dol-overflow-hidden dol-shadow dol-mb-6">
        <div class="dol-p-4 lg:dol-px-8 lg:dol-py-4 dol-bg-gray-200">
            <h4 class="dol-p-0 dol-m-0 dol-text-base md:dol-text-xl">
				<?php echo dollie()->icon()->wordpress( 'dol-mr-1' ); ?>
				<?php if ( $container->is_site() ) : ?>
					<?php esc_html_e( 'Your site has been deleted.', 'dollie' ); ?>
				<?php elseif ( $container->is_blueprint() ) : ?>
					<?php esc_html_e( 'Your blueprint has been deleted.', 'dollie' ); ?>
				<?php else : ?>
					<?php esc_html_e( 'Your staging site has been deleted.', 'dollie' ); ?>
				<?php endif; ?>
            </h4>
        </div>
        <div class="dol-p-4 lg:dol-px-8 lg:dol-py-6">
            <p class="mt-20 mb-20 pl-100 pr-100 h5 font-size-large text-gray">
				<?php printf( esc_html__( 'This instance has been removed and is no longer accessible. There is a %s grace period when you\'ll be able restore it. When the time runs out, it will be completely gone.' ), human_time_diff( current_time( 'timestamp' ), $container->get_details( 'deleted_at' ) + ( 5 * 86400 ) ) ); ?>
            </p>
            <form action="<?php echo $container->get_permalink( '', [ 'restore_container' => 'yes' ] ); ?>"
                  method="post">
                <button name="restore_container" type="submit"
                        class="dol-px-4 dol-py-2 dol-bg-primary-600 dol-text-white dol-rounded">
					<?php echo dollie()->icon()->refresh( 'dol-mr-1' ); ?>
					<?php esc_html_e( 'Restore', 'dollie' ); ?>
                </button>
            </form>
        </div>
    </div>
<?php elseif ( $container->is_stopped() ) : ?>
    <div class="dol-rounded dol-overflow-hidden dol-shadow dol-mb-6">
        <div class="dol-p-4 lg:dol-px-8 lg:dol-py-4 dol-bg-gray-200">
            <h4 class="dol-p-0 dol-m-0 dol-text-base md:dol-text-xl">
				<?php echo dollie()->icon()->wordpress( 'dol-mr-1' ); ?>
				<?php esc_html_e( 'Hosting for this site is currently paused!', 'dollie' ); ?>
            </h4>
        </div>

        <div class="dol-p-4 lg:dol-px-8 lg:dol-py-6">
            <p>
				<?php
				echo wp_kses_post(
					sprintf(
						__( 'If you have cancelled your subscription by accident please re-activate your <a href="%s">subscription</a> to prevent this site from being removed completely.', 'dollie' ),
						get_permalink( wc_get_page_id( 'myaccount' ) )
					)
				)
				?>
            </p>

            <h5 class="dol-font-bold dol-mt-6">
				<?php esc_html_e( 'Do you think this is a mistake?', 'dollie' ); ?>
            </h5>

            <p>
				<?php esc_html_e( 'Please reach out to our team and we\'ll get back to you as soon as we can!', 'dollie' ); ?>
            </p>
        </div>
    </div>
<?php elseif ( $container->is_failed() ) : ?>
    <div class="hero-section">
    <div class="hero-inner">
    <div class="content content-blank">
        <div class="text-center py-30">
			<?php if ( current_user_can( 'manage_options' ) ) : ?>
                <div class="mb-20">
                    <h2 class="text-white">
						<?php
						echo dollie()->icon()->close();
						printf( esc_html__( 'Admin Notice - This %s has failed to launch', 'dollie-setup' ), dollie()->string_variants()->get_site_type_string() );

						?>
                    </h2>
                </div>
                <p class="mt-20 mb-20 pl-100 pr-100 h5 font-size-large text-gray">
					<?php

					printf(
						esc_html__(
							'When a %s fails to launch in your Hub it usually means there is a misconfiguration in your
							Dollie Cloud settings. Usually re-connecting to Dollie Cloud solves these issues.',
							'dollie-setup'
						),
						dollie()->string_variants()->get_site_type_string()
					);

					?>
                    <br>
                    <br>
                    <a class="text-white dol-btn dol-btn-primary"
                       href="<?php echo admin_url( 'admin.php?page=wpd_platform_setup' ); ?>" data-clear="text-white">Reset
                        Dollie Cloud Connection</a>
                    <br><br>

					<?php _e( 'Still having issues? Reach out to the Dollie Support team via your <a class="text-white" href="https://dashboard.getdollie.com">Dollie Dashboard</a>', 'dollie' ); ?>
                </p>
			<?php else : ?>
                <div class="mb-20">
                    <h2 class="text-white">
						<?php

						echo dollie()->icon()->close();
						printf( esc_html__( 'Sorry, there was an error launching your %s', 'dollie-setup' ), dollie()->string_variants()->get_site_type_string() );

						?>
                    </h2>
                </div>
                <p class="mt-20 mb-20 pl-100 pr-100 h5 font-size-large text-gray">
					<?php

					printf(
						esc_html__( 'We were unable to launch your new %s on our platform at this time. Our team has been notified and will be looking into this issue immediately. We will reach out to you as soon as we can.', 'dollie-setup' ),
						dollie()->string_variants()->get_site_type_string()
					);

					?>
                </p>
			<?php endif; ?>
        </div>
    </div>
<?php elseif ( $container->is_deploying() || isset( $_GET['launch-splash-preview'] ) ) : ?>

	<?php

	if ( ! dollie()->is_elementor_editor() ) {
		wp_enqueue_script( 'dollie-site-content' );
	}

	if ( get_field( 'wpd_custom_launch_splash', 'option' ) === true ) {
		wp_enqueue_script( 'dollie-custom-launch' );
	}

	dollie()->load_template( 'parts/site-deploying-checker', [], true );

	?>


    <div class="dol-mt-1 dol-text-md div-loader-wrap dol-text-l dol-text-center dol-p-6">
        <div class="dol-py-8 dol-flex dol-flex-col dol-items-center dol-justify-center">
            <svg class="dol-animate-spin dol-h-16 dol-w-16 dol-text-flame-600" xmlns="http://www.w3.org/2000/svg"
                 fill="none" viewBox="0 0 24 24">
                <circle class="dol-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="dol-opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>

		<?php if ( $container->is_blueprint() ) : ?>
            <div class="dol-font-bold dol-mt-6 dol-text-2xl dol-uppercase">
				<?php esc_html_e( 'Launching New Blueprint', 'dollie' ); ?>
            </div>

            <div class="dol-mt-1 dol-text-md dol-text-gray-500">
				<?php esc_html_e( 'Once your Blueprint has been launched you can go ahead and install your favorite plugins and themes, to make the perfect pre-made site for your customers.', 'dollie' ); ?>
            </div>
		<?php else : ?>
            <div class="dol-font-bold dol-mt-6 dol-text-2xl dol-uppercase">
				<?php printf( esc_html__( 'Launching Your New %s', 'dollie-setup' ), dollie()->string_variants()->get_site_type_string() ); ?>
            </div>

            <div class="dol-mt-1 dol-text-md dol-text-gray-500">
				<?php printf( esc_html__( 'Your %s will be ready to go in just a moment.', 'dollie-setup' ), dollie()->string_variants()->get_site_type_string() ); ?>
            </div>

            <div class="dol-text-gray-500 dol-text-sm dol-mt-6">
				<?php esc_html_e( 'Don\'t worry, we\'ll automatically reload this page once it\'s ready.', 'dollie' ); ?>
            </div>
            <div class="dol-text-gray-500 dol-text-sm">
				<?php esc_html_e( 'If you don\'t want to wait for a few seconds, you can navigate away. We got it covered!', 'dollie' ); ?>
            </div>
		<?php endif; ?>
    </div>


<?php else : ?>

	<?php

	$pages = [
		'plugins',
		'themes',
		'domains',
		'updates',
		'migrate',
		'backups',
		'developer-tools',
		'blueprints',
		'delete',
		'staging',
	];

	$sub_page = get_query_var( 'sub_page' );

	if ( $sub_page && in_array( $sub_page, $pages, true ) ) {
		if ( in_array( $sub_page, [ 'blueprints', 'domains' ] ) && ! dollie()->is_elementor_editor() ) {
			wp_enqueue_script( 'dollie-site-content' );
		}

		if ( in_array( $sub_page, [ 'blueprints' ] ) ) {
			acf_form_head();
		}

		dollie()->load_template(
			"widgets/site/pages/{$sub_page}",
			[
				'container' => $container,
			],
			true
		);
	} else {
		dollie()->load_template(
			'widgets/site/pages/dashboard',
			[
				'container' => $container,
			],
			true
		);
	}

	?>

<?php
endif;
