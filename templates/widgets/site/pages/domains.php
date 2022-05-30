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
	if ( $zone['status'] ) {
		// DNS managed & active
		dollie()->load_template(
			'widgets/site/pages/domain/dns-manager',
			[
				'records'   => $container->get_records(),
				'domain'    => $zone['domain'],
				'container' => $container,
			],
			true
		);
	} else {
		// DNS managed & pending
		dollie()->load_template(
			'widgets/site/pages/domain/dns-manager-pending',
			[
				'domain' => $zone['domain'],
			],
			true
		);
	}
} elseif ( ! empty( $routes ) ) {
	$routes_active = count(
		array_filter(
			$routes,
			function( $route ) {
				return $route['status'];
			}
		)
	);

	// DNS not managed
	dollie()->load_template(
		'widgets/site/pages/domain/dns-not-managed',
		[
			'credentials'   => $container->get_credentials(),
			'routes_active' => $routes_active,
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
