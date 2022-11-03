<div class="dol-sites dol-relative dol--mx-2">
	<div class="dol-loader" data-for="pagination">
		<div class="dol-flex dol-items-center dol-justify-center dol-h-full">
			<svg class="dol-animate-spin dol-h-10 dol-w-10 dol-text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
				<circle class="dol-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
				<path class="dol-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
			</svg>
		</div>
	</div>
	<?php if ( $containers->have_posts() ) : ?>
		<div class="dol-sites-container">
			<?php while ( $containers->have_posts() ) : ?>
				<?php

				$containers->the_post();

				$container = dollie()->get_container( get_the_ID() );

				if ( is_wp_error( $container ) ) {
					continue;
				}

				$list_item_class      = array();
				$btn_controls_classes = array();

				$domain = $container->get_url();

				$data = array(
					'slug'       => $container->get_slug(),
					'domain'     => $container->get_url(),
					'name'       => $container->get_title(),
					'wp_version' => $container->get_wp_version(),
					'is_running' => 'Running' === $container->get_status(),
				);


				if ( $container->is_blueprint() ) {
					$list_item_class[] = 'dol-blueprint-site';
					$blueprint_title   = $container->get_saved_title();
					if ( $blueprint_title ) {
						$data['name'] = $blueprint_title;
					}
				}

				$btn_controls_classes[] = 'dol-hidden';
				$list_item_class[]      = 'dol-sites-item-locked';

				$btn_controls_classes = implode( ' ', $btn_controls_classes );
				$list_item_class      = implode( ' ', $list_item_class );

				?>
				<div class="dol-sites-item <?php echo esc_attr( $list_item_class ); ?>" data-site-name="<?php echo esc_attr( $domain ); ?>">
					<div class="dol-sites-item-inner">

						<div class="dol-sites-image dol-relative">
							<div class="dol-sites-image-box">
								<img width="700" class="dol-block dol-object-cover" alt="<?php echo $container->get_url(); ?>" src="<?php echo $container->get_screenshot(); ?>">
							</div>

							<div class="dol-sites-status">
								<?php if ( $container->is_running() ) : ?>
									<span class="dol-flex dol-h-4 dol-w-4 dol-relative">
										<span class="dol-animate-ping dol-absolute dol-inline-flex dol-h-full dol-w-full dol-rounded-full dol-bg-green-500 dol-opacity-75"></span>
										<span class="dol-relative dol-inline-flex dol-rounded-full dol-h-4 dol-w-4 dol-bg-green-600"></span>
									</span>
								<?php elseif ( $container->is_deploying() ) : ?>
									<span class="dol-flex dol-h-4 dol-w-4 dol-relative">
										<span class="dol-animate-ping dol-absolute dol-inline-flex dol-h-full dol-w-full dol-rounded-full dol-bg-yellow-600 dol-opacity-75"></span>
										<span class="dol-relative dol-inline-flex dol-rounded-full dol-h-4 dol-w-4 dol-bg-yellow-600"></span>
									</span>
								<?php else : ?>
									<span class="dol-flex dol-h-4 dol-w-4 dol-relative">
										<span class="dol-animate-ping dol-absolute dol-inline-flex dol-h-full dol-w-full dol-rounded-full dol-bg-red-500 dol-opacity-75"></span>
										<span class="dol-relative dol-inline-flex dol-rounded-full dol-h-4 dol-w-4 dol-bg-red-600"></span>
									</span>
								<?php endif; ?>
							</div>
						</div>
						<div class="dol-sites-name">
							<div class="dol-px-4">
								<div class="dol-font-bold dol-text-lg dol-cursor-default dol-truncate">
									<a class="dol-item-name dol-text-normal dol-leading-normal dol-truncate dol-text-gray-600" href="<?php echo $container->get_url(); ?>" title="<?php echo esc_attr( $data['name'] ); ?>">
										<?php echo esc_html( $data['name'] ); ?>
									</a>
								</div>

								<div class="dol-flex dol-items-center dol-truncate">
									<a class="dol-item-url dol-text-brand-500 hover:dol-text-brand-600 dol-text-sm dol-leading-normal dol-truncate" href="<?php echo $container->get_url(); ?>" title="<?php echo esc_html( $data['domain'] ); ?>" target="_blank">
										<?php echo esc_html( $data['domain'] ); ?>
									</a>
								</div>
							</div>
						</div>
						<div class="dol-sites-version dol-cursor-default dol-text-sm">
							<?php if ( ! $container->is_deploying() ) : ?>
								<div class="dol-font-semibold dol-text-gray-500">
									<?php esc_html_e( 'WordPress', 'dollie' ); ?>
								</div>
								<div class="dol-font-bold ">
									<?php printf( __( 'Version %s', 'dollie' ), $data['wp_version'] ); ?>
								</div>
							<?php endif; ?>
						</div>
						<?php if ( $container->is_blueprint() && ! $container->is_deploying() ) : ?>
							<div class="dol-sites-client dol-cursor-default dol-text-sm">
								<div class="dol-font-semibold dol-text-gray-500">
									<?php esc_html_e( 'Blueprint Updated', 'dollie' ); ?>
								</div>
								<div class="dol-font-bold">
									<?php if ( $container->get_changes_update_time() ) : ?>
										<?php echo $container->get_changes_update_time(); ?>
									<?php else : ?>
										<a class="dol-link" href="<?php echo $container->get_permalink( 'blueprints' ); ?>">
											<?php esc_html_e( 'Never. Update now!', 'dollie' ); ?>
										</a>
									<?php endif; ?>
								</div>
							</div>
						<?php else : ?>
							<div class="dol-sites-client dol-cursor-default dol-text-sm">
								<div class="dol-font-semibold dol-text-gray-500">
									<?php echo dollie()->string_variants()->get_user_type_string(); ?>
								</div>
								<div class="dol-font-bold ">
									<?php echo $container->get_author_name(); ?>
								</div>
							</div>
						<?php endif; ?>
						<div class="dol-sites-controls">
							<?php if ( $container->is_deploying() ) : ?>
								<a class="dol-inline-block dol-text-sm dol-text-white dol-bg-primary dol-rounded dol-px-3 dol-py-2 hover:dol-text-white hover:dol-bg-primary-600" href="<?php echo $container->get_permalink(); ?>">
									<?php esc_html_e( 'View progress' ); ?>
								</a>
							<?php else : ?>
								<a class="dol-inline-block dol-text-sm dol-text-white dol-bg-primary dol-rounded dol-px-3 dol-py-2 hover:dol-text-white hover:dol-bg-primary-600" href="<?php echo $container->get_permalink(); ?>" data-tooltip="<?php echo esc_attr__( 'Manage', 'dollie' ); ?>">
									<i data-tooltip="Carefully crafted site designs made by our team which you can use as a starting point for your new site." class="fas fa-cog svg-tooltip acf__tooltip"></i>
								</a>
								<?php if ( $container->is_blueprint() ) : ?>
									<a class="dol-inline-block dol-text-sm dol-text-white dol-bg-primary dol-rounded dol-px-3 dol-py-2 hover:dol-text-white hover:dol-bg-primary-600" href="<?php echo $container->get_permalink( 'blueprints' ); ?>" data-tooltip="<?php echo esc_attr__( 'Update Blueprint', 'dollie' ); ?>">
										<?php echo dollie()->icon()->refresh(); ?>
									</a>
								<?php endif; ?>

								<?php
								$staging_url = get_post_meta( get_the_ID(), '_wpd_staging_url', true );
								$login_link  = $container->get_customer_login_url();
								?>

								<?php if ( $staging_url ) : ?>
									<a class="dol-inline-block dol-text-sm dol-text-white dol-bg-primary dol-rounded dol-px-3 dol-py-2 hover:dol-text-white hover:dol-bg-primary-600" href="<?php echo $container->get_permalink( 'staging' ); ?>" data-tooltip="<?php echo esc_attr__( 'Visit Staging Area', 'dollie' ); ?>">
										<?php echo dollie()->icon()->staging(); ?>
									</a>
								<?php endif; ?>

								<?php if ( ! empty( $login_link ) ) : ?>
									<a class="dol-inline-block dol-text-sm dol-text-gray-500 dol-bg-gray-200 dol-rounded dol-px-3 dol-py-2 hover:dol-text-white hover:dol-bg-secondary" href="<?php echo esc_url( $login_link ); ?>" data-tooltip="<?php printf( esc_html__( 'Login to %s as Admin', 'dollie' ), dollie()->string_variants()->get_site_type_string() ); ?>">
										<?php echo dollie()->icon()->site_login(); ?>
									</a>
								<?php endif; ?>
							<?php endif; ?>
						</div>
					</div>
				</div>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
		</div>

	<?php else : ?>
		<div class="dol-flex dol-items-center dol-justify-center">
			<div class="dol-text-2xl dol-text-primary-600">
				<?php

				$data = [
					'title'    => sprintf( esc_html__( 'Ready to Launch your first %s?', 'dollie' ), dollie()->string_variants()->get_site_type_string() ),
					'subtitle' => sprintf( esc_html__( 'Launching your first %s via your own Platform is something special. What are you waiting for, lets launch your first site!', 'dollie' ), dollie()->string_variants()->get_site_type_string() ),
					'button'   => esc_html__( 'Launch Now', 'dollie' ),
				];

				dollie()->load_template( 'widgets/dashboard/launch-site', $data, true );

				?>
			</div>
		</div>
	<?php endif; ?>
</div>
