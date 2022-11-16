<?php
	$migration = get_option( 'wpd_onboarding_migrate_site_url' );
	$url       = preg_replace( '(^https?://)', '', $migration );
	$charge    = get_option( 'options_wpd_charge_for_deployments' );
	$temp      = explode( '.', $url );
	unset( $temp[ count( $temp ) - 1 ] );
	$migration = implode( '.', $temp );

	$launch_url = get_permalink( dollie()->page()->get_launch_site_id() ) . '?default_site_url=' . sanitize_title( $migration ) . '&redirect=migrate';

	$blueprint            = get_option( 'wpd_onboarding_blueprint_name' );
	$launch_blueprint_url = get_permalink( dollie()->page()->get_launch_blueprint_id() ) . '?default_site_url=' . sanitize_title( $blueprint );
?>

<div class="dol-bg-white dol-shadow dol-overflow-hidden sm:dol-rounded-md">
	<ul role="list" class="dol-divide-y dol-divide-gray-200">
		<?php if ( ! dollie()->count_total_sites() ) : ?>
			<?php if ( $migration ) : ?>
				<h4 class="dol-text-gray-500 dol-pl-4 text-s  dol-uppercase dol-tracking-wide dol-mb-1 dol-text-m">
					<?php esc_html_e( 'Step 1 - Migrate Your First Site', 'dollie' ); ?>
				</h4>
			<?php endif; ?>

			<li>
				<a href="<?php echo $launch_url; ?>" class="dol-block hover:dol-bg-gray-50">
					<div class="dol-flex dol-items-center dol-px-4 dol-py-4 sm:dol-px-6">
						<div class="dol-min-w-0 dol-flex-1 dol-flex dol-items-center">
							<div class="dol-flex-shrink-0">
								<img class="dol-h-6 dol-w-6 dol-rounded-full dol-bg-primary-400 dol-p-2" src="<?php echo DOLLIE_ASSETS_URL; ?>/img/active.png" alt="">
							</div>
							<div class="dol-min-w-0 dol-flex-1 dol-px-4 md:dol-grid md:dol-grid-cols-1 md:dol-gap-4">
								<div>
									<p class="dol-text-sm dol-font-medium dol-text-primary-600 dol-truncate">
									<?php if ( $migration ) : ?>
										<?php printf( __( 'Migrate %s to Your Platform', 'dollie' ), $migration ); ?>
									<?php else : ?>
										<?php esc_html_e( 'Launch Your First Site on Your Platform', 'dollie' ); ?>
									<?php endif; ?>
									</p>
									<p class="dol-mt-2 dol-flex dol-items-center dol-text-sm dol-text-gray-500">
									<?php if ( $migration ) : ?>
										<?php esc_html_e( 'Click here to get started with this.', 'dollie' ); ?>
									<?php else : ?>
										<?php esc_html_e( 'Let\'s launch your first WordPress site on your platform!', 'dollie' ); ?>
									<?php endif; ?>
									</p>
								</div>
							</div>
						</div>
						<div>
							<svg class="dol-h-5 dol-w-5 dol-text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
								<path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
							</svg>
						</div>
					</div>
				</a>
			</li>
		<?php endif; ?>

		<?php if ( ! dollie()->count_total_created_blueprints() && $blueprint ) : ?>
			<h4 class="dol-text-gray-500 dol-pl-4 text-s  dol-uppercase dol-tracking-wide dol-mb-1 dol-text-m">
				<?php esc_html_e( 'Step 1 - Launch Your Agency Blueprint', 'dollie' ); ?>
			</h4>

			<li>
				<a href="<?php echo esc_url( $launch_blueprint_url ); ?>" class="dol-block hover:dol-bg-gray-50">
					<div class="dol-flex dol-items-center dol-px-4 dol-py-4 sm:dol-px-6">
						<div class="dol-min-w-0 dol-flex-1 dol-flex dol-items-center">
							<div class="dol-flex-shrink-0">
								<img class="dol-h-6 dol-w-6 dol-rounded-full dol-bg-primary-400 dol-p-2" src="<?php echo DOLLIE_ASSETS_URL; ?>/img/active.png" alt="">
							</div>
							<div class="dol-min-w-0 dol-flex-1 dol-px-4 md:dol-grid md:dol-grid-cols-1 md:dol-gap-4">
								<div>
									<p class="dol-text-sm dol-font-medium dol-text-primary-600 dol-truncate">
										<?php printf( __( 'Blueprint Setup - Let\'s set up "%s"', 'dollie' ), $blueprint ); ?>
									</p>
									<p class="dol-mt-2 dol-flex dol-items-center dol-text-sm dol-text-gray-500">
										<?php esc_html_e( 'Save hours of setup time by creating your first Blueprint.', 'dollie' ); ?>
									</p>
								</div>
							</div>
						</div>
						<div>
							<svg class="dol-h-5 dol-w-5 dol-text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
								<path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
							</svg>
						</div>
					</div>
				</a>
			</li>
		<?php endif; ?>
	</ul>
