<!-- component -->
<div class="dol-custom-layout dol-layout-sidebar-left">

	<div class="dol-flex dol-h-screen dol-overflow-hidden" x-data="{ sidebarOpen: false }" @keydown.window.escape="sidebarOpen = false">
		<div x-show="sidebarOpen" class="md:dol-hidden" x-description="Off-canvas menu for mobile, show/hide based on off-canvas menu state." style="display: none;">
			<div class="dol-fixed dol-inset-0 dol-z-40 dol-flex">
				<div @click="sidebarOpen = false" x-show="sidebarOpen" x-description="Off-canvas menu overlay, show/hide based on off-canvas menu state." x-transition:enter="dol-transition-opacity dol-ease-linear dol-duration-300" x-transition:enter-start="dol-opacity-0" x-transition:enter-end="dol-opacity-100" x-transition:leave="dol-transition-opacity dol-ease-linear dol-duration-300" x-transition:leave-start="dol-opacity-100" x-transition:leave-end="dol-opacity-0" class="dol-fixed dol-inset-0" aria-hidden="true" style="display: none;">
					<div class="dol-absolute dol-inset-0 dol-bg-gray-600 dol-opacity-75"></div>
				</div>
				<div x-show="sidebarOpen" x-description="Off-canvas menu, show/hide based on off-canvas menu state." x-transition:enter="dol-transition dol-ease-in-out dol-duration-300 dol-transform" x-transition:enter-start="dol--translate-x-full" x-transition:enter-end="dol-translate-x-0" x-transition:leave="dol-transition dol-ease-in-out dol-duration-300 dol-transform" x-transition:leave-start="dol-translate-x-0" x-transition:leave-end="dol--translate-x-full" class="dol-relative dol-flex dol-flex-col dol-flex-1 dol-w-full dol-max-w-xs dol-pt-5 dol-pb-4 dol-bg-white" style="display: none;">
					<div class="dol-absolute dol-top-0 dol-right-0 dol-pt-2 dol--mr-12">
						<button x-show="sidebarOpen" @click="sidebarOpen = false" class="dol-flex dol-items-center dol-justify-center dol-w-10 dol-h-10 dol-ml-1 dol-rounded-full focus:dol-outline-none focus:dol-ring-2 focus:dol-ring-inset focus:dol-ring-white" style="display: none;">
							<span class="dol-sr-only">Close sidebar</span>
							<svg class="dol-w-6 dol-h-6 dol-text-white" x-description="Heroicon name: x" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
							</svg>
						</button>
					</div>
					<div class="dol-flex-1 dol-h-0 dol-mt-5 dol-overflow-y-auto">
						<?php echo do_shortcode('[elementor-template id="' . $settings['sidebar'] . '"]'); ?>
					</div>
				</div>
				<div class="dol-flex-shrink-0 dol-w-14" aria-hidden="true">
					<!-- Dummy element to force sidebar to shrink to fit close icon -->
				</div>
			</div>
		</div>
		<!-- Static sidebar for desktop -->
		<div class="dol-hidden md:dol-flex md:dol-flex-shrink-0">
			<div class="dol-flex dol-flex-col dol-w-80">
				<!-- Sidebar component, swap this element with another sidebar if you like -->
				<div class="dol-flex dol-flex-col dol-flex-grow dol-pt-5 dol-pb-4 dol-overflow-y-auto dol-bg-white dol-border-r dol-border-gray-200 dol-layout-sidebar dol-bg-gray-700">
					<div class="dol-flex dol-flex-col dol-flex-grow dol-mt-5">
						<?php echo do_shortcode('[elementor-template id="' . $settings['sidebar'] . '"]'); ?>
					</div>
				</div>
			</div>
		</div>
		<div class="dol-flex dol-flex-col dol-flex-1 dol-w-0 dol-overflow-hidden">
			<div class="dol-relative dol-z-10 dol-flex dol-flex-shrink-0 dol-shadow">
				<button @click.stop="sidebarOpen = true" class="dol-px-4 dol-text-gray-500 dol-border-r dol-border-gray-200 focus:dol-outline-none focus:dol-ring-2 focus:dol-ring-inset focus:dol-ring-primary-500 md:dol-hidden">
					<span class="dol-sr-only">Open Sidebar</span>
					<svg class="dol-w-6 dol-h-6" x-description="Heroicon name: menu-alt-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
					</svg>
				</button>
				<div class="dol-flex dol-justify-between dol-flex-1 dol-ml-4 md:dol-ml-6">
					<div class="dol-flex dol-flex-1 dol-layout-header">
						<?php echo do_shortcode('[elementor-template id="' . $settings['header'] . '"]'); ?>
					</div>
					<div class="dol-flex dol-items-center dol-ml-4 md:dol-ml-6">
						<?php
						wp_nav_menu(
							array(
								'container_id'    => false,
								'container_class' => false,
								'menu_class'      => 'lg:dol-flex lg:dol-mx-4',
								'theme_location'  => 'dol_notifications_menu',
								'li_class'        => 'lg:dol-mx-4',
								'fallback_cb'     => false,
							)
						);
						?>
					</div>
				</div>
			</div>

			<main class="dol-relative dol-flex-1 dol-overflow-y-auto focus:dol-outline-none dol-layout-content" tabindex="0" x-data="" x-init="$el.focus()">
				<?php echo do_shortcode('[elementor-template id="' . $settings['content'] . '"]'); ?>
			</main>
		</div>
	</div>
</div>
