	<p><?php esc_html_e( "To update, check the plugins you want to update and click 'Update'.", 'dollie-setup' ); ?></p>

	<h3><?php _e( 'Required', 'dollie-setup' ); ?></h3>

	<p><?php printf( __( 'Dollie Setup %s requires the following plugins.', 'dollie-setup' ), dollie_setup_get_package_prop( 'name' ) ); ?></p>
