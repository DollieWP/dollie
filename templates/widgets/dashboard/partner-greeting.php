<!-- component -->
<div class="dol-custom-layout dol-layout-sidebar-left">
	<script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>

	<div x-data="{ sidebarOpen: false }" class="dol-flex dol-h-screen">
		<div :class="sidebarOpen ? 'dol-block' : 'dol-hidden'" @click="sidebarOpen = false" class="dol-fixed dol-inset-0 dol-z-20 dol-transition-opacity dol-bg-black dol-opacity-50 lg:dol-hidden"></div>

		<div :class="sidebarOpen ? 'dol-translate-x-0 dol-ease-out' : 'dol--translate-x-full dol-ease-in'" class="dol-fixed dol-inset-y-0 dol-left-0 dol-z-30 dol-w-72 dol-overflow-y-auto dol-transition dol-duration-300 dol-transform dol-bg-white lg:dol-translate-x-0 lg:dol-static lg:dol-inset-0 dol-layout-sidebar dol-bg-gray-700">
			<?php echo do_shortcode('[elementor-template id="' . $settings['sidebar'] . '"]'); ?>
		</div>
		<div class="dol-flex dol-flex-col dol-flex-1 dol-overflow-hidden">
			<header class="dol-flex dol-items-center dol-justify-between dol-px-6 dol-py-4 dol-bg-white dol-border-b-4 dol-border-indigo-600">
				<div class="dol-flex dol-items-center">
					<button @click="sidebarOpen = true" class="dol-text-gray-500 focus:dol-outline-none lg:dol-hidden">
						<svg class="dol-w-6 dol-h-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M4 6H20M4 12H20M4 18H11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
						</svg>
					</button>
					<div class="dol-relative lg:dol-mx-0 dol-layout-header">
						<?php echo do_shortcode('[elementor-template id="' . $settings['header'] . '"]'); ?>
					</div>
				</div>
				<div class="dol-flex dol-items-center">
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
			</header>
			<main class="dol-flex-1 dol-overflow-x-hidden dol-overflow-y-auto">
				<div class="container dol-mx-auto">
					<?php echo do_shortcode('[elementor-template id="' . $settings['content'] . '"]'); ?>
				</div>
			</main>
		</div>
	</div>
</div>
