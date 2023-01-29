<div class="dol-max-w-4xl">
	<h2><?php _e('What Type of Platform Would You Like to Build?', 'dollie-setup'); ?></h2>

	<p class=" !dol-text-l dol-text-gray-500"><?php esc_html_e('Hundreds of partners are building their dream platform using Dollie already. Based on their experiences our team has created some ready-made "Packages" especially designed for specific use cases. Each package contains our recommended plugins, beautiful dashboard designs and default products/subscription examples you can use as the foundation of your platform.', 'dollie-setup'); ?></p>
</div>


<h4>Choose Your Getting Started Package</h4>


<form method="post" action="<?php echo self_admin_url('admin.php?page=dollie_setup'); ?>">
	<div class="wp-list-table widefat dollie-packages">
		<div id="the-list">
			<?php
			foreach (dollie_setup_get_packages() as $package => $class) :
				$incompatible = !is_multisite() && true === dollie_setup_get_package_prop('network', $package);
			?>

				<div class="plugin-card plugin-card-<?php echo sanitize_html_class(dollie_setup_get_package_prop('name', $package)); ?>" style="width:100%; margin-left:0;">
					<div class="plugin-card-top">
						<div class="name column-name">
							<h3><?php esc_attr_e(dollie_setup_get_package_prop('name', $package)); ?>

								<img src="<?php echo esc_url(dollie_setup_get_package_prop('icon_url', $package)); ?>" class="plugin-icon" alt="">
							</h3>
						</div>

						<div class="action-links">
							<ul class="plugin-action-buttons">
								<li><a href="<?php echo $incompatible ? '#' : wp_nonce_url(self_admin_url('admin.php?page=dollie_setup&amp;dollie_setup-package-details=' . $package), 'dollie_setup_package_details'); ?>" class="button <?php echo $incompatible ? 'disabled' : 'activate-now'; ?>" aria-label="<?php printf(esc_html__('Select %s', 'dollie-setup'), dollie_setup_get_package_prop('name', $package)); ?>"><?php esc_html_e('Choose This Package', 'dollie-setup'); ?></a></li>
							</ul>
						</div>

						<div class="desc column-description dol-text-gray-400">
							<?php dollie_setup_get_template_part('description', $package); ?>
							<!--<p class="authors"> <cite>By <a href="">DOLLIE_SETUP Team</a></cite></p>-->
						</div>
					</div>

				</div>

			<?php endforeach; ?>

		</div>
	</div>
</form>
