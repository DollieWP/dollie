<?php
// Retrieve the user's data (here we suppose, of course, the user exists)
$user = get_userdata( get_current_user_id() );
// Get the user's first and last name
$first_name = $user->first_name;
?>
<section aria-labelledby="profile-overview-title">
	<div class="dol-overflow-hidden dol-bg-white dol-rounded-lg dol-shadow">
		<div class="dol-p-6 dol-bg-white">
			<div class="dol-grid dol-items-start">
				<div class="sm:dol-flex sm:dol-items-center sm:dol-justify-between ">
					<div class="sm:dol-flex sm:dol-space-x-5 dol-grid dol-grid-cols-1 dol-gap-4 lg:dol-col-span-2">
						<div class="dol-flex-shrink-0">
							<img class="dol-w-20 dol-h-20 dol-mx-auto dol-rounded-full" src="<?php echo esc_url( DOLLIE_ASSETS_URL . 'wizard/agency.svg' ); ?>"
								alt="">
						</div>
						<div class="dol-mt-4 dol-text-center sm:dol-mt-0 sm:dol-pt-1 sm:dol-text-left">
							<p class="dol-text-sm dol-font-medium dol-text-gray-600"></p>
							<p class="dol-text-xxl dol-font-bold dol-text-gray-900 sm:dol-text-2xl">Hi
								<?php echo $first_name; ?>, Welcome to Your Hub</p>
							<p class="dol-mt-2 dol-flex dol-items-center dol-text-sm dol-text-gray-500 dol-max-w-3xl">
								Here are some things that are still left to do.
							</p>
							<div class="dol-flex dol-mt-3 sm:dol-mt-0">
								<a target="_blank" href="https://control.getdollie.com" class="">Visit Dollie
									Control HQ</a>
							</div>
						</div>
					</div>
					<div class="sm:dol-flex sm:dol-space-x-5 dol-grid dol-grid-cols-1 dol-gap-4 lg:dol-col-span-2">
						<!-- <?php dollie_setup_get_template_part( 'setup-complete' ); ?> -->
					</div>
				</div>

			</div>
		</div>

	</div>
</section>
