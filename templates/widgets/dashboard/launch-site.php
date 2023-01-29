<div class="dol-widget-launch-site dol-flex dol-flex-wrap dol-overflow-hidden dol-bg-primary dol-p-8 dol-rounded">
	<div class="dol-w-full md:dol-w-4/6 dol-flex dol-flex-wrap dol-items-center">
		
		<?php if ( isset( $icon ) && is_array( $icon ) && ! empty( $icon['value'] ) ) : ?>
			<div class="dol-widget-icon dol-mb-4 md:dol-mb-0 md:dol-mr-8 dol-text-center dol-w-full md:dol-w-auto">
				<?php
				\Elementor\Icons_Manager::render_icon(
					$icon,
					[
						'aria-hidden' => 'true',
						'class'       => 'fa-3x dol-text-white',
					]
				);
				?>
			</div>
		<?php endif; ?>

		<div class="dol-text-center md:dol-text-left">
			<div class="dol-widget-title dol-font-semibold dol-text-2xl dol-text-white dol-uppercase">
				<?php echo esc_html( $title ); ?>
			</div>

			<?php if ( $subtitle ) : ?>
				<div class="dol-widget-subtitle dol-text-primary-300 dol-text-lg">
					<?php echo esc_html( $subtitle ); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<div class="dol-widget-button dol-w-full md:dol-w-2/6 dol-flex dol-items-center dol-justify-end dol-mt-8 md:dol-mt-0">
		<a href="<?php echo dollie()->page()->get_launch_site_url(); ?>" class="dol-border dol-border-solid dol-border-primary-700 dol-block dol-w-full md:dol-inline-block md:dol-w-auto dol-text-center dol-text-white dol-text-lg dol-no-underline dol-px-8 dol-py-4 dol-bg-primary-600 dol-rounded dol-tracking-wider hover:dol-text-white hover:dol-shadow-lg">
			<?php echo esc_html( $button ); ?>
		</a>
	</div>
</div>
