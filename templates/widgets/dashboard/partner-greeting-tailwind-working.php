<script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>
<!-- This example requires Tailwind CSS v2.0+ -->
<div class="dol-flex dol-h-screen dol-overflow-hidden dol-bg-white">
	<!-- Off-canvas menu for mobile, show/hide based on off-canvas menu state. -->
	<div class="lg:dol-hidden">
		<div class="dol-fixed dol-inset-0 dol-z-40 dol-flex">
			<!--
        Off-canvas menu overlay, show/hide based on off-canvas menu state.

        Entering: "dol-transition-opacity dol-ease-linear dol-duration-300"
          From: "dol-opacity-0"
          To: "dol-opacity-100"
        Leaving: "dol-transition-opacity dol-ease-linear dol-duration-300"
          From: "dol-opacity-100"
          To: "dol-opacity-0"
      -->
			<div class="dol-fixed dol-inset-0">
				<div class="dol-absolute dol-inset-0 dol-bg-gray-600 dol-opacity-75"></div>
			</div>
			<!--
        Off-canvas menu, show/hide based on off-canvas menu state.

        Entering: "dol-transition dol-ease-in-out dol-duration-300 dol-transform"
          From: "dol--translate-x-full"
          To: "dol-translate-x-0"
        Leaving: "dol-transition dol-ease-in-out dol-duration-300 dol-transform"
          From: "dol-translate-x-0"
          To: "dol--translate-x-full"
      -->
			<div tabindex="0" class="dol-relative dol-flex dol-flex-col dol-flex-1 dol-w-full dol-max-w-xs dol-bg-white focus:dol-outline-none">
				<div class="dol-absolute dol-top-0 dol-right-0 dol-pt-2 dol--mr-12">
					<button type="button" class="dol-flex dol-items-center dol-justify-center dol-w-10 dol-h-10 dol-ml-1 dol-rounded-full focus:dol-outline-none focus:dol-ring-2 focus:dol-ring-inset focus:dol-ring-white">
						<span class="dol-sr-only">Close sidebar</span>
						<!-- Heroicon name: x -->
						<svg class="dol-w-6 dol-h-6 dol-text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
						</svg>
					</button>
				</div>
			</div>
			<div class="dol-flex-shrink-0 dol-w-14" aria-hidden="true">
				<!-- Force sidebar to shrink to fit close icon -->
			</div>
		</div>
	</div>

	<!-- Static sidebar for desktop -->
	<div class="dol-hidden lg:dol-flex lg:dol-flex-shrink-0 dol-sidebar-widget">
		<div class="dol-flex dol-flex-col dol-w-72">
			<!-- Sidebar component, swap this element with another sidebar if you like -->
			<div class="dol-flex dol-flex-col dol-flex-1 dol-h-0 dol-bg-gray-100 dol-border-r dol-border-gray-200">
				<div class="dol-flex dol-flex-col dol-flex-1 dol-pt-5 dol-pb-4 dol-overflow-y-auto">
					<div class="dol-flex dol-items-center dol-flex-shrink-0 dol-px-4">
						<?php echo do_shortcode('[elementor-template id="' . $settings['sidebar'] . '"]'); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="dol-flex dol-flex-col dol-flex-1 dol-min-w-0 dol-overflow-hidden">
		<div class="lg:dol-hidden">
			<div class="dol-flex dol-items-center dol-justify-between dol-bg-gray-50 dol-border-b dol-border-gray-200 dol-px-4 dol-py-1.5">
				<div>
					<img class="dol-w-auto dol-h-8" src="https://tailwindui.com/img/logos/workflow-mark-indigo-600.svg" alt="Workflow">
				</div>
				<div>
					<button type="button" class="dol-inline-flex dol-items-center dol-justify-center dol-w-12 dol-h-12 dol--mr-3 dol-text-gray-500 dol-rounded-md hover:dol-text-gray-900">
						<span class="dol-sr-only">Open sidebar</span>
						<!-- Heroicon name: menu -->
						<svg class="dol-w-6 dol-h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
						</svg>
					</button>
				</div>
			</div>
		</div>
		<div class="dol-relative dol-z-0 dol-flex dol-flex-1 dol-overflow-hidden">
			<main class="dol-relative dol-z-0 dol-flex-1 dol-overflow-y-auto focus:dol-outline-none xl:dol-order-last" tabindex="0">
				<div class="dol-pt-6 dol-pb-10 dol-pt-10">
					<?php echo do_shortcode('[elementor-template id="' . $settings['content'] . '"]'); ?>
				</div>
			</main>
			<aside class="dol-relative dol-flex-shrink-0 dol-hidden dol-border-r dol-border-gray-200 xl:dol-order-first xl:dol-flex xl:dol-flex-col dol-w-96">
				<!-- Start secondary column (hidden on smaller screens) -->
				<div class="dol-absolute dol-inset-0 dol-px-4 dol-py-6 sm:dol-px-6 lg:dol-px-8">
					<div class="dol-h-full dol-border-4 dol-border-gray-200 dol-border-dashed dol-rounded-lg"></div>
				</div>
				<!-- End secondary column -->
			</aside>
		</div>
	</div>
</div>
