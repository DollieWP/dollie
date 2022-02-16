<div class="dol-min-h-full">
	<nav class="dol-bg-gray-800">
		<div class="dol-max-w-7xl dol-mx-auto dol-px-4 sm:dol-px-6 lg:dol-px-8">
			<div class="dol-flex dol-items-center dol-justify-between dol-h-16">
				<div class="dol-flex dol-items-center">
					<div class="dol-flex-shrink-0">
						<div class="dol-text-white">
							<h3 class="dol-text-white dol-font-semibold"> <img class="dol-h-8 dol-w-8" src="https://getdollie.com/wp-content/uploads/2020/01/cropped-icon-only-180x180.png" alt="Workflow"> Agency Platform</h3>
						</div>
					</div>
				</div>
			</div>
		</div>
	</nav>

	<header class="dol-bg-white dol-shadow">
		<div class="dol-max-w-7xl dol-mx-auto dol-py-6 dol-px-4 sm:dol-px-6 lg:dol-px-8">
			<h1 class="dol-text-3xl dol-font-bold dol-text-gray-900">Let's set up your platform!</h1>
		</div>
	</header>
	<main class="dol-bg-gray-100 dol-pt-8 dol-pb-8">
		<div class="dol-max-w-7xl dol-mx-auto dol-py-6 sm:dol-px-6 lg:dol-px-8 dol-bg-white">
			<!-- Replace with your content -->
			<div class="dol-px-4 dol-py-6 sm:dol-px-0">

				<!-- This example requires Tailwind CSS v2.0+ -->
				<div class="dol-flex dol-mb-6">
					<div>

						<?php if (dollie_setup_get_theme_prop('name', $_GET['dollie_setup-package-details'])) : ?>

							<?php if (dollie_setup_get_theme_prop('screenshot_url', $_GET['dollie_setup-package-details'])) : ?>
								<a class="thickbox" title="<?php esc_html_e('Screenshot of theme', 'dollie-setup'); ?>" href="<?php echo esc_url(dollie_setup_get_theme_prop('screenshot_url', $_GET['dollie_setup-package-details'])); ?>" style="float:right; margin-left:2em;"><img width="200" src="<?php echo esc_url(dollie_setup_get_theme_prop('screenshot_url', $_GET['dollie_setup-package-details'])); ?>" alt="" /></a>
							<?php endif; ?>

							<h3><?php esc_html_e('Theme', 'dollie-setup'); ?></h3>
							<?php dollie_setup_get_template_part('package-details-theme', $_GET['dollie_setup-package-details']); ?>

						<?php endif; ?>

						<h3><?php esc_html_e('Plugins', 'dollie-setup'); ?></h3>
						<?php dollie_setup_get_template_part('package-details-plugins', $_GET['dollie_setup-package-details']); ?>

					</div>
					<div class="dol-ml-12 dol-flex-shrink-0">
						<img class="dol-h-64 dol-w-64" src="https://getdollie.com/wp-content/uploads/2019/10/wordpress.png" alt="Workflow">
					</div>
				</div>

			</div>
			<!-- /End replace -->
		</div>
	</main>
</div>
