

<ul class="subsubsub">
	<?php if ( defined( 'Dollie_SHOW_PACKAGE_SWITCH' ) && true === constant( 'Dollie_SHOW_PACKAGE_SWITCH' ) && count( dollie_setup_get_packages() ) > 1 ) : ?>
		<li><a class="confirm" href="<?php echo esc_url( wp_nonce_url( self_admin_url( 'admin.php?page=dollie_setup&amp;dollie_setup-package=0' ), 'dollie_setup_select_package' ) ); ?>"><?php esc_html_e( 'Change packages', 'dollie-setup' ); ?></a></li>
	<?php endif; ?>
</ul>
