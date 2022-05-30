<?php

if ( ! isset( $container ) ) {
	$container = dollie()->get_container();
}

$zone = $container->get_zones();

if ( is_wp_error( $zone ) ) {
	$zone = [];
}

$routes = $container->get_routes();

if ( is_wp_error( $routes ) ) {
	$routes = [];
}

?>

<h2 class="dol-text-gray-500 text-s dol-font-small dol-uppercase dol-tracking-wide dol-mb-5 dol-text-xl">
	<?php esc_html_e( 'Domains', 'dollie' ); ?>
</h2>

<?php

if ( get_field( 'wpd_enable_dns_manager', 'options' ) && ! empty( $zone ) && isset( $zone['domain'] ) && isset( $zone['status'] ) ) {
	dollie()->load_template(
		'widgets/site/pages/domain/managed',
		[
			'zone'      => $zone,
			'container' => $container,
		],
		true
	);
} elseif ( ! empty( $routes ) ) {
	dollie()->load_template(
		'widgets/site/pages/domain/not-managed',
		[
			'container' => $container,
			'routes'    => $routes,
		],
		true
	);
}

?>
	
<?php if ( ! \Dollie\Core\Forms\DomainConnect::instance()->is_form_restricted() ) : ?>
	<div class="dol-mt-6">
		<?php echo do_shortcode( '[dollie_form form="form_dollie_domain_connect"]' ); ?>
	</div>
<?php endif; ?>
