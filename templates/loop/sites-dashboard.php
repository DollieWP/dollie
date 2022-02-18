<?php

if (!isset($view_type)) {
	$view_type = 'list';
}


// dollie()->check_bulk_actions();

// $bulk_actions          = dollie()->get_bulk_actions();
// $allowed_bulk_commands = dollie()->get_allowed_commands_in_progress();

//dollie()->load_template( 'loop/parts/modal-actions', [], true );
//dollie()->load_template( 'loop/parts/modal-filters', [], true );

?>



<div class="dol-sites dol-relative">
	<div class="dol-loader" data-for="pagination">
		<div class="dol-flex dol-items-center dol-justify-center dol-h-full">
			<svg class="dol-animate-spin dol-h-10 dol-w-10 dol-text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
				<circle class="dol-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
				<path class="dol-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
			</svg>
		</div>
	</div>
	<?php if ($sites->have_posts()) : ?>
		<?php if (isset($_GET['blueprints']) && $_GET['blueprints']) : ?>
			<div class="dol-bg-primary dol-p-3 dol-text-white dol-rounded dol-shadow dol-w-full dol-text-sm dol-mb-3">
				<?php esc_html_e('You are now viewing the blueprints made by you and your team.', 'dollie'); ?>
			</div>
		<?php endif; ?>

		<div class="dol-sites-container list">
			<?php while ($sites->have_posts()) : ?>
				<?php

				$sites->the_post();

				$list_item_class        = [];
				$btn_controls_classes   = [];

				$domain           = dollie()->get_wp_site_data('uri', get_the_ID());

				$data = [
					'slug'       => get_post_field('post_name', get_the_ID()),
					'domain'     => get_post_meta(get_the_ID(), 'wpd_domains', true) ?: dollie()->get_container_url(get_the_ID()),
					'name'       => get_post_meta(get_the_ID(), 'wpd_installation_name', true) ?: __('Unnamed', 'dollie'),
					'wp_version' => get_post_meta(get_the_ID(), 'wpd_installation_version', true),
					'is_running' => dollie()->get_container_status(get_the_ID()) === 'start',
				];


				if (dollie()->is_blueprint(get_the_ID())) {
					$list_item_class[] = 'dol-blueprint-site';
					if (get_field('wpd_installation_blueprint_title', get_the_ID())) {
						$data['name'] = get_field('wpd_installation_blueprint_title', get_the_ID());
					}
				}

				$btn_controls_classes[] = 'dol-hidden';
				$list_item_class[]      = 'dol-sites-item-locked';

				$list_item_class[] = 'dol-sites-' . $view_type . '-item';

				$btn_controls_classes   = implode(' ', $btn_controls_classes);
				$list_item_class        = implode(' ', $list_item_class);

				?>
				<div class="dol-sites-item <?php echo esc_attr($list_item_class); ?>" data-site-name="<?php echo esc_attr($domain); ?>">
					<div class="dol-sites-item-inner dol-relative dol-divide-y dol-divide-gray-200 dol-shadow dol-rounded-md dol-widget-custom">

						<div class="dol-sites-image dol-relative">
							<div class="dol-sites-image-box">
								<?php echo dollie()->get_site_screenshot(get_the_ID()); ?>
							</div>

							<div class="dol-sites-status">
								<?php if ($data['is_running']) : ?>
									<span class="dol-flex dol-h-4 dol-w-4 dol-relative">
										<span class="dol-animate-ping dol-absolute dol-inline-flex dol-h-full dol-w-full dol-rounded-full dol-bg-green-500 dol-opacity-75"></span>
										<span class="dol-relative dol-inline-flex dol-rounded-full dol-h-4 dol-w-4 dol-bg-green-600"></span>
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
									<a class="dol-item-name dol-text-normal dol-leading-normal dol-truncate dol-text-gray-600" href="<?php echo dollie()->get_site_url(get_the_ID()); ?>" title="<?php echo esc_attr($data['name']); ?>">
										<?php echo esc_html($data['name']); ?>
									</a>
								</div>

								<div class="dol-flex dol-items-center dol-truncate">
									<a class="dol-item-url dol-text-brand-500 hover:dol-text-brand-600 dol-text-sm dol-leading-normal dol-truncate" href="<?php echo esc_url($data['domain']); ?>" title="<?php echo esc_html($data['domain']); ?>" target="_blank">
										<?php echo esc_html($data['domain']); ?>
									</a>
								</div>
							</div>
						</div>
						<div class="dol-sites-version dol-cursor-default dol-text-sm">
							<div class="dol-font-semibold dol-text-gray-500">
								<?php esc_html_e('WordPress', 'dollie'); ?>
							</div>
							<div class="dol-font-bold ">
								<?php printf(__('Version %s', 'dollie'), $data['wp_version']); ?>
							</div>
						</div>
						<?php if (dollie()->is_blueprint(get_the_ID())) : ?>
							<div class="dol-sites-client dol-cursor-default dol-text-sm">
								<div class="dol-font-semibold dol-text-gray-500">
									<?php esc_html_e('Blueprint Updated', 'dollie'); ?>
								</div>
								<div class="dol-font-bold">
									<?php if (get_post_meta(get_the_ID(), 'wpd_blueprint_time', true)) : ?>
										<?php echo get_post_meta(get_the_ID(), 'wpd_blueprint_time', true); ?>
									<?php else : ?>
										<a class="dol-link" href="<?php echo get_the_permalink(get_the_ID()); ?>blueprints">
											<?php esc_html_e('Never. Update now!', 'dollie'); ?>
										</a>
									<?php endif; ?>
								</div>
							</div>
						<?php else : ?>
							<div class="dol-sites-client dol-cursor-default dol-text-sm">
								<div class="dol-font-semibold dol-text-gray-500">
									<?php echo dollie()->get_user_type_plural_string(); ?>
								</div>
								<div class="dol-font-bold ">
									<?php echo get_the_author(); ?>
								</div>
							</div>
						<?php endif; ?>
						<div class="dol-sites-controls">
							<?php if (dollie()->is_blueprint(get_the_ID())) : ?>
								<a class="dol-inline-block dol-text-sm dol-text-white dol-bg-primary dol-rounded dol-px-3 dol-py-2 hover:dol-text-white hover:dol-bg-primary-600" href="<?php echo get_the_permalink(get_the_ID()); ?>blueprints" data-tooltip="<?php echo esc_attr__('Update Blueprint', 'dollie'); ?>">
									<i class="fas fa-sync"></i>
								</a>
							<?php else : ?>
								<a class="dol-inline-block dol-text-sm dol-text-white dol-bg-primary dol-rounded dol-px-3 dol-py-2 hover:dol-text-white hover:dol-bg-primary-600" href="<?php echo dollie()->get_site_url(get_the_ID()); ?>" data-tooltip="<?php echo esc_attr__('Manage', 'dollie'); ?>">
									<i data-tooltip="Carefully crafted site designs made by our team which you can use as a starting point for your new site." class="fas fa-cog svg-tooltip acf__tooltip"></i>
								</a>
							<?php endif; ?>

							<?php
							$staging_url = get_post_meta(get_the_ID(), '_wpd_staging_url', true);
							if ($staging_url) :
							?>
								<a class="dol-inline-block dol-text-sm dol-text-white dol-bg-primary dol-rounded dol-px-3 dol-py-2 hover:dol-text-white hover:dol-bg-primary-600" href="<?php echo get_the_permalink(get_the_ID()); ?>staging" data-tooltip="<?php echo esc_attr__('Visit Staging Area', 'dollie'); ?>">
									<i class="fas fa-copy"></i>
								</a>
							<?php endif; ?>


							<?php
							$login_link = dollie()->get_customer_login_url(get_the_ID());
							if (!empty($login_link)) :
							?>
								<a class="dol-inline-block dol-text-sm dol-text-gray-500 dol-bg-gray-200 dol-rounded dol-px-3 dol-py-2 hover:dol-text-white hover:dol-bg-secondary" href="<?php echo esc_url($login_link); ?>" data-tooltip="<?php echo esc_attr__('Login to Site as Admin', 'dollie'); ?>">
									<i class="fas fa-sign-in-alt"></i>
								</a>
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
		<div class="dol-flex dol-items-center dol-justify-center dol-h-40">
			<div class="dol-text-2xl dol-text-ash-600">
				<?php esc_html_e('No sites found.', 'dollie'); ?>
			</div>
		</div>
	<?php endif; ?>
</div>
