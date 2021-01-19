<?php $setup_complete = get_post_meta( $post_id, 'wpd_setup_complete', true ); ?>


<?php if ( empty( $data ) || ( $data['site_data']['Installed Themes'] === '' && $setup_complete === 'yes' ) ) : ?>
	<?php dollie()->could_not_connect_message(); ?>
<?php else : ?>
	<?php if ( $setup_complete !== 'yes' ) : ?>

		<?php if ( current_user_can( 'manage_options' ) && dollie()->count_total_containers() === 1 ) : ?>
			<div class="dol-mb-6">
				<h4 class="dol-text-primary-800 dol-mb-2 dol-font-bold dol-text-xl lg:dol-text-2xl">
					<?php esc_html_e( 'Setup Wizard - Your first site was deployed successfully!', 'dollie' ); ?>
				</h4>
				<p class="dol-p-0 dol-m-0 dol-text-ash-700">
					<?php esc_html_e( 'Finally give your site a name and give it an admin account and you\'re all set!', 'dollie' ); ?>
				</p>
			</div>
		<?php endif; ?>
		<div>
			<?php echo do_shortcode( '[dollie_form form="form_dollie_after_launch"]' ); ?>
		</div>

	<?php else : ?>

		<?php
		$active   = $data['site_data']['Active Plugins'] ?? 0;
		$security = get_transient( 'dollie_security_check_failed_' . $install );
		?>

		<div class="dol-mb-12">
			<h2 class="dol-text-gray-500 text-s dol-font-medium dol-uppercase dol-tracking-wide dol-mb-5">
				<?php esc_html_e( 'Your Installation', 'dollie' ); ?>
			</h2>
			<div class="dol-mt-2 dol-grid dol-grid-cols-1 dol-gap-5 sm:dol-grid-cols-2 lg:dol-grid-cols-3">

				<div class="dol-bg-white dol-overflow-hidden dol-shadow dol-rounded-lg">
					<div class="dol-p-5">
						<div class="dol-flex dol-items-center">
							<div class="dol-flex-shrink-0">
								<i class="fal fa-wrench dol-mr-2 dol-text-primary-300 dol-text-xl"></i>
							</div>
							<div class="dol-ml-5 dol-w-0 dol-flex-1">
								<dl>
									<dt class="dol-text-sm dol-leading-5 dol-font-medium dol-text-cool-gray-500 dol-truncate">
										<?php esc_html_e( 'Plugins Active', 'dollie' ); ?>
									</dt>
									<dd class="dol-m-0">
										<div class="dol-text-lg dol-leading-7 dol-font-medium dol-text-cool-gray-900">
											<?php echo count( $active ); ?>
										</div>
									</dd>
								</dl>
							</div>
						</div>
					</div>
					<div class="dol-bg-cool-gray-50 dol-px-5 dol-py-3">
						<div class="dol-text-sm dol-leading-5">
							<a href="<?php echo dollie()->get_site_url( $post_id, 'updates' ); ?>"
							   class="dol-font-medium dol-text-teal-600 hover:dol-text-teal-900 dol-transition dol-ease-in-out dol-duration-150">
								<?php
								$plugin_updates = get_post_meta( $post_id, 'wpd_installation_plugin_updates', true );
								printf( _n( '%s Updates Available', '%s Updates Available', $plugin_updates, 'dollie' ), $plugin_updates );
								?>
							</a>
						</div>
					</div>
				</div>

				<div class="dol-bg-white dol-overflow-hidden dol-shadow dol-rounded-lg">
					<div class="dol-p-5">
						<div class="dol-flex dol-items-center">
							<div class="dol-flex-shrink-0">
								<i class="fal fa-themes dol-text-primary-300 dol-text-xl"></i>
							</div>
							<div class="dol-ml-5 dol-w-0 dol-flex-1">
								<dl>
									<dt class="dol-text-sm dol-leading-5 dol-font-medium dol-text-cool-gray-500 dol-truncate">
										<?php esc_html_e( 'Active Theme', 'dollie' ); ?>
									</dt>
									<dd class="dol-m-0">
										<div class="dol-text-lg dol-leading-7 dol-font-medium dol-text-cool-gray-900">
											<?php echo esc_html( $data['site_data']['Theme Name'] ); ?>
										</div>
									</dd>
								</dl>
							</div>
						</div>
					</div>
					<div class="dol-bg-cool-gray-50 dol-px-5 dol-py-3">
						<div class="dol-text-sm dol-leading-5">
							<a href="<?php echo dollie()->get_site_url( $post_id, 'updates' ); ?>"
							   class="dol-font-medium dol-text-teal-600 hover:dol-text-teal-900 dol-transition dol-ease-in-out dol-duration-150">
								<?php esc_html_e( 'Check for Updates', 'dollie' ); ?>
							</a>
						</div>
					</div>
				</div>

				<div class="dol-bg-white dol-overflow-hidden dol-shadow dol-rounded-lg">
					<div class="dol-p-5">
						<div class="dol-flex dol-items-center">
							<div class="dol-flex-shrink-0">
								<i class="fab fa-wordpress-simple dol-text-primary-300 dol-text-xl"></i>
							</div>
							<div class="dol-ml-5 dol-w-0 dol-flex-1">
								<dl>
									<dt class="dol-text-sm dol-leading-5 dol-font-medium dol-text-cool-gray-500 dol-truncate">
										<?php esc_html_e( 'WordPress Version', 'dollie' ); ?>
									</dt>
									<dd class="dol-m-0">
										<div class="dol-text-lg dol-leading-7 dol-font-medium dol-text-cool-gray-900">
											5.5.3
										</div>
									</dd>
								</dl>
							</div>
						</div>
					</div>
					<div class="dol-bg-cool-gray-50 dol-px-5 dol-py-3">
						<div class="dol-text-sm dol-leading-5">
							<a href="<?php echo dollie()->get_site_url( $post_id, 'updates' ); ?>"
							   class="dol-font-medium dol-text-teal-600 hover:dol-text-teal-900 dol-transition dol-ease-in-out dol-duration-150">
								<?php esc_html_e( 'Check Core Updates', 'dollie' ); ?>
							</a>
						</div>
					</div>
				</div>
			</div>
			<div>
				<div class="dol-mt-5 dol-grid dol-grid-cols-1 dol-gap-5 sm:dol-grid-cols-1 lg:dol-grid-cols-1">
					<div class="dol-bg-white dol-overflow-hidden dol-shadow dol-rounded-lg">
						<div class="dol-px-4 dol-py-5 sm:dol-p-6">
							<div class="dol-flex dol-items-center">
								<div class="dol-flex-shrink-0 dol-bg-cool-gray-50 dol-rounded-md dol-p-3">
									<i class="fal fa-hdd dol-text-primary-300 dol-text-xl"></i>
								</div>
								<div class="dol-ml-5 dol-w-0 dol-flex-1">
									<dl>
										<dt class="dol-text-sm dol-leading-5 dol-font-medium dol-text-gray-500 dol-truncate">
											<?php esc_html_e( 'Backup Status', 'dollie' ); ?>
										</dt>
										<dd class="dol-m-0 dol-flex dol-items-baseline">
											<div class="dol-text-2xl dol-leading-8 dol-font-semibold dol-text-gray-900">
												<?php
												$available_backups = dollie()->get_site_total_backups();
												?>
												<?php if ( $available_backups ) : ?>
													<?php printf( __( '%s Backups Available', 'dollie' ), $available_backups ); ?>
												<?php else : ?>
													<span><?php esc_html_e( 'No backups yet.', 'dollie' ); ?></span>
												<?php endif; ?>
											</div>
											<div class="dol-ml-2 dol-flex dol-items-baseline dol-text-sm dol-leading-5 dol-font-semibold dol-text-green-600">
												<svg class="dol-self-center dol-flex-shrink-0 dol-h-5 dol-w-5"
													 fill="currentColor" viewBox="0 0 20 20">
													<path fill-rule="evenodd"
														  d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z"
														  clip-rule="evenodd"/>
												</svg>
												<?php esc_html_e( 'Runs every 24 hours', 'dollie' ); ?>
											</div>
										</dd>
									</dl>
								</div>
							</div>
						</div>
						<div class="dol-bg-cool-gray-50 dol-px-5 dol-py-3">
							<div class="dol-text-sm dol-leading-5">
								<a href="<?php echo dollie()->get_site_url( $post_id, 'backups' ); ?>"
								   class="dol-font-medium dol-text-teal-600 hover:dol-text-teal-900 dol-transition dol-ease-in-out dol-duration-150">
									<?php esc_html_e( 'View Backups', 'dollie' ); ?>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="dol-mb-10 dol-p-0 dol-m-0">
			<h2 class="dol-text-gray-500 dol-font-medium dol-uppercase dol-tracking-wide dol-mb-5">
				<?php esc_html_e( 'Details', 'dollie' ); ?>
			</h2>
			<ul class="dol-grid dol-grid-cols-1 dol-gap-4 sm:dol-gap-6 sm:dol-grid-cols-2 dol-mt-3 dol-p-0 dol-m-0">
				<li class="dol-relative dol-col-span-1 dol-flex dol-shadow-sm dol-rounded-md">
					<div class="dol-flex-shrink-0 dol-flex dol-items-center dol-justify-center dol-w-16 dol-bg-red-500 dol-text-white dol-text-sm dol-leading-5 dol-font-medium dol-rounded-l-md">
						<i class="fal fa-comments dol-text-white dol-text-xl"></i>
					</div>
					<div class="dol-flex-1 dol-flex dol-items-center dol-justify-between dol-border-t dol-border-r dol-border-b dol-border-gray-200 dol-bg-white dol-rounded-r-md dol-truncate">
						<div class="dol-flex-1 dol-px-4 dol-py-2 dol-text-sm dol-leading-5 dol-truncate">
							<a href="#"
							   class="dol-text-gray-900 dol-font-medium hover:dol-text-gray-600 dol-transition dol-ease-in-out dol-duration-150">
								<?php printf( __( '%s Comments Made', 'dollie' ), get_post_meta( $post_id, 'wpd_installation_comments_total', true ) ); ?>
							</a>
							<p class="dol-text-gray-500">
								<?php printf( __( '%s marked as spam', 'dollie' ), get_post_meta( $post_id, 'wpd_installation_comments_spam', true ) ); ?>
							</p>
						</div>
					</div>
				</li>

				<li class="dol-relative dol-col-span-1 dol-flex dol-shadow-sm dol-rounded-md">
					<div class="dol-flex-shrink-0 dol-flex dol-items-center dol-justify-center dol-w-16 dol-bg-secondary dol-text-white dol-text-sm dol-leading-5 dol-font-medium dol-rounded-l-md">
						<i class="fal fa-at dol-text-white dol-text-xl"></i>
					</div>
					<div class="dol-flex-1 dol-flex dol-items-center dol-justify-between dol-border-t dol-border-r dol-border-b dol-border-gray-200 dol-bg-white dol-rounded-r-md dol-truncate">
						<div class="dol-flex-1 dol-px-4 dol-py-2 dol-text-sm dol-leading-5 dol-truncate">
							<a href="#"
							   class="dol-text-gray-900 dol-font-medium hover:dol-text-gray-600 dol-transition dol-ease-in-out dol-duration-150">
								<?php echo esc_html( $data['container_details']['Admin Email'] ); ?>
							</a>
							<p class="dol-text-gray-500"><?php esc_html_e( 'is the site administrator email', 'dollie' ); ?></p>
						</div>
					</div>
				</li>

				<li class="dol-relative dol-col-span-1 dol-flex dol-shadow-sm dol-rounded-md">
					<div class="dol-flex-shrink-0 dol-flex dol-items-center dol-justify-center dol-w-16 dol-bg-green-500 dol-text-white dol-text-sm dol-leading-5 dol-font-medium dol-rounded-l-md">
						<i class="fal fa-hdd dol-text-white dol-text-xl"></i>
					</div>
					<div class="dol-flex-1 dol-flex dol-items-center dol-justify-between dol-border-t dol-border-r dol-border-b dol-border-gray-200 dol-bg-white dol-rounded-r-md dol-truncate">
						<div class="dol-flex-1 dol-px-4 dol-py-2 dol-text-sm dol-leading-5 dol-truncate">
							<a href="#"
							   class="dol-text-gray-900 dol-font-medium hover:dol-text-gray-600 dol-transition dol-ease-in-out dol-duration-150">
								<?php echo get_post_meta( $post_id, 'wpd_installation_size', true ); ?>
							</a>
							<p class="dol-text-gray-500"><?php esc_html_e( 'of Disk space is used', 'dollie' ); ?></p>
						</div>
					</div>
				</li>

				<li class="dol-relative dol-col-span-1 dol-flex dol-shadow-sm dol-rounded-md">
					<div class="dol-flex-shrink-0 dol-flex dol-items-center dol-justify-center dol-w-16 dol-bg-primary dol-text-white dol-text-sm dol-leading-5 dol-font-medium dol-rounded-l-md">
						<i class="fal fa-users dol-text-white dol-text-xl"></i>
					</div>
					<div class="dol-flex-1 dol-flex dol-items-center dol-justify-between dol-border-t dol-border-r dol-border-b dol-border-gray-200 dol-bg-white dol-rounded-r-md dol-truncate">
						<div class="dol-flex-1 dol-px-4 dol-py-2 dol-text-sm dol-leading-5 dol-truncate">
							<a href="#"
							   class="dol-text-gray-900 dol-font-medium hover:dol-text-gray-600 dol-transition dol-ease-in-out dol-duration-150">
								<?php printf( __( '%s Users', 'dollie' ), get_post_meta( $post_id, 'wpd_installation_users', true ) ); ?>
							</a>
							<p class="dol-text-gray-500"><?php esc_html_e( 'Registered on this site', 'dollie' ); ?></p>
						</div>
					</div>
				</li>

			</ul>
		</div>


		<?php

		\Dollie\Core\Utils\Tpl::load( 'loop/posts',
			[
				'title' => __( 'From your blog', 'dollie' ),
				'posts' => dollie()->get_latest_container_posts(),
			],
			true
		);

		?>

	<?php endif; ?>
<?php endif; ?>
