<!-- Recent Clients -->
<section aria-labelledby="recent-hires-title">
	<div class="dol-overflow-hidden dol-bg-white dol-rounded-lg dol-shadow">
		<div class="dol-p-6">
			<h2 class="dol-text-base dol-font-medium dol-text-gray-900" id="recent-hires-title">
				New <?php echo dollie()->string_variants()->get_user_type_plural_string(); ?>
			</h2>
			<div class="dol-flow-root dol-mt-6">
				<ul role="list" class="dol--my-5 dol-divide-y dol-divide-gray-200">

					<?php
					$args      = [
						[ 'role__in' => [ 'author', 'subscriber', 'customer' ] ],
						'number' => 5, // How many per page
					];
					$customers = new WP_User_Query( $args );
					if ( ! empty( $customers->results ) ) {
						foreach ( $customers->results as $customer ) {
							?>
							<li class="dol-py-3">
								<div class="dol-flex dol-items-center dol-space-x-4">
									<div class="dol-flex-shrink-0">
										<?php echo get_avatar( $customer->ID, '100', '', '', [ 'class' => 'dol-w-8 dol-h-8 dol-rounded-full' ] ); ?>
									</div>
									<div class="dol-flex-1 dol-min-w-0">
										<p class="dol-text-sm dol-font-medium dol-text-gray-900 dol-truncate"><?php echo $customer->display_name; ?></p>
										<p class="dol-text-sm dol-text-gray-500 dol-truncate"><?php echo dollie()->count_customer_containers( $customer->ID ); ?> <?php echo dollie()->get_site_type_plural_string(); ?></p>
									</div>
									<div>
										<a href="<?php echo get_edit_user_link( $customer->ID ); ?>" class="dol-inline-flex dol-items-center dol-shadow-sm dol-px-2.5 dol-py-0.5 dol-border dol-border-gray-300 dol-text-sm dol-leading-5 dol-font-medium dol-rounded-full dol-text-gray-700 dol-bg-white hover:dol-bg-gray-50"> Edit Client </a>
									</div>
								</div>
							</li>

							<?php
						}
					}
					?>
				</ul>
			</div>
			<div class="dol-mt-6">
				<a href="<?php echo get_admin_url(); ?>users.php" class="dol-flex dol-items-center dol-justify-center dol-w-full dol-px-4 dol-py-2 dol-text-sm dol-font-medium dol-text-gray-700 dol-bg-white dol-border dol-border-gray-300 dol-rounded-md dol-shadow-sm hover:dol-bg-gray-50"> View all </a>
			</div>
		</div>
	</div>
</section>
