<?php

$container = dollie()->get_container();

if ( is_wp_error( $container ) ) {
	return;
}

?>
<div class="">
	<div class="<?php do_action( 'dol_add_widget_classes' ); ?> dol-overflow-hidden dol-p-4 dol-widget-site-sidebar">
		<?php if ( ! $container->is_deploying() ) : ?>
			<div class="dol-rounded dol-overflow-hidden">
				<a class="dol-block dol-leading-none" target="_blank" href="<?php echo $container->get_url( true ); ?>"><img width="700" class="dol-block dol-object-cover" alt="<?php echo $container->get_url(); ?>" src="<?php echo $container->get_screenshot(); ?>"></a>
			</div>

			<?php if ( $container->is_running() ) : ?>
				<div class="dol-flex dol-flex-wrap dol-rounded dol-overflow-hidden dol-mt-4">
					<div class="dol-w-full md:dol-w-1/2">
						<a class="dol-block dol-text-gray-600 dol-text-center dol-text-sm dol-bg-gray-200 hover:dol-bg-primary-100 dol-px-4 dol-py-2" href="<?php echo $container->get_url( true ); ?>" target="_blank">
							<?php echo dollie()->icon()->site_view(); ?>
							<?php esc_html_e( 'Visit', 'dollie' ); ?>
						</a>
					</div>
					<div class="dol-w-full md:dol-w-1/2">
						<a class="dol-block dol-text-center dol-text-sm dol-bg-secondary-200 hover: hover:dol-bg-primary-300 dol-text-secondary-600 dol-px-4 dol-py-2" href="<?php echo $container->get_customer_login_url(); ?>" target="_blank">
							<?php echo dollie()->icon()->site_login( 'dol-mr-1' ); ?>
							<?php esc_html_e( 'Admin', 'dollie' ); ?>
						</a>
					</div>
				</div>
			<?php endif; ?>
		<?php else : ?>
			<div class="dol-mt-2 dol-mb-4">
				<div class="dol-flex dol-items-center dol-justify-center dol-uppercase dol-font-medium dol-text-sm dol-text-flame-100 dol-bg-flame-600 dol-rounded dol-tracking-wide dol-px-3 dol-py-2 dol-cursor-default dol-leading-none">
					<svg class="dol-animate-spin dol--ml-1 dol-mr-3 dol-h-5 dol-w-5 " xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
						<circle class="dol-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
						<path class="dol-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
					</svg>
					<?php if ( $container->is_blueprint() ) : ?>
						<?php esc_html_e( 'Launching Blueprint', 'dollie' ); ?>
					<?php else : ?>
						<?php esc_html_e( 'Launching Site', 'dollie' ); ?>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
