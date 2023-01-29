<?php dollie_setup_get_template_part( 'wrapper-header' ); ?>
<?php if ( dollie_setup_get_theme_prop( 'name', $_GET['dollie_setup-package-details'] ) ) : ?>



	<h2><?php esc_html_e( 'Dollie Agency Setup', 'dollie-setup' ); ?></h2>
	<?php dollie_setup_get_template_part( 'package-details-theme', $_GET['dollie_setup-package-details'] ); ?>
	<?php dollie_setup_get_template_part( 'package-onboarding-form' ); ?>

<?php endif; ?>

<?php // dollie_setup_get_template_part( 'package-details-plugins', $_GET['dollie_setup-package-details'] ); ?>
<?php // dollie_setup_get_template_part( 'wrapper-footer' ); ?>
