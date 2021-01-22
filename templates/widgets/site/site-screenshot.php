<?php

use Dollie\Core\Modules\AccessControl;

$deploying = 'pending' === \Dollie\Core\Modules\Container::instance()->get_status($current_id);

?>
<div class="">
	<div class="<?php do_action('dol_add_widget_classes'); ?> dol-overflow-hidden dol-p-4 dol-widget-site-sidebar">

		<div class="dol-px-3">
			<div class="dol-rounded dol-overflow-hidden">
				<?php echo dollie()->get_site_screenshot($current_id); ?>
			</div>

			<?php if (!$deploying) : ?>
				<div class="dol-flex dol-flex-wrap dol-rounded dol-overflow-hidden dol-mt-2">
					<div class="dol-w-full md:dol-w-1/2">
						<a class="dol-block dol-text-gray-600 dol-text-center dol-text-sm dol-font-bold dol-bg-gray-200 hover:dol-bg-primary-100 dark:dol-bg-gray-900 dark:dol-text-gray-200 dol-px-4 dol-py-2" href="<?php echo dollie()->get_container_url($current_id); ?>" target="_blank">
							<i class="fal fa-external-link-alt dol-mr-1"></i>
							<?php esc_html_e('Visit', 'dollie'); ?>
						</a>
					</div>
					<div class="dol-w-full md:dol-w-1/2">
						<a class="dol-block dol-text-center dol-text-sm dol-font-bold dol-bg-secondary-200 hover: hover:dol-bg-primary-300 dol-text-secondary-600 dol-px-4 dol-py-2 dark:dol-bg-secondary-700 dark:dol-text-gray-200" href="<?php echo dollie()->get_customer_login_url($current_id); ?>" target="_blank">
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
						<?php esc_html_e('Launching Site', 'dollie'); ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