</div>

<div class="dol-bg-white dol-shadow dol-overflow-hidden sm:dol-rounded-md">
	<ul role="list" class="dol-divide-y dol-divide-gray-200">
		<?php if ( dollie()->auth()->is_connected() ) : ?>
			<li>
				<a href="#" class="dol-block hover:dol-bg-gray-50">
					<div class="dol-flex dol-items-center dol-px-4 dol-py-4 sm:dol-px-6">
						<div class="dol-min-w-0 dol-flex-1 dol-flex dol-items-center">
							<div class="dol-flex-shrink-0">
								<img class="dol-h-6 dol-w-6 dol-rounded-full dol-bg-green-400 dol-p-2" src="<?php echo DOLLIE_ASSETS_URL; ?>/img/active.png" alt="">
							</div>
							<div class="dol-min-w-0 dol-flex-1 dol-px-4 md:dol-grid md:dol-grid-cols-2 md:dol-gap-4">
								<div>
									<p class="dol-text-sm dol-font-medium dol-text-green-600 dol-truncate"><?php esc_html_e( 'Successfully connected to Dollie Cloud API', 'dollie' ); ?></p>
									<p class="dol-mt-2 dol-flex dol-items-center dol-text-sm dol-text-gray-500">
										<span class="dol-truncate"><?php esc_html_e( 'Sites can be launched via this Hub', 'dollie' ); ?></span>
									</p>
								</div>
								<div class="dol-hidden dol-mt-3 md:dol-block">
									<div>
										<p class="dol-mt-2 dol-flex dol-items-center dol-text-sm dol-text-gray-500">
											<svg class="dol-flex-shrink-0 dol-mr-1.5 dol-h-5 dol-w-5 dol-text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
												<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
											</svg>
											<?php esc_html_e( 'Completed', 'dollie' ); ?>
										</p>
									</div>
								</div>
							</div>
						</div>
						<div>
							<svg class="dol-h-5 dol-w-5 dol-text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
								<path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
							</svg>
						</div>
					</div>
				</a>
			</li>
		<?php else : ?>
			<li>
				<a href="<?php echo esc_url( dollie()->auth()->get_auth_link() ); ?>" class="dol-block hover:dol-bg-gray-50">
					<div class="dol-flex dol-items-center dol-px-4 dol-py-4 sm:dol-px-6">
						<div class="dol-min-w-0 dol-flex-1 dol-flex dol-items-center">
							<div class="dol-flex-shrink-0">
								<img class="dol-h-6 dol-w-6 dol-rounded-full dol-bg-red-400 dol-p-2" src="<?php echo DOLLIE_ASSETS_URL; ?>/img/active.png" alt="">
							</div>
							<div class="dol-min-w-0 dol-flex-1 dol-px-4 md:dol-grid md:dol-grid-cols-2 md:dol-gap-4">
								<div>
									<p class="dol-text-sm dol-font-medium dol-text-red-600 dol-truncate"><?php esc_html_e( 'Not connected to Dollie API', 'dollie' ); ?></p>
									<p class="dol-mt-2 dol-flex dol-items-center dol-text-sm dol-text-gray-500">
										<span class="dol-truncate"><?php esc_html_e( 'Please connect to the API', 'dollie' ); ?></span>
									</p>
								</div>
								<div class="dol-hidden dol-mt-3 md:dol-block">
									<div>
										<p class="dol-mt-2 dol-flex dol-items-center dol-text-sm dol-text-gray-500">
											<svg xmlns="http://www.w3.org/2000/svg" class="dol-flex-shrink-0 dol-mr-1.5 dol-h-5 dol-w-5 dol-text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
												<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
											</svg>
											<?php esc_html_e( 'Not Completed', 'dollie' ); ?>
										</p>
									</div>
								</div>
							</div>
						</div>
						<div>
							<svg class="dol-h-5 dol-w-5 dol-text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
								<path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
							</svg>
						</div>
					</div>
				</a>
			</li>
		<?php endif; ?>

		<?php if ( $charge && dollie()->has_products() ) : ?>
			<li>
				<a href="#" class="dol-block hover:dol-bg-gray-50">
					<div class="dol-flex dol-items-center dol-px-4 dol-py-4 sm:dol-px-6">
						<div class="dol-min-w-0 dol-flex-1 dol-flex dol-items-center">
							<div class="dol-flex-shrink-0">
								<svg xmlns="http://www.w3.org/2000/svg" class="dol-h-6 dol-w-6 dol-rounded-full dol-bg-green-400 dol-p-2 dol-text-green-100" fill="none" viewBox="0 0 24 24" stroke="currentColor">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
								</svg>
							</div>
							<div class="dol-min-w-0 dol-flex-1 dol-px-4 md:dol-grid md:dol-grid-cols-2 md:dol-gap-4">
								<div>
									<p class="dol-text-sm dol-font-medium dol-text-green-600 dol-truncate"><?php esc_html_e( 'WooCommerce Products Found', 'dollie' ); ?></p>
									<p class="dol-mt-2 dol-flex dol-items-center dol-text-sm dol-text-gray-500">
										<span class="dol-truncate"><?php esc_html_e( 'One or more WooCommmerce products with Dollie settings are found.', 'dollie' ); ?></span>
									</p>
								</div>
								<div class="dol-hidden dol-mt-3 md:dol-block">
									<div>
										<p class="dol-mt-2 dol-flex dol-items-center dol-text-sm dol-text-gray-500">
											<svg class="dol-flex-shrink-0 dol-mr-1.5 dol-h-5 dol-w-5 dol-text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
												<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
											</svg>
											<?php esc_html_e( 'Completed', 'dollie' ); ?>
										</p>
									</div>
								</div>
							</div>
						</div>
						<div>
							<svg class="dol-h-5 dol-w-5 dol-text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
								<path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
							</svg>
						</div>
					</div>
				</a>
			</li>
		<?php elseif ( $charge ) : ?>
			<li>
				<a href="<?php echo get_admin_url(); ?>post-new.php?post_type=product" class="dol-block hover:dol-bg-gray-50">
					<div class="dol-flex dol-items-center dol-px-4 dol-py-4 sm:dol-px-6">
						<div class="dol-min-w-0 dol-flex-1 dol-flex dol-items-center">
							<div class="dol-flex-shrink-0">
								<svg xmlns="http://www.w3.org/2000/svg" class="dol-h-6 dol-w-6 dol-rounded-full dol-bg-red-400 dol-p-2 dol-text-red-100" fill="none" viewBox="0 0 24 24" stroke="currentColor">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
								</svg>
							</div>
							<div class="dol-min-w-0 dol-flex-1 dol-px-4 md:dol-grid md:dol-grid-cols-2 md:dol-gap-4">
								<div>
									<p class="dol-text-sm dol-font-medium dol-text-red-600 dol-truncate"><?php esc_html_e( 'WooCommerce Setup', 'dollie' ); ?></p>
									<p class="dol-mt-2 dol-flex dol-items-center dol-text-sm dol-text-gray-500">
										<span class="dol-truncate"><?php esc_html_e( 'No Products with Dollie Settings Found', 'dollie' ); ?></span>
									</p>
								</div>
								<div class="dol-hidden dol-mt-3 md:dol-block">
									<div>
										<p class="dol-mt-2 dol-flex dol-items-center dol-text-sm dol-text-gray-500">
											<svg xmlns="http://www.w3.org/2000/svg" class="dol-flex-shrink-0 dol-mr-1.5 dol-h-5 dol-w-5 dol-text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
												<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
											</svg>
											<?php esc_html_e( 'Not Completed', 'dollie' ); ?>
										</p>
									</div>
								</div>
							</div>
						</div>
						<div>
							<svg class="dol-h-5 dol-w-5 dol-text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
								<path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
							</svg>
						</div>
					</div>
				</a>
			</li>
		<?php endif; ?>

		<?php if ( dollie()->workspace()->has_custom_deployment_domain() ) : ?>
			<li>
				<a href="#" class="dol-block hover:dol-bg-gray-50">
					<div class="dol-flex dol-items-center dol-px-4 dol-py-4 sm:dol-px-6">
						<div class="dol-min-w-0 dol-flex-1 dol-flex dol-items-center">
							<div class="dol-flex-shrink-0">
								<svg xmlns="http://www.w3.org/2000/svg" class="dol-h-6 dol-w-6 dol-rounded-full dol-bg-green-400 dol-p-2 dol-text-green-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
								<path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
								</svg>
							</div>
							<div class="dol-min-w-0 dol-flex-1 dol-px-4 md:dol-grid md:dol-grid-cols-2 md:dol-gap-4">
								<div>
									<p class="dol-text-sm dol-font-medium dol-text-green-600 dol-truncate"><?php esc_html_e( 'Your Custom Domain is Active', 'dollie' ); ?></p>
									<p class="dol-mt-2 dol-flex dol-items-center dol-text-sm dol-text-gray-500">
										<span class="dol-truncate"><?php printf( __( 'You are using the domain %s.', 'dollie' ), dollie()->workspace()->get_deployment_domain() ); ?></span>
									</p>
								</div>
								<div class="dol-hidden dol-mt-3 md:dol-block">
									<div>
										<p class="dol-mt-2 dol-flex dol-items-center dol-text-sm dol-text-gray-500">
											<svg class="dol-flex-shrink-0 dol-mr-1.5 dol-h-5 dol-w-5 dol-text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
												<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
											</svg>
											<?php esc_html_e( 'Completed', 'dollie' ); ?>
										</p>
									</div>
								</div>
							</div>
						</div>
						<div>
							<svg class="dol-h-5 dol-w-5 dol-text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
								<path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
							</svg>
						</div>
					</div>
				</a>
			</li>
		<?php else : ?>
			<li>
				<a href="<?php echo get_admin_url(); ?>admin.php?page=wpd_platform_setup" class="dol-block hover:dol-bg-gray-50">
					<div class="dol-flex dol-items-center dol-px-4 dol-py-4 sm:dol-px-6">
						<div class="dol-min-w-0 dol-flex-1 dol-flex dol-items-center">
							<div class="dol-flex-shrink-0">
								<svg xmlns="http://www.w3.org/2000/svg" class="dol-h-6 dol-w-6 dol-rounded-full dol-bg-red-400 dol-p-2 dol-text-red-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
									<path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
								</svg>
							</div>
							<div class="dol-min-w-0 dol-flex-1 dol-px-4 md:dol-grid md:dol-grid-cols-2 md:dol-gap-4">
								<div>
									<p class="dol-text-sm dol-font-medium dol-text-red-600 dol-truncate"><?php esc_html_e( 'Custom Domain Setup', 'dollie' ); ?></p>
									<p class="dol-mt-2 dol-flex dol-items-center dol-text-sm dol-text-gray-500">
										<span class="dol-truncate"><?php esc_html_e( 'You have not connected your own domain yet.', 'dollie' ); ?></span>
									</p>
								</div>
								<div class="dol-hidden dol-mt-3 md:dol-block">
									<div>
										<p class="dol-mt-2 dol-flex dol-items-center dol-text-sm dol-text-gray-500">
											<svg class="dol-h-5 dol-w-5 dol-text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
												<path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
											</svg>
											<?php esc_html_e( 'Not Completed', 'dollie' ); ?>
										</p>
									</div>
								</div>
							</div>
						</div>
						<div>
							<svg class="dol-h-5 dol-w-5 dol-text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
								<path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
							</svg>
						</div>
					</div>
				</a>
			</li>
		<?php endif; ?>
	</ul>
</div>
