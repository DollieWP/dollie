<?php
if (!isset($view_type)) {
	$view_type = 'list';
}

$list_type      = 'dol-sites-' . $view_type;
$list_item_type = 'dol-sites-' . $view_type . '-item';

$list_btn_active = $view_type === 'list' ? 'dol-switch-active' : '';
$grid_btn_active = $view_type === 'grid' ? 'dol-switch-active' : '';
?>
<div class="dol-mb-6">
	<div class="dol-flex dol-flex-wrap md:dol-justify-between">
		<div class="dol-flex dol-items-center dol-rounded dol-overflow-hidden dol-mb-4 md:dol-mb-0 dol-h-10 md:dol-h-auto">
			<span class="dol-list-switch dol-inline-flex <?php echo esc_attr($list_btn_active); ?>" data-list-type="list">
				<i class="fal fa-list"></i>
			</span>
			<span class="dol-list-switch dol-inline-flex <?php echo esc_attr($grid_btn_active); ?>" data-list-type="grid">
				<i class="fal fa-th"></i>
			</span>
		</div>
		<div class="dol-relative dol-w-full md:dol-w-auto">
			<div class="dol-absolute dol-left-0 dol-top-0 dol-ml-4 dol-flex dol-items-center dol-h-full">
				<i class="fal fa-search dol-text-ash-500"></i>
			</div>
			<input type="text" name="site_search" class="dol-search-input dol-search-site dol-w-full md:dol-w-64" data-list-type="<?php echo esc_attr($view_type); ?>" data-permalink="<?php echo esc_attr($query_data['permalink']); ?>" data-search-term="" placeholder="<?php esc_html_e('Search for a site...', 'dollie'); ?>">
		</div>
	</div>
</div>

<div class="dol-sites dol-relative">
	<div class="dol-loader">
		<div class="dol-flex dol-items-center dol-justify-center dol-h-full">
			<svg class="dol-animate-spin dol-h-10 dol-w-10 dol-text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
				<circle class="dol-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
				<path class="dol-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
			</svg>
		</div>
	</div>
	<?php if ($sites->have_posts()) : ?>
		<div class="dol-sites-container <?php echo esc_attr($list_type); ?>">
			<?php while ($sites->have_posts()) : ?>
				<?php

				$sites->the_post();

				$data = [
					'slug'       => get_post_field('post_name', get_the_ID()),
					'domain'     => get_post_meta(get_the_ID(), 'wpd_domains', true) ?: dollie()->get_container_url(get_the_ID()),
					'name'       => get_post_meta(get_the_ID(), 'wpd_installation_name', true) ?: __('Unnamed', 'dollie'),
					'wp_version' => get_post_meta(get_the_ID(), 'wpd_installation_version', true),
					'is_running' => dollie()->get_container_status(get_the_ID()) === 'start',
				];

				?>
				<div class="dol-sites-item <?php echo esc_attr($list_item_type); ?>">
					<div class="dol-sites-item-inner dol-rounded-lg dol-shadow dol-divide-y dol-divide-gray-200">
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
								<div class="dol-font-bold dol-text-lg dol-cursor-default">
									<a class="dol-text-normal dol-leading-normal dol-truncate dol-text-gray-600" href="<?php echo dollie()->get_site_url(get_the_ID()); ?>" target="_blank">
										<?php echo esc_html($data['name']); ?>
									</a>
								</div>

								<div class="dol-flex dol-items-center">
									<a class="dol-text-brand-500 hover:dol-text-brand-600 dol-text-sm dol-leading-normal dol-truncate" href="<?php echo esc_url($data['domain']); ?>" target="_blank">
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
						<div class="dol-sites-client dol-cursor-default dol-text-sm">
							<div class="dol-font-semibold dol-text-gray-500">
								<?php esc_html_e('Client', 'dollie'); ?>
							</div>
							<div class="dol-font-bold ">
								<?php echo get_the_author(); ?>
							</div>
						</div>
						<div class="dol-sites-controls">
							<a class="dol-inline-block dol-text-sm dol-text-white dol-font-semibold dol-bg-primary dol-rounded dol-px-3 dol-py-2 hover:dol-text-white hover:dol-bg-primary-600" href="<?php echo dollie()->get_site_url(get_the_ID()); ?>">
								<i class="fal fa-cog"></i>
								<span class="dol-ml-1"><?php esc_html_e('Manage', 'dollie'); ?></span>
							</a>


							<?php
							$login_link = dollie()->get_customer_login_url(get_the_ID());
							if (!empty($login_link)) :
							?>
								<a class="dol-inline-block dol-text-sm dol-text-gray-500 dol-font-semibold dol-bg-gray-200 dol-rounded dol-px-3 dol-py-2 hover:dol-text-white hover:dol-bg-secondary" href="<?php echo $login_link; ?>">
									<i class="fal fa-wrench"></i>
									<span class="dol-ml-1"><?php esc_html_e('Admin', 'dollie'); ?></span>
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

		<div class="dol-sites-pages" data-permalink="<?php echo esc_url($query_data['permalink']); ?>" data-current-page="<?php echo esc_attr($query_data['current_page']); ?>" data-list-type="<?php echo esc_attr($view_type); ?>">
			<?php
			echo paginate_links(
				[
					'total'        => $sites->max_num_pages,
					'show_all'     => false,
					'type'         => 'plain',
					'end_size'     => 2,
					'mid_size'     => 1,
					'prev_next'    => false,
					'add_args'     => false,
					'add_fragment' => '',
				]
			);

			?>
		</div>
	<?php else : ?>
		<div class="dol-flex dol-items-center dol-justify-center dol-h-40">
			<div class="dol-text-2xl dol-text-ash-600 dol-font-bold">
				<?php esc_html_e('No sites found.', 'dollie'); ?>
			</div>
		</div>
	<?php endif; ?>
</div>
