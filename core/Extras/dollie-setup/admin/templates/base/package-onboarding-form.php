<p class="dol-mb-5"><?php printf( esc_html__( 'With Dollie you can create a powerful platform for your Agency, that can be used by your team and your clients. Ready to get started?', 'dollie-setup' ), dollie_setup_get_package_prop( 'name', $_GET['dollie_setup-package-details'] ) ); ?></p>

<div class="dol-mt-5 dol-p-0">
	<?php
	$current_page = admin_url( 'admin.php?page=' . $_GET['page'] );
	// echo do_shortcode( '[dollie_form form="form_dollie_' . $_GET['dollie_setup-package-details'] . '_onboarding" ]' )
	echo do_shortcode( '[dollie_form form="form_626802e1966b3" redirect="' . $current_page . '" ]' )
	?>
</div>
