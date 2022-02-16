<!-- This example requires Tailwind CSS v2.0+ -->
<nav class="dol-bg-white dol-shadow">
	<div class="dol-max-w-7xl dol-mx-auto dol-px-2 sm:dol-px-6 lg:dol-px-8">
		<div class="dol-relative dol-flex dol-justify-between dol-h-16">
			<div class="dol-absolute dol-inset-y-0 dol-left-0 dol-flex dol-items-center sm:dol-hidden">
				<!-- Mobile menu button -->
				<button type="button" class="dol-inline-flex dol-items-center dol-justify-center dol-p-2 dol-rounded-md dol-text-gray-400 hover:dol-text-gray-500 hover:dol-bg-gray-100 focus:dol-outline-none focus:dol-ring-2 focus:dol-ring-inset focus:dol-ring-indigo-500" aria-controls="mobile-menu" aria-expanded="false">
					<span class="dol-sr-only">Open main menu</span>
					<!--
            Icon when menu is closed.

            Heroicon name: outline/menu

            Menu open: "dol-hidden", Menu closed: "dol-block"
          -->
					<svg class="dol-block dol-h-6 dol-w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
					</svg>
					<!--
            Icon when menu is open.

            Heroicon name: outline/x

            Menu open: "dol-block", Menu closed: "dol-hidden"
          -->
					<svg class="dol-hidden dol-h-6 dol-w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
					</svg>
				</button>
			</div>
			<div class="dol-flex-1 dol-flex dol-items-center dol-justify-center sm:dol-items-stretch sm:dol-justify-start">
				<div class="dol-flex-shrink-0 dol-flex dol-items-center">
					<img class="dol-block lg:dol-hidden dol-h-8 dol-w-auto" src="https://tailwindui.com/img/logos/workflow-mark-indigo-600.svg" alt="Workflow">
					<img class="dol-hidden lg:dol-block dol-h-8 dol-w-auto" src="https://tailwindui.com/img/logos/workflow-logo-indigo-600-mark-gray-800-text.svg" alt="Workflow">
				</div>
				<div class="dol-hidden sm:dol-ml-6 sm:dol-flex sm:dol-space-x-8">
					<!-- Current: "dol-border-indigo-500 dol-text-gray-900", Default: "dol-border-transparent dol-text-gray-500 hover:dol-border-gray-300 hover:dol-text-gray-700" -->
					<a href="#" class="dol-border-indigo-500 dol-text-gray-900 dol-inline-flex dol-items-center dol-px-1 dol-pt-1 dol-border-b-2 dol-text-sm dol-font-medium"> Dashboard </a>
					<a href="#" class="dol-border-transparent dol-text-gray-500 hover:dol-border-gray-300 hover:dol-text-gray-700 dol-inline-flex dol-items-center dol-px-1 dol-pt-1 dol-border-b-2 dol-text-sm dol-font-medium"> Team </a>
					<a href="#" class="dol-border-transparent dol-text-gray-500 hover:dol-border-gray-300 hover:dol-text-gray-700 dol-inline-flex dol-items-center dol-px-1 dol-pt-1 dol-border-b-2 dol-text-sm dol-font-medium"> Projects </a>
					<a href="#" class="dol-border-transparent dol-text-gray-500 hover:dol-border-gray-300 hover:dol-text-gray-700 dol-inline-flex dol-items-center dol-px-1 dol-pt-1 dol-border-b-2 dol-text-sm dol-font-medium"> Calendar </a>
				</div>
			</div>
			<div class="dol-absolute dol-inset-y-0 dol-right-0 dol-flex dol-items-center dol-pr-2 sm:dol-static sm:dol-inset-auto sm:dol-ml-6 sm:dol-pr-0">
				<button type="button" class="dol-bg-white dol-p-1 dol-rounded-full dol-text-gray-400 hover:dol-text-gray-500 focus:dol-outline-none focus:dol-ring-2 focus:dol-ring-offset-2 focus:dol-ring-indigo-500">
					<span class="dol-sr-only">View notifications</span>
					<!-- Heroicon name: outline/bell -->
					<svg class="dol-h-6 dol-w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
					</svg>
				</button>

				<!-- Profile dropdown -->
				<div class="dol-ml-3 dol-relative">
					<div>
						<button type="button" class="dol-bg-white dol-rounded-full dol-flex dol-text-sm focus:dol-outline-none focus:dol-ring-2 focus:dol-ring-offset-2 focus:dol-ring-indigo-500" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
							<span class="dol-sr-only">Open user menu</span>
							<img class="dol-h-8 dol-w-8 dol-rounded-full" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">
						</button>
					</div>

					<!--
            Dropdown menu, show/hide based on menu state.

            Entering: "dol-transition dol-ease-out dol-duration-200"
              From: "dol-transform dol-opacity-0 dol-scale-95"
              To: "dol-transform dol-opacity-100 dol-scale-100"
            Leaving: "dol-transition dol-ease-in dol-duration-75"
              From: "dol-transform dol-opacity-100 dol-scale-100"
              To: "dol-transform dol-opacity-0 dol-scale-95"
          -->
					<div class="dol-origin-top-right dol-absolute dol-right-0 dol-mt-2 dol-w-48 dol-rounded-md dol-shadow-lg dol-py-1 dol-bg-white dol-ring-1 dol-ring-black dol-ring-opacity-5 focus:dol-outline-none" role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1">
						<!-- Active: "dol-bg-gray-100", Not Active: "" -->
						<a href="#" class="dol-block dol-px-4 dol-py-2 dol-text-sm dol-text-gray-700" role="menuitem" tabindex="-1" id="user-menu-item-0">Your Profile</a>
						<a href="#" class="dol-block dol-px-4 dol-py-2 dol-text-sm dol-text-gray-700" role="menuitem" tabindex="-1" id="user-menu-item-1">Settings</a>
						<a href="#" class="dol-block dol-px-4 dol-py-2 dol-text-sm dol-text-gray-700" role="menuitem" tabindex="-1" id="user-menu-item-2">Sign out</a>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Mobile menu, show/hide based on menu state. -->
	<div class="sm:dol-hidden" id="mobile-menu">
		<div class="dol-pt-2 dol-pb-4 dol-space-y-1">
			<!-- Current: "dol-bg-indigo-50 dol-border-indigo-500 dol-text-indigo-700", Default: "dol-border-transparent dol-text-gray-500 hover:dol-bg-gray-50 hover:dol-border-gray-300 hover:dol-text-gray-700" -->
			<a href="#" class="dol-bg-indigo-50 dol-border-indigo-500 dol-text-indigo-700 dol-block dol-pl-3 dol-pr-4 dol-py-2 dol-border-l-4 dol-text-base dol-font-medium">Dashboard</a>
			<a href="#" class="dol-border-transparent dol-text-gray-500 hover:dol-bg-gray-50 hover:dol-border-gray-300 hover:dol-text-gray-700 dol-block dol-pl-3 dol-pr-4 dol-py-2 dol-border-l-4 dol-text-base dol-font-medium">Team</a>
			<a href="#" class="dol-border-transparent dol-text-gray-500 hover:dol-bg-gray-50 hover:dol-border-gray-300 hover:dol-text-gray-700 dol-block dol-pl-3 dol-pr-4 dol-py-2 dol-border-l-4 dol-text-base dol-font-medium">Projects</a>
			<a href="#" class="dol-border-transparent dol-text-gray-500 hover:dol-bg-gray-50 hover:dol-border-gray-300 hover:dol-text-gray-700 dol-block dol-pl-3 dol-pr-4 dol-py-2 dol-border-l-4 dol-text-base dol-font-medium">Calendar</a>
		</div>
	</div>
</nav>

<div class="dol-min-h-full dol-mt-8 dol-pt-[100px]">
	<main class="dol-pb-8 dol--mt-24">
		<div class="dol-max-w-3xl dol-px-4 dol-mx-auto sm:dol-px-6 lg:dol-max-w-7xl lg:dol-px-8">

			<div class="dol-min-h-full dol-mt-8 dol-pt-[100px]">
				<main class="dol-pb-8 dol--mt-24">
					<div class="dol-max-w-3xl dol-px-4 dol-mx-auto sm:dol-px-6 lg:dol-max-w-7xl lg:dol-px-8">
