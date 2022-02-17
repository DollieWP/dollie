<?php dollie_setup_get_template_part('wrapper-header'); ?>
<?php if (dollie_setup_get_theme_prop('name', $_GET['dollie_setup-package-details'])) : ?>

	<?php if (dollie_setup_get_theme_prop('screenshot_url', $_GET['dollie_setup-package-details'])) : ?>
		<a class="thickbox" title="<?php esc_html_e('Screenshot of theme', 'dollie-setup'); ?>" href="<?php echo esc_url(dollie_setup_get_theme_prop('screenshot_url', $_GET['dollie_setup-package-details'])); ?>" style="float:right; margin-left:2em;"><img width="200" src="<?php echo esc_url(dollie_setup_get_theme_prop('screenshot_url', $_GET['dollie_setup-package-details'])); ?>" alt="" /></a>
	<?php endif; ?>

	<h3><?php esc_html_e('Theme', 'dollie-setup'); ?></h3>
	<?php dollie_setup_get_template_part('package-details-theme', $_GET['dollie_setup-package-details']); ?>

<?php endif; ?>

<h3><?php esc_html_e('Plugins', 'dollie-setup'); ?></h3>
<?php dollie_setup_get_template_part('package-details-plugins', $_GET['dollie_setup-package-details']); ?>
<?php dollie_setup_get_template_part('wrapper-footer'); ?>
