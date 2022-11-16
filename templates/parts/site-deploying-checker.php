<?php

if ( ! isset( $container ) ) {
	$container = dollie()->get_container();
}

if ( is_wp_error( $container ) ) {
	return;
}

?>

<div id="dol-deploying-site" class="dol-hidden" 
	data-container="<?php echo esc_attr( $container->get_id() ); ?>" 
	data-nonce="<?php echo esc_attr( wp_create_nonce( 'check_deploy_nonce' ) ); ?>" 
	data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" 
	data-staging="<?php echo esc_attr( (int) $container->is_staging() ); ?>"></div>
