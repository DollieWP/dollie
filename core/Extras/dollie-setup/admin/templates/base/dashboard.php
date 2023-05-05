<div class="dol-min-h-full dol-mt-8 dol-pt-[100px]">
	<main class="dol-pb-8 dol--mt-24">
		<div class="dol-max-w-3xl dol-px-4 dol-mx-auto sm:dol-px-6 lg:dol-max-w-7xl lg:dol-px-8">
			<h1 class="dol-sr-only">Profile</h1>
			<div class="dol-grid dol-items-start dol-grid-cols-1 dol-gap-4 lg:dol-grid-cols-3 lg:dol-gap-8">
				<div class="dol-grid dol-grid-cols-1 dol-gap-4 lg:dol-col-span-2">
					<?php dollie_setup_get_template_part( 'welcome-header' ); ?>
					<?php dollie_setup_get_template_part( 'setup-complete' ); ?>

					<?php dollie_setup_get_template_part( 'recent-sites' ); ?>
				</div>

				<div class="dol-grid dol-grid-cols-1 dol-gap-4">
					<?php dollie_setup_get_template_part( 'recent-customers' ); ?>
					<?php dollie_setup_get_template_part( 'dollie-plugins' ); ?>
				</div>
			</div>
		</div>
	</main>
</div>
