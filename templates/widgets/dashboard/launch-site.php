<div class="dol-flex dol-flex-wrap dol-overflow-hidden dol-bg-primary dol-p-8 dol-rounded dol-widget-launch-site">
	<div class="dol-w-full md:dol-w-4/6 dol-flex dol-flex-wrap dol-items-center">
		<div class="dol-text-brand-50 dol-mb-4 md:dol-mb-0 md:dol-mr-8 dol-text-center dol-w-full md:dol-w-auto">
			<i class="fas fa-rocket fa-3x dol-text-white"></i>
		</div>
		<div class="dol-text-center md:dol-text-left">
			<div class="dol-font-semibold dol-text-2xl dol-text-white dol-uppercase">
				<?php esc_html_e($settings['title']); ?>
			</div>
			<div class="dol-text-primary-300 dol-text-lg">
				<?php esc_html_e($settings['subtitle']); ?>
			</div>
		</div>
	</div>
	<div class="dol-w-full md:dol-w-2/6 dol-flex dol-items-center dol-justify-end dol-mt-8 md:dol-mt-0">
		<a href="<?php echo get_permalink(dollie()->get_launch_page_id()); ?>" class="dol-border dol-border-solid dol-border-primary-700 dol-block dol-w-full md:dol-inline-block md:dol-w-auto dol-text-center dol-text-white dol-text-lg dol-px-8 dol-py-4 dol-bg-primary-600 dol-rounded dol-tracking-wider hover:dol-text-white hover:dol-shadow-lg">
			<?php _e('Launch', 'dollie'); ?>
		</a>
	</div>
</div>
