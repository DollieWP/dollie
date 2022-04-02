<div class="dol-bg-white dol-shadow dol-overflow-hidden sm:dol-rounded-md">

	<ul role="list" class="dol-divide-y dol-divide-gray-200">

		<?php if ( dollie()->is_api_connected() ) : ?>
			<li>
				<a href="#" class="dol-block hover:dol-bg-gray-50">
					<div class="dol-flex dol-items-center dol-px-4 dol-py-4 sm:dol-px-6">
						<div class="dol-min-w-0 dol-flex-1 dol-flex dol-items-center">
							<div class="dol-flex-shrink-0">
								<img class="dol-h-10 dol-w-10 dol-rounded-full dol-bg-green-400 dol-p-2" src="<?php echo DOLLIE_ASSETS_URL; ?>/img/active.png" alt="">
							</div>
							<div class="dol-min-w-0 dol-flex-1 dol-px-4 md:dol-grid md:dol-grid-cols-2 md:dol-gap-4">
								<div>
									<p class="dol-text-sm dol-font-medium dol-text-green-600 dol-truncate"><?php esc_html_e( 'Successfully connected to Dollie API', 'dollie' ); ?></p>
									<p class="dol-mt-2 dol-flex dol-items-center dol-text-sm dol-text-gray-500">
										<!-- Heroicon name: solid/mail -->
										<span class="dol-truncate">No more steps needed.</span>
									</p>
								</div>
								<div class="dol-hidden dol-mt-3 md:dol-block">
									<div>
										<p class="dol-mt-2 dol-flex dol-items-center dol-text-sm dol-text-gray-500">
											<!-- Heroicon name: solid/check-circle -->
											<svg class="dol-flex-shrink-0 dol-mr-1.5 dol-h-5 dol-w-5 dol-text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
												<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
											</svg>
											Completed
										</p>
									</div>
								</div>
							</div>
						</div>
						<div>
							<!-- Heroicon name: solid/chevron-right -->
							<svg class="dol-h-5 dol-w-5 dol-text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
								<path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
							</svg>
						</div>
					</div>
				</a>
			</li>
		<?php else : ?>
			<li>
				<a href="<?php echo \Dollie\Core\Plugin::instance()->get_api_access_url(); ?>" class="dol-block hover:dol-bg-gray-50">
					<div class="dol-flex dol-items-center dol-px-4 dol-py-4 sm:dol-px-6">
						<div class="dol-min-w-0 dol-flex-1 dol-flex dol-items-center">
							<div class="dol-flex-shrink-0">
								<img class="dol-h-10 dol-w-10 dol-rounded-full dol-bg-red-400 dol-p-2" src="<?php echo DOLLIE_ASSETS_URL; ?>/img/active.png" alt="">
							</div>
							<div class="dol-min-w-0 dol-flex-1 dol-px-4 md:dol-grid md:dol-grid-cols-2 md:dol-gap-4">
								<div>
									<p class="dol-text-sm dol-font-medium dol-text-red-600 dol-truncate"><?php esc_html_e( 'Not connected to Dollie API', 'dollie' ); ?></p>
									<p class="dol-mt-2 dol-flex dol-items-center dol-text-sm dol-text-gray-500">
										<!-- Heroicon name: solid/mail -->
										<span class="dol-truncate">Please connect to the API</span>
									</p>
								</div>
								<div class="dol-hidden dol-mt-3 md:dol-block">
									<div>
										<p class="dol-mt-2 dol-flex dol-items-center dol-text-sm dol-text-gray-500">
											<!-- Heroicon name: solid/check-circle -->
											<svg xmlns="http://www.w3.org/2000/svg" class="dol-flex-shrink-0 dol-mr-1.5 dol-h-5 dol-w-5 dol-text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
												<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
											</svg>
											Not Completed
										</p>
									</div>
								</div>
							</div>
						</div>
						<div>
							<!-- Heroicon name: solid/chevron-right -->
							<svg class="dol-h-5 dol-w-5 dol-text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
								<path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
							</svg>
						</div>
					</div>
				</a>
			</li>
		<?php endif; ?>

		<?php
		if ( get_field( 'wpd_charge_for_deployments', 'options' ) == true && dollie()->has_products() == true ) {
			?>
			<li>
				<a href="#" class="dol-block hover:dol-bg-gray-50">
					<div class="dol-flex dol-items-center dol-px-4 dol-py-4 sm:dol-px-6">
						<div class="dol-min-w-0 dol-flex-1 dol-flex dol-items-center">
							<div class="dol-flex-shrink-0">
								<svg xmlns="http://www.w3.org/2000/svg" class="dol-h-10 dol-w-10 dol-rounded-full dol-bg-green-400 dol-p-2 dol-text-green-100" fill="none" viewBox="0 0 24 24" stroke="currentColor">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
								</svg>
							</div>
							<div class="dol-min-w-0 dol-flex-1 dol-px-4 md:dol-grid md:dol-grid-cols-2 md:dol-gap-4">
								<div>
									<p class="dol-text-sm dol-font-medium dol-text-green-600 dol-truncate"><?php esc_html_e( 'WooCommerce Products Found', 'dollie' ); ?></p>
									<p class="dol-mt-2 dol-flex dol-items-center dol-text-sm dol-text-gray-500">
										<!-- Heroicon name: solid/mail -->
										<span class="dol-truncate">One or more WooCommmerce products with Dollie settings are found.</span>
									</p>
								</div>
								<div class="dol-hidden dol-mt-3 md:dol-block">
									<div>
										<p class="dol-mt-2 dol-flex dol-items-center dol-text-sm dol-text-gray-500">
											<!-- Heroicon name: solid/check-circle -->
											<svg class="dol-flex-shrink-0 dol-mr-1.5 dol-h-5 dol-w-5 dol-text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
												<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
											</svg>
											Completed
										</p>
									</div>
								</div>
							</div>
						</div>
						<div>
							<!-- Heroicon name: solid/chevron-right -->
							<svg class="dol-h-5 dol-w-5 dol-text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
								<path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
							</svg>
						</div>
					</div>
				</a>
			</li>
		<?php } else { ?>
			<li>
				<a href="<?php echo get_admin_url(); ?>post-new.php?post_type=product" class="dol-block hover:dol-bg-gray-50">
					<div class="dol-flex dol-items-center dol-px-4 dol-py-4 sm:dol-px-6">
						<div class="dol-min-w-0 dol-flex-1 dol-flex dol-items-center">
							<div class="dol-flex-shrink-0">
								<svg xmlns="http://www.w3.org/2000/svg" class="dol-h-10 dol-w-10 dol-rounded-full dol-bg-red-400 dol-p-2 dol-text-red-100" fill="none" viewBox="0 0 24 24" stroke="currentColor">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
								</svg>
							</div>
							<div class="dol-min-w-0 dol-flex-1 dol-px-4 md:dol-grid md:dol-grid-cols-2 md:dol-gap-4">
								<div>
									<p class="dol-text-sm dol-font-medium dol-text-red-600 dol-truncate"><?php esc_html_e( 'WooCommerce Setup', 'dollie' ); ?></p>
									<p class="dol-mt-2 dol-flex dol-items-center dol-text-sm dol-text-gray-500">
										<!-- Heroicon name: solid/mail -->
										<span class="dol-truncate">No Products with Dollie Settings Found</span>
									</p>
								</div>
								<div class="dol-hidden dol-mt-3 md:dol-block">
									<div>
										<p class="dol-mt-2 dol-flex dol-items-center dol-text-sm dol-text-gray-500">
											<!-- Heroicon name: solid/check-circle -->
											<svg xmlns="http://www.w3.org/2000/svg" class="dol-flex-shrink-0 dol-mr-1.5 dol-h-5 dol-w-5 dol-text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
												<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
											</svg>
											Not Completed
										</p>
									</div>
								</div>
							</div>
						</div>
						<div>
							<!-- Heroicon name: solid/chevron-right -->
							<svg class="dol-h-5 dol-w-5 dol-text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
								<path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
							</svg>
						</div>
					</div>
				</a>
			</li>

		<?php } ?>


	</ul>
</div>