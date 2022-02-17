<?php //dollie_setup_get_template_part('wrapper-header');
?>
<div class="dol-min-h-full dol-mt-8 dol-pt-[100px]">
	<main class="dol-pb-8 dol--mt-24">
		<div class="dol-max-w-3xl dol-px-4 dol-mx-auto sm:dol-px-6 lg:dol-max-w-7xl lg:dol-px-8">
			<h1 class="dol-sr-only">Profile</h1>
			<!-- Main 3 column dol-grid -->
			<div class="dol-grid dol-items-start dol-grid-cols-1 dol-gap-4 lg:dol-grid-cols-3 lg:dol-gap-8">
				<!-- Left column -->
				<div class="dol-grid dol-grid-cols-1 dol-gap-4 lg:dol-col-span-2">
					<!-- Welcome panel -->
					<section aria-labelledby="profile-overview-title">
						<div class="dol-overflow-hidden dol-bg-white dol-rounded-lg dol-shadow">
							<h2 class="dol-sr-only" id="profile-overview-title">Profile Overview</h2>
							<div class="dol-p-6 dol-bg-white">
								<div class="sm:dol-flex sm:dol-items-center sm:dol-justify-between">
									<div class="sm:dol-flex sm:dol-space-x-5">
										<div class="dol-flex-shrink-0">
											<img class="dol-w-20 dol-h-20 dol-mx-auto dol-rounded-full" src="<?php echo esc_url(DOLLIE_ASSETS_URL . 'wizard/agency.svg'); ?>" alt="">
										</div>
										<div class="dol-mt-4 dol-text-center sm:dol-mt-0 sm:dol-pt-1 sm:dol-text-left">
											<p class="dol-text-sm dol-font-medium dol-text-gray-600">Welcome back to</p>
											<p class="dol-text-xl dol-font-bold dol-text-gray-900 sm:dol-text-2xl">Your Agency Dashboard</p>
										</div>
									</div>
									<div class="dol-flex dol-justify-center dol-mt-5 sm:dol-mt-0">
										<a href="#" class="dol-flex dol-items-center dol-justify-center dol-px-4 dol-py-2 dol-text-sm dol-font-medium dol-text-gray-700 dol-bg-white dol-border dol-border-gray-300 dol-rounded-md dol-shadow-sm hover:dol-bg-gray-50"> View profile </a>
									</div>
								</div>
							</div>
							<div class="dol-grid dol-grid-cols-1 dol-border-t dol-border-gray-200 dol-divide-y dol-divide-gray-200 dol-bg-gray-50 sm:dol-grid-cols-3 sm:dol-divide-y-0 sm:dol-divide-x">
								<div class="dol-px-6 dol-py-5 dol-text-sm dol-font-medium dol-text-center">
									<span class="dol-text-gray-900"><?php echo dollie()->count_total_containers(); ?></span>
									<span class="dol-text-gray-600">Customer Sites</span>
								</div>

								<div class="dol-px-6 dol-py-5 dol-text-sm dol-font-medium dol-text-center">
									<span class="dol-text-gray-900"><?php echo dollie()->count_total_blueprints(); ?></span>
									<span class="dol-text-gray-600">Site Blueprints</span>
								</div>

								<div class="dol-px-6 dol-py-5 dol-text-sm dol-font-medium dol-text-center">
									<span class="dol-text-gray-900"><?php echo dollie()->count_customer_staging_sites(); ?></span>
									<span class="dol-text-gray-600">Staging Sites</span>
								</div>
							</div>
						</div>
					</section>

					<h2 class="dol-text-gray-500 text-s dol-font-small dol-uppercase dol-tracking-wide dol-mb-1 dol-text-xl">
						Recent Launched Sites
					</h2>

					<section aria-labelledby="profile-overview-title">
						<?php
						$args = [
							'posts_per_page' => 3,
							'post_type'      => 'container',
							'post_status'    => 'publish',
						];

						$sites = new WP_Query($args);

						$data = [
							'sites'      => $sites,
							'query_data' => [
								'permalink'    => get_the_permalink(),
							],
						];
						dollie()->load_template('loop/sites-dashboard', $data, true);
						?>
					</section>

					<h2 class="dol-text-gray-500 text-s dol-font-small dol-uppercase dol-tracking-wide dol-mb-1 dol-text-xl">
						Dollie Updates
					</h2>

					<section>
						<?php
						$data = [
							'posts'    => dollie()->get_dashboard_posts()
						];
						dollie()->load_template('widgets/dashboard/dollie-news', $data, true);
						?>
					</section>


				</div>

				<!-- Right column -->
				<div class="dol-grid dol-grid-cols-1 dol-gap-4">


					<!-- Recent Hires -->
					<section aria-labelledby="recent-hires-title">
						<div class="dol-overflow-hidden dol-bg-white dol-rounded-lg dol-shadow">
							<div class="dol-p-6">
								<h2 class="dol-text-base dol-font-medium dol-text-gray-900" id="recent-hires-title">Recent Clients</h2>
								<div class="dol-flow-root dol-mt-6">
									<ul role="list" class="dol--my-5 dol-divide-y dol-divide-gray-200">

										<?php
										$args = [
											['role__in' => ['author', 'subscriber', 'customer']],
											'number' => 5, // How many per page
										];
										$customers = new WP_User_Query($args);
										if (!empty($customers->results)) {
											foreach ($customers->results as $customer) {
										?>
												<li class="dol-py-4">
													<div class="dol-flex dol-items-center dol-space-x-4">
														<div class="dol-flex-shrink-0">
															<?php echo get_avatar($customer->ID, '100', '', '', ['class' => 'dol-w-8 dol-h-8 dol-rounded-full']); ?>
														</div>
														<div class="dol-flex-1 dol-min-w-0">
															<p class="dol-text-sm dol-font-medium dol-text-gray-900 dol-truncate"><?php echo $customer->display_name; ?></p>
															<p class="dol-text-sm dol-text-gray-500 dol-truncate"><?php echo dollie()->count_customer_containers($customer->ID); ?> Sites</p>
														</div>
														<div>
															<a href="<?php echo get_edit_user_link($customer->ID); ?>" class="dol-inline-flex dol-items-center dol-shadow-sm dol-px-2.5 dol-py-0.5 dol-border dol-border-gray-300 dol-text-sm dol-leading-5 dol-font-medium dol-rounded-full dol-text-gray-700 dol-bg-white hover:dol-bg-gray-50"> Edit Client </a>
														</div>
													</div>
												</li>

										<?php }
										} ?>
									</ul>
								</div>
								<div class="dol-mt-6">
									<a href="<?php echo get_admin_url(); ?>users.php" class="dol-flex dol-items-center dol-justify-center dol-w-full dol-px-4 dol-py-2 dol-text-sm dol-font-medium dol-text-gray-700 dol-bg-white dol-border dol-border-gray-300 dol-rounded-md dol-shadow-sm hover:dol-bg-gray-50"> View all </a>
								</div>
							</div>
						</div>
					</section>
					<!-- Announcements -->
					<section aria-labelledby="announcements-title">
						<div class="dol-overflow-hidden dol-bg-white dol-rounded-lg dol-shadow">
							<div class="dol-p-6">
								<h2 class="dol-text-base dol-font-medium dol-text-gray-900" id="announcements-title">Core Dollie Plugins</h2>
								<div class="dol-flow-root dol-mt-6">

									<!-- This example requires Tailwind CSS v2.0+ -->
									<div>
										<div class="dol-flow-root dol-mt-6">
											<ul role="list" class="dol--my-5 dol-divide-y dol-divide-gray-200">

												<?php
												$dollie_setup_plugins = Dollie_Setup_Plugins::get_plugins();
												foreach (Dollie_Setup_Admin_Plugins::get_settings() as $plugin => $settings_url) {
												?>
													<li class="dol-py-4">
														<div class="dol-flex dol-items-center dol-space-x-4">
															<div class="dol-flex-shrink-0">
																<img class="dol-h-5 dol-w-5 dol-opacity-100 dol-rounded-full" src="<?php echo esc_url(DOLLIE_ASSETS_URL . 'wizard/plugin.svg'); ?>" alt="">
															</div>
															<div class="dol-flex-1 dol-min-w-0">
																<p class="dol-text-sm dol-font-medium dol-text-gray-900"><?php echo $plugin . ' <span class="dol-text-gray-400">' . $dollie_setup_plugins[$plugin]['version'] . '</span>'; ?></p>
																<p class="dol-text-sm dol-text-gray-500"><?php echo $dollie_setup_plugins[$plugin]['dollie_setup_description']; ?></p>
															</div>
															<div>
																<a href="<?php echo $settings_url; ?>" class="dol-inline-flex dol-items-center dol-shadow-sm dol-px-2.5 dol-py-0.5 dol-border dol-border-gray-300 dol-text-sm dol-leading-5 dol-font-medium dol-rounded-full dol-text-gray-700 dol-bg-white hover:dol-bg-gray-50"> View </a>
															</div>
														</div>
													</li>
												<?php } ?>
											</ul>
										</div>
										<div class="dol-mt-6">
											<a href="#" class="dol-w-full dol-flex dol-justify-center dol-items-center dol-px-4 dol-py-2 dol-border dol-border-gray-300 dol-shadow-sm dol-text-sm dol-font-medium dol-rounded-md dol-text-gray-700 dol-bg-white hover:dol-bg-gray-50"> <?php printf(__('<a href="%s">Manage all your Dollie plugins here</a>', 'dollie-setup'), esc_url(self_admin_url('admin.php?page=dollie_setup-plugins'))); ?> </a>
										</div>
									</div>

								</div>
							</div>
						</div>
					</section>

				</div>
			</div>
		</div>
	</main>

</div>
<?php // dollie_setup_get_template_part('wrapper-footer');
?>
