<?php if ( class_exists( 'WooCommerce' ) && get_option( 'options_wpd_charge_for_deployments' ) === '1' ) : ?>
	<div class="dol-widget-subscription dol-border <?php do_action( 'dol_add_widget_classes' ); ?> dol-overflow-hidden">
		<div class="dol-widget-title-section dol-p-4 lg:dol-px-8 lg:dol-py-4 dol-bg-primary">
			<h4 class="dol-text-white dol-text-lg dol-m-0">
				<?php echo esc_html( $title ); ?>
			</h4>
		</div>
		<div class="dol-widget-content-section dol-px-4 dol-py-2 lg:dol-px-8 lg:dol-py-6 dol-bg-ash-100">
			<?php if ( wcs_user_has_subscription( '', '', 'active' ) ) : ?>
				<ul class="dol-list-none dol-p-0 dol-m-0">
					<?php do_action( 'dollie/before/subscription/list' ); ?>

					<?php foreach ( $items as $item ) : ?>
						<li>
							<div class="dol-flex dol-flex-wrap">
								<div class="dol-widget-item-title dol-w-1/2 md:dol-w-2/6 lg:dol-w-1/6 dol-text-ash-800">
									<?php echo esc_html( $item['title'] ); ?>
								</div>
								<div class="dol-widget-item-source dol-w-1/2 md:dol-w-4/6 lg:dol-w-5/6 dol-text-ash-800">
									<?php echo esc_html( $item['value'] ); ?>
								</div>
							</div>
						</li>
					<?php endforeach; ?>
	
					<?php do_action( 'dollie/after/subscription/list' ); ?>
				</ul>
			<?php else : ?>
				<div>
					<?php esc_html_e( 'You have no active subscriptions. Please sign-up for one of our plans to launch your site(s)!', 'dollie' ); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>
