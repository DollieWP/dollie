<section aria-labelledby="announcements-title">
	<div class="dol-overflow-hidden dol-bg-white dol-rounded-lg dol-shadow">
		<div class="dol-p-6">
			<h2 class="dol-text-base dol-font-medium dol-text-gray-900" id="announcements-title"> Core Plugins</h2>
			<div class="dol-flow-root dol-mt-6">

				<!-- This example requires Tailwind CSS v2.0+ -->
				<div>
					<div class="dol-flow-root dol-mt-6">
						<ul role="list" class="dol--my-5 dol-divide-y dol-divide-gray-200">

							<?php
							$dollie_setup_plugins = Dollie_Setup_Plugins::get_plugins();
							foreach (Dollie_Setup_Admin_Plugins::get_settings() as $plugin => $settings_url) {
							?>
								<li class="dol-py-4">
									<div class="dol-flex dol-items-center dol-space-x-4">
										<div class="dol-flex-shrink-0">
											<img class="dol-h-5 dol-w-5 dol-opacity-100 dol-rounded-full" src="<?php echo esc_url(DOLLIE_ASSETS_URL . 'wizard/plugin.svg'); ?>" alt="">
										</div>
										<div class="dol-flex-1 dol-min-w-0">
											<p class="dol-text-sm dol-font-medium dol-text-gray-900"><?php echo $plugin . ' <span class="dol-text-gray-400">' . $dollie_setup_plugins[$plugin]['version'] . '</span>'; ?></p>
											<p class="dol-text-sm dol-text-gray-500"><?php echo $dollie_setup_plugins[$plugin]['dollie_setup_description']; ?></p>
										</div>
										<div>
											<a href="<?php echo $settings_url; ?>" class="dol-inline-flex dol-items-center dol-shadow-sm dol-px-2.5 dol-py-0.5 dol-border dol-border-gray-300 dol-text-sm dol-leading-5 dol-font-medium dol-rounded-full dol-text-gray-700 dol-bg-white hover:dol-bg-gray-50"> View </a>
										</div>
									</div>
								</li>
							<?php } ?>
						</ul>
					</div>
					<div class="dol-mt-6">
						<a href="#" class="dol-w-full dol-flex dol-justify-center dol-items-center dol-px-4 dol-py-2 dol-border dol-border-gray-300 dol-shadow-sm dol-text-sm dol-font-medium dol-rounded-md dol-text-gray-700 dol-bg-white hover:dol-bg-gray-50"> <?php printf(__('<a href="%s">Manage all your Dollie plugins here</a>', 'dollie-setup'), esc_url(self_admin_url('admin.php?page=dollie_setup-plugins'))); ?> </a>
					</div>
				</div>

			</div>
		</div>
	</div>
</section>
