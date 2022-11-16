<?php
// Retrieve the user's data (here we suppose, of course, the user exists)
$user = get_userdata( get_current_user_id() );
// Get the user's first and last name
$first_name = $user->first_name;
?>
<section aria-labelledby="profile-overview-title">
	<div class="dol-overflow-hidden dol-bg-white dol-rounded-lg dol-shadow">
		<div class="dol-p-6 dol-bg-white">
			<div class="sm:dol-flex sm:dol-items-center sm:dol-justify-between">
				<div class="sm:dol-flex sm:dol-space-x-5">
					<div class="dol-flex-shrink-0">
						<img class="dol-w-20 dol-h-20 dol-mx-auto dol-rounded-full" src="<?php echo esc_url( DOLLIE_ASSETS_URL . 'wizard/agency.svg' ); ?>" alt="">
					</div>
					<div class="dol-mt-4 dol-text-center sm:dol-mt-0 sm:dol-pt-1 sm:dol-text-left">
						<p class="dol-text-sm dol-font-medium dol-text-gray-600"></p>
						<p class="dol-text-xxl dol-font-bold dol-text-gray-900 sm:dol-text-2xl">Hi <?php echo $first_name; ?>, Welcome to Your Hub</p>
						<p class="dol-mt-2 dol-flex dol-items-center dol-text-sm dol-text-gray-500">
							 Your Hub is the place where you build your white-labeled platform using WordPress. Everything happens inside your Hub, so you have complete freedom to build something amazing for your customers. Also you have full control over pricing, customer data and branding.
					</p>
					<div class="dol-flex dol-mt-3 sm:dol-mt-0">
					<a target="_blank" href="https://cloud.getdollie.com" class="">Visit Dollie Cloud Dashboard</a>
				</div>
					</div>
				</div>
			</div>
		</div>
		<div class="dol-grid dol-grid-cols-1 dol-border-t dol-border-gray-200 dol-divide-y dol-divide-gray-200 dol-bg-gray-50 sm:dol-grid-cols-3 sm:dol-divide-y-0 sm:dol-divide-x">
			<div class="dol-px-6 dol-py-5 dol-text-sm dol-font-medium dol-text-center">
				<span class="dol-text-gray-900"><?php echo dollie()->count_total_sites(); ?></span>
				<span class="dol-text-gray-600"><?php printf( esc_html__( '%1$s %2$s', 'dollie-setup' ), dollie()->string_variants()->get_user_type_string(), dollie()->string_variants()->get_site_type_plural_string() ); ?></span>
			</div>

			<div class="dol-px-6 dol-py-5 dol-text-sm dol-font-medium dol-text-center">
				<span class="dol-text-gray-900"><?php echo dollie()->count_total_blueprints(); ?></span>
				<span class="dol-text-gray-600"><?php printf( esc_html__( '%s Blueprints', 'dollie-setup' ), dollie()->string_variants()->get_site_type_string() ); ?></span>
			</div>

			<div class="dol-px-6 dol-py-5 dol-text-sm dol-font-medium dol-text-center">
				<span class="dol-text-gray-900"><?php echo dollie()->get_user()->count_stagings(); ?></span>
				<span class="dol-text-gray-600"><?php printf( esc_html__( 'Staging %s', 'dollie-setup' ), dollie()->string_variants()->get_site_type_plural_string() ); ?></span>
			</div>
		</div>
	</div>
</section>
