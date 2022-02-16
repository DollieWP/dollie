

<ul class="subsubsub">
	<?php if ( defined( 'DOLLIE_SETUP_SHOW_PACKAGE_SWITCH' ) && true === constant( 'DOLLIE_SETUP_SHOW_PACKAGE_SWITCH' ) && count( dollie_setup_get_packages() ) > 1 ) : ?>
		<li><a class="confirm" href="<?php echo esc_url( wp_nonce_url( self_admin_url( 'admin.php?page=cbox&amp;cbox-package=0' ), 'dollie_setup_select_package' ) ); ?>"><?php esc_html_e( 'Change packages', 'commons-in-a-box' ); ?></a></li>
	<?php endif; ?>
</ul>
