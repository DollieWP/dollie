<!-- Welcome panel -->
<section aria-labelledby="profile-overview-title">
	<div class="dol-overflow-hidden dol-bg-white dol-rounded-lg dol-shadow">
		<h2 class="dol-sr-only" id="profile-overview-title">Profile Overview</h2>
		<div class="dol-p-6 dol-bg-white">
			<div class="sm:dol-flex sm:dol-items-center sm:dol-justify-between">
				<div class="sm:dol-flex sm:dol-space-x-5">
					<div class="dol-flex-shrink-0">
						<img class="dol-w-20 dol-h-20 dol-mx-auto dol-rounded-full" src="<?php echo esc_url(DOLLIE_ASSETS_URL . 'wizard/agency.svg'); ?>" alt="">
					</div>
					<div class="dol-mt-4 dol-text-center sm:dol-mt-0 sm:dol-pt-1 sm:dol-text-left">
						<p class="dol-text-sm dol-font-medium dol-text-gray-600">Welcome back to</p>
						<p class="dol-text-xl dol-font-bold dol-text-gray-900 sm:dol-text-2xl">Your Agency Dashboard</p>
					</div>
				</div>
				<div class="dol-flex dol-justify-center dol-mt-5 sm:dol-mt-0">
					<a href="#" class="dol-flex dol-items-center dol-justify-center dol-px-4 dol-py-2 dol-text-sm dol-font-medium dol-text-gray-700 dol-bg-white dol-border dol-border-gray-300 dol-rounded-md dol-shadow-sm hover:dol-bg-gray-50"> View profile </a>
				</div>
			</div>
		</div>
		<div class="dol-grid dol-grid-cols-1 dol-border-t dol-border-gray-200 dol-divide-y dol-divide-gray-200 dol-bg-gray-50 sm:dol-grid-cols-3 sm:dol-divide-y-0 sm:dol-divide-x">
			<div class="dol-px-6 dol-py-5 dol-text-sm dol-font-medium dol-text-center">
				<span class="dol-text-gray-900"><?php echo dollie()->count_total_containers(); ?></span>
				<span class="dol-text-gray-600">Customer Sites</span>
			</div>

			<div class="dol-px-6 dol-py-5 dol-text-sm dol-font-medium dol-text-center">
				<span class="dol-text-gray-900"><?php echo dollie()->count_total_blueprints(); ?></span>
				<span class="dol-text-gray-600">Site Blueprints</span>
			</div>

			<div class="dol-px-6 dol-py-5 dol-text-sm dol-font-medium dol-text-center">
				<span class="dol-text-gray-900"><?php echo dollie()->count_customer_staging_sites(); ?></span>
				<span class="dol-text-gray-600">Staging Sites</span>
			</div>
		</div>
	</div>
</section>
