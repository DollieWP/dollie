<?php if ( class_exists( 'WooCommerce' ) && get_option( 'options_wpd_charge_for_deployments' ) === '1' ) : ?>
	<div class=" dol-border dol-bg-white dol-rounded-lg dol-shadow dol-rounded dol-overflow-hidden">
		<div class="dol-p-4 lg:dol-px-8 lg:dol-py-4 dol-bg-primary">
			<h4 class="dol-text-white dol-text-lg dol-font-bold dol-m-0"><?php esc_html_e( 'Plan details', 'dollie' ); ?></h4>
		</div>
		<div class="dol-px-4 dol-py-2 lg:dol-px-8 lg:dol-py-6 dol-bg-ash-100">
			<?php if ( wcs_user_has_subscription( '', '', 'active' ) ) : ?>
				<ul class="dol-list-none dol-p-0 dol-m-0">
					<li>
						<div class="dol-flex dol-flex-wrap">
							<div class="dol-w-1/2 md:dol-w-2/6 lg:dol-w-1/6 dol-text-ash-800">
								<?php esc_html_e( 'Current plan', 'dollie' ); ?>
							</div>
							<div class="dol-w-1/2 md:dol-w-4/6 lg:dol-w-5/6 dol-font-semibold dol-text-ash-800">
								<?php echo dollie()->subscription_name(); ?>
							</div>
						</div>
					</li>
					<li>
						<div class="dol-flex dol-flex-wrap">
							<div class="dol-w-1/2 md:dol-w-2/6 lg:dol-w-1/6 dol-text-ash-800">
								<?php esc_html_e( 'Remaining sites', 'dollie' ); ?>
							</div>
							<div class="dol-w-1/2 md:dol-w-4/6 lg:dol-w-5/6 dol-font-semibold dol-text-ash-800">
								<?php echo dollie()->sites_available(); ?>
							</div>
						</div>
					</li>
					<li>
						<div class="dol-flex dol-flex-wrap">
							<div class="dol-w-1/2 md:dol-w-2/6 lg:dol-w-1/6 dol-text-ash-800">
								<?php esc_html_e( 'Available storage' ); ?>
							</div>
							<div class="dol-w-1/2 md:dol-w-4/6 lg:dol-w-5/6 dol-font-semibold dol-text-ash-800">
								<?php

								$available_storage = dollie()->storage_available();
								if ( $available_storage ) {
									echo esc_html( $available_storage ) . 'GB';
								} else {
									echo esc_html( $available_storage );
								}

								?>
							</div>
						</div>
					</li>
					<li>
						<div class="dol-flex dol-flex-wrap">
							<div class="dol-w-1/2 md:dol-w-2/6 lg:dol-w-1/6 dol-text-ash-800">
								<?php esc_html_e( 'Used storage', 'dollie' ); ?>
							</div>
							<div class="dol-w-1/2 md:dol-w-4/6 lg:dol-w-5/6 dol-font-semibold dol-text-ash-800">
								<?php echo dollie()->convert_to_readable_size( dollie()->get_total_container_size() ); ?>
							</div>
						</div>
					</li>
				</ul>
			<?php else : ?>
				<div>
					<?php esc_html_e( 'You have no active subscriptions. Please sign-up for one of our plans to launch your site(s)!', 'dollie' ); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>
