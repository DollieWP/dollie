<!-- component -->
<div class="dol-custom-layout dol-layout-sidebar-left">
	<script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>



			<div class="dol-flex dol-h-screen dol-overflow-hidden dol-bg-gray-100" x-data="{ sidebarOpen: false }" @keydown.window.escape="sidebarOpen = false">
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
							<div class="dol-flex dol-items-center dol-flex-shrink-0 dol-px-4">
								<img class="dol-w-auto dol-h-8" src="https://tailwindui.com/img/logos/workflow-logo-indigo-600-mark-gray-800-text.svg" alt="Workflow">
							</div>
							<div class="dol-flex-1 dol-h-0 dol-mt-5 dol-overflow-y-auto">
								<nav class="dol-px-2 dol-space-y-1">


									<!-- Current: "dol-bg-gray-100 dol-text-gray-900", Default: "dol-text-gray-600 hover:dol-bg-gray-50 hover:dol-text-gray-900" -->
									<a href="#" class="dol-flex dol-items-center dol-px-2 dol-py-2 dol-text-base dol-font-medium dol-text-gray-900 dol-bg-gray-100 dol-rounded-md group">
										<!-- Current: "dol-text-gray-500", Default: "dol-text-gray-400 group-hover:dol-text-gray-500" -->
										<svg class="dol-w-6 dol-h-6 dol-mr-4 dol-text-gray-500" x-description="Heroicon name: home" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
										</svg>
										Dashboard
									</a>


									<a href="#" class="dol-flex dol-items-center dol-px-2 dol-py-2 dol-text-base dol-font-medium dol-text-gray-600 dol-rounded-md hover:dol-bg-gray-50 hover:dol-text-gray-900 group">
										<svg class="dol-w-6 dol-h-6 dol-mr-4 dol-text-gray-400 group-hover:dol-text-gray-500" x-description="Heroicon name: users" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
										</svg>
										Team
									</a>


									<a href="#" class="dol-flex dol-items-center dol-px-2 dol-py-2 dol-text-base dol-font-medium dol-text-gray-600 dol-rounded-md hover:dol-bg-gray-50 hover:dol-text-gray-900 group">
										<svg class="dol-w-6 dol-h-6 dol-mr-4 dol-text-gray-400 group-hover:dol-text-gray-500" x-description="Heroicon name: folder" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
										</svg>
										Projects
									</a>


									<a href="#" class="dol-flex dol-items-center dol-px-2 dol-py-2 dol-text-base dol-font-medium dol-text-gray-600 dol-rounded-md hover:dol-bg-gray-50 hover:dol-text-gray-900 group">
										<svg class="dol-w-6 dol-h-6 dol-mr-4 dol-text-gray-400 group-hover:dol-text-gray-500" x-description="Heroicon name: calendar" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
										</svg>
										Calendar
									</a>


									<a href="#" class="dol-flex dol-items-center dol-px-2 dol-py-2 dol-text-base dol-font-medium dol-text-gray-600 dol-rounded-md hover:dol-bg-gray-50 hover:dol-text-gray-900 group">
										<svg class="dol-w-6 dol-h-6 dol-mr-4 dol-text-gray-400 group-hover:dol-text-gray-500" x-description="Heroicon name: inbox" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
										</svg>
										Documents
									</a>


									<a href="#" class="dol-flex dol-items-center dol-px-2 dol-py-2 dol-text-base dol-font-medium dol-text-gray-600 dol-rounded-md hover:dol-bg-gray-50 hover:dol-text-gray-900 group">
										<svg class="dol-w-6 dol-h-6 dol-mr-4 dol-text-gray-400 group-hover:dol-text-gray-500" x-description="Heroicon name: chart-bar" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
										</svg>
										Reports
									</a>

								</nav>
							</div>
						</div>
						<div class="dol-flex-shrink-0 dol-w-14" aria-hidden="true">
							<!-- Dummy element to force sidebar to shrink to fit close icon -->
						</div>
					</div>
				</div>

				<!-- Static sidebar for desktop -->
				<div class="dol-hidden md:dol-flex md:dol-flex-shrink-0">
					<div class="dol-flex dol-flex-col dol-w-64">
						<!-- Sidebar component, swap this element with another sidebar if you like -->
						<div class="dol-flex dol-flex-col dol-flex-grow dol-pt-5 dol-pb-4 dol-overflow-y-auto dol-bg-white dol-border-r dol-border-gray-200">
							<div class="dol-flex dol-items-center dol-flex-shrink-0 dol-px-4">
								<img class="dol-w-auto dol-h-8" src="https://tailwindui.com/img/logos/workflow-logo-indigo-600-mark-gray-800-text.svg" alt="Workflow">
							</div>
							<div class="dol-flex dol-flex-col dol-flex-grow dol-mt-5">
								<nav class="dol-flex-1 dol-px-2 dol-space-y-1 dol-bg-white">


									<!-- Current: "dol-bg-gray-100 dol-text-gray-900", Default: "dol-text-gray-600 hover:dol-bg-gray-50 hover:dol-text-gray-900" -->
									<a href="#" class="dol-flex dol-items-center dol-px-2 dol-py-2 dol-text-sm dol-font-medium dol-text-gray-900 dol-bg-gray-100 dol-rounded-md group">
										<!-- Current: "dol-text-gray-500", Default: "dol-text-gray-400 group-hover:dol-text-gray-500" -->
										<svg class="dol-w-6 dol-h-6 dol-mr-3 dol-text-gray-500" x-description="Heroicon name: home" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
										</svg>
										Dashboard
									</a>


									<a href="#" class="dol-flex dol-items-center dol-px-2 dol-py-2 dol-text-sm dol-font-medium dol-text-gray-600 dol-rounded-md hover:dol-bg-gray-50 hover:dol-text-gray-900 group">
										<svg class="dol-w-6 dol-h-6 dol-mr-3 dol-text-gray-400 group-hover:dol-text-gray-500" x-description="Heroicon name: users" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
										</svg>
										Team
									</a>


									<a href="#" class="dol-flex dol-items-center dol-px-2 dol-py-2 dol-text-sm dol-font-medium dol-text-gray-600 dol-rounded-md hover:dol-bg-gray-50 hover:dol-text-gray-900 group">
										<svg class="dol-w-6 dol-h-6 dol-mr-3 dol-text-gray-400 group-hover:dol-text-gray-500" x-description="Heroicon name: folder" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
										</svg>
										Projects
									</a>


									<a href="#" class="dol-flex dol-items-center dol-px-2 dol-py-2 dol-text-sm dol-font-medium dol-text-gray-600 dol-rounded-md hover:dol-bg-gray-50 hover:dol-text-gray-900 group">
										<svg class="dol-w-6 dol-h-6 dol-mr-3 dol-text-gray-400 group-hover:dol-text-gray-500" x-description="Heroicon name: calendar" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
										</svg>
										Calendar
									</a>


									<a href="#" class="dol-flex dol-items-center dol-px-2 dol-py-2 dol-text-sm dol-font-medium dol-text-gray-600 dol-rounded-md hover:dol-bg-gray-50 hover:dol-text-gray-900 group">
										<svg class="dol-w-6 dol-h-6 dol-mr-3 dol-text-gray-400 group-hover:dol-text-gray-500" x-description="Heroicon name: inbox" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
										</svg>
										Documents
									</a>


									<a href="#" class="dol-flex dol-items-center dol-px-2 dol-py-2 dol-text-sm dol-font-medium dol-text-gray-600 dol-rounded-md hover:dol-bg-gray-50 hover:dol-text-gray-900 group">
										<svg class="dol-w-6 dol-h-6 dol-mr-3 dol-text-gray-400 group-hover:dol-text-gray-500" x-description="Heroicon name: chart-bar" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
										</svg>
										Reports
									</a>

								</nav>
							</div>
						</div>
					</div>
				</div>
				<div class="dol-flex dol-flex-col dol-flex-1 dol-w-0 dol-overflow-hidden">
					<div class="dol-relative dol-z-10 dol-flex dol-flex-shrink-0 dol-h-16 dol-bg-white dol-shadow">
						<button @click.stop="sidebarOpen = true" class="dol-px-4 dol-text-gray-500 dol-border-r dol-border-gray-200 focus:dol-outline-none focus:dol-ring-2 focus:dol-ring-inset focus:dol-ring-indigo-500 md:dol-hidden">
							<span class="dol-sr-only">Open sidebar</span>
							<svg class="dol-w-6 dol-h-6" x-description="Heroicon name: menu-alt-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
							</svg>
						</button>
						<div class="dol-flex dol-justify-between dol-flex-1 dol-px-4">
							<div class="dol-flex dol-flex-1">
								<form class="dol-flex dol-w-full md:dol-ml-0" action="#" method="GET">
									<label for="search_field" class="dol-sr-only">Search</label>
									<div class="dol-relative dol-w-full dol-text-gray-400 focus-within:dol-text-gray-600">
										<div class="dol-absolute dol-inset-y-0 dol-left-0 dol-flex dol-items-center dol-pointer-events-none">
											<svg class="dol-w-5 dol-h-5" x-description="Heroicon name: search" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
												<path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
											</svg>
										</div>
										<input id="search_field" class="dol-block dol-w-full dol-h-full dol-py-2 dol-pl-8 dol-pr-3 dol-text-gray-900 dol-placeholder-gray-500 dol-border-transparent focus:dol-outline-none focus:dol-placeholder-gray-400 focus:dol-ring-0 focus:dol-border-transparent sm:dol-text-sm" placeholder="Search" type="search" name="search">
									</div>
								</form>
							</div>
							<div class="dol-flex dol-items-center dol-ml-4 md:dol-ml-6">
								<button class="dol-p-1 dol-text-gray-400 dol-bg-white dol-rounded-full hover:dol-text-gray-500 focus:dol-outline-none focus:dol-ring-2 focus:dol-ring-offset-2 focus:dol-ring-indigo-500">
									<span class="dol-sr-only">View notifications</span>
									<svg class="dol-w-6 dol-h-6" x-description="Heroicon name: bell" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
									</svg>
								</button>

								<!-- Profile dropdown -->
								<div @click.away="open = false" class="dol-relative dol-ml-3" x-data="{ open: false }">
									<div>
										<button @click="open = !open" class="dol-flex dol-items-center dol-max-w-xs dol-text-sm dol-bg-white dol-rounded-full focus:dol-outline-none focus:dol-ring-2 focus:dol-ring-offset-2 focus:dol-ring-indigo-500" id="user-menu" aria-haspopup="true" x-bind:aria-expanded="open">
											<span class="dol-sr-only">Open user menu</span>
											<img class="dol-w-8 dol-h-8 dol-rounded-full" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&amp;ixid=eyJhcHBfaWQiOjEyMDd9&amp;auto=format&amp;fit=facearea&amp;facepad=2&amp;w=256&amp;h=256&amp;q=80" alt="">
										</button>
									</div>
									<div x-show="open" x-description="Profile dropdown panel, show/hide based on dropdown state." x-transition:enter="dol-transition dol-ease-out dol-duration-100" x-transition:enter-start="dol-transform dol-opacity-0 dol-scale-95" x-transition:enter-end="dol-transform dol-opacity-100 dol-scale-100" x-transition:leave="dol-transition dol-ease-in dol-duration-75" x-transition:leave-start="dol-transform dol-opacity-100 dol-scale-100" x-transition:leave-end="dol-transform dol-opacity-0 dol-scale-95" class="dol-absolute dol-right-0 dol-w-48 dol-py-1 dol-mt-2 dol-origin-top-right dol-bg-white dol-rounded-md dol-shadow-lg dol-ring-1 dol-ring-black dol-ring-opacity-5" role="menu" aria-orientation="vertical" aria-labelledby="user-menu" style="display: none;">

										<a href="#" class="dol-block dol-px-4 dol-py-2 dol-text-sm dol-text-gray-700 hover:dol-bg-gray-100" role="menuitem">Your Profile</a>

										<a href="#" class="dol-block dol-px-4 dol-py-2 dol-text-sm dol-text-gray-700 hover:dol-bg-gray-100" role="menuitem">Settings</a>

										<a href="#" class="dol-block dol-px-4 dol-py-2 dol-text-sm dol-text-gray-700 hover:dol-bg-gray-100" role="menuitem">Sign out</a>

									</div>
								</div>
							</div>
						</div>
					</div>

					<main class="dol-relative dol-flex-1 dol-overflow-y-auto focus:dol-outline-none" tabindex="0" x-data="" x-init="$el.focus()">
						<div class="dol-py-6">
							<div class="dol-px-4 dol-mx-auto dol-max-w-7xl sm:dol-px-6 md:dol-px-8">
								<h1 class="dol-text-2xl dol-font-semibold dol-text-gray-900">Dashboard</h1>
							</div>
							<div class="dol-px-4 dol-mx-auto dol-max-w-7xl sm:dol-px-6 md:dol-px-8">
								<!-- Replace with your content -->
								<div class="dol-py-4">
									<div class="dol-border-4 dol-border-gray-200 dol-border-dashed dol-rounded-lg dol-h-96"></div>
								</div>
								<!-- /End replace -->
							</div>
						</div>
					</main>
				</div>
			</div>
</div>
