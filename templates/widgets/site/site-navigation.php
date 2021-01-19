<?php

use Dollie\Core\Modules\AccessControl;

$deploying = 'pending' === \Dollie\Core\Modules\Container::instance()->get_status($current_id);

?>

<div class="dol-dark dark">
	<div class="dol-bg-white dark:dol-bg-gray-800 dol-rounded dol-overflow-hidden">
		<?php if (!$deploying) : ?>
			<div class="dol-px-4 dol-py-3 dol-mb-1">
				<div class=" dol-font-bold dol-text-xl dol-uppercase">
					<?php echo get_post_meta($current_id, 'wpd_installation_name', true); ?>
				</div>
				<div class="dol-inline-block dol-text-sm dol-break-all">
					<?php echo dollie()->get_container_url($current_id); ?>
				</div>
			</div>
		<?php else : ?>
			<div class="dol-px-4 dol-py-3">
				<div class=" dol-font-bold dol-text-xl dol-uppercase">
					<?php esc_html_e('Getting ready soon', 'dollie'); ?>
				</div>
				<div class="dol-inline-block dol-text-sm">
					<?php echo get_the_title($current_id); ?>
				</div>
			</div>
		<?php endif; ?>

		<div class="dol-px-3">
			<div class="dol-rounded dol-overflow-hidden">
				<?php echo dollie()->get_site_screenshot($current_id); ?>
			</div>

			<?php if (!$deploying) : ?>
				<div class="dol-flex dol-flex-wrap dol-rounded dol-overflow-hidden dol-mt-2">
					<div class="dol-w-full md:dol-w-1/2 dol-border-0 md:dol-border-r dol-border-solid dol-border-primary-100">
						<a class="dol-block dol-text-center dol-text-sm dol-font-bold dol-bg-white hover: hover:dol-bg-primary-100 dol-px-4 dol-py-2" href="<?php echo dollie()->get_container_url($current_id); ?>" target="_blank">
							<i class="fal fa-external-link-alt dol-mr-1"></i>
							<?php esc_html_e('Visit', 'dollie'); ?>
						</a>
					</div>
					<div class="dol-w-full md:dol-w-1/2 dol-border-0 md:dol-border-l dol-border-solid dol-border-primary-100">
						<a class="dol-block dol-text-center dol-text-sm dol-font-bold dol-bg-white hover: hover:dol-bg-primary-100 dol-px-4 dol-py-2" href="<?php echo dollie()->get_customer_login_url($current_id); ?>" target="_blank">
							<i class="fal fa-tools dol-mr-1"></i>
							<?php esc_html_e('Admin', 'dollie'); ?>
						</a>
					</div>
				</div>
			<?php else : ?>
				<div class="dol-mt-2 dol-mb-4">
					<div class="dol-flex dol-items-center dol-justify-center dol-uppercase dol-font-medium dol-text-sm dol-text-flame-100 dol-bg-flame-600 dol-rounded dol-tracking-wide dol-px-3 dol-py-2 dol-cursor-default dol-leading-none">
						<svg class="dol-animate-spin dol--ml-1 dol-mr-3 dol-h-5 dol-w-5 " xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
							<circle class="dol-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
							<path class="dol-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
						</svg>
						<?php esc_html_e('Deploying', 'dollie'); ?>
					</div>
				</div>
			<?php endif; ?>
		</div>

		<?php if (!$deploying) : ?>
			<?php

			$menu = [
				''                => __('Dashboard', 'dollie'),
				'plugins'         => __('Plugins', 'dollie'),
				'themes'          => __('Themes', 'dollie'),
				'domains'         => __('Domains', 'dollie'),
				'backups'         => __('Backups', 'dollie'),
				'updates'         => __('Updates', 'dollie'),
				'developer-tools' => __('Developer Tools', 'dollie'),
				'blueprints'      => __('Blueprints', 'dollie'),
				'migrate'         => __('Migrate', 'dollie'),
				'delete'          => __('Delete', 'dollie'),
			];

			$sub_page = get_query_var('sub_page');

			?>

			<div class="dol-px-6 dol-py-3 dol-mt-2">
				<ul class="dol-list-none dol-p-0 dol-m-0">
					<?php foreach ($menu as $page => $title) : ?>
						<?php

						if ('' === $page) {
							$page = 'dashboard';
						}

						if (!dollie()->in_array_r($page, AccessControl::instance()->get_available_sections())) {
							continue;
						}

						$active_class = $sub_page === $page ? ' dol-font-bold' : 'dol-font-normal';
						?>
						<li class="dol-m-0">
							<a class="dol-block hover: dol-py-1 dol-rounded <?php echo esc_attr($active_class); ?>" href="<?php echo dollie()->get_site_url($current_id, $page); ?>">
								<i class="fal fa-angle-right dol-mr-1"></i>
								<?php echo esc_html($title); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
	</div>
</div>
