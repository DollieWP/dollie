
	<?php if ( dollie_setup_get_theme_prop( 'screenshot_url' ) ) : ?>
		<a class="thickbox" title="<?php esc_html_e( 'Screenshot of theme', 'dollie-setup' ); ?>" href="<?php echo esc_url( dollie_setup_get_theme_prop( 'screenshot_url' ) ); ?>" style="float:right; margin-left:2em;"><img width="200" src="<?php echo esc_url( dollie_setup_get_theme_prop( 'screenshot_url' ) ); ?>" alt="" /></a>
	<?php endif; ?>

	<p><?php esc_html_e( 'One last step!', 'dollie-setup' ); ?></p>

	<p><?php printf( __( 'The %1$s Theme is the final piece of the Dollie Setup %2$s experience.', 'dollie-setup' ), dollie_setup_get_theme_prop( 'name' ), dollie_setup_get_package_prop( 'name' ) ); ?></p>
