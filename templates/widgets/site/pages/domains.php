<?php

if ( ! isset( $container ) ) {
	$container = dollie()->get_container();
}

$zones  = $container->get_zones();
$routes = $container->get_routes();

if ( is_wp_error( $routes ) ) {
	$routes = [];
}

$credentials = $container->get_credentials();

?>

<h2 class="dol-text-gray-500 text-s dol-font-small dol-uppercase dol-tracking-wide dol-mb-5 dol-text-xl">
	<?php esc_html_e( 'Domains', 'dollie' ); ?>
</h2>

<?php

$dns_manager = get_post_meta( get_the_ID(), 'wpd_domain_dns_manager', true );

?>

<?php if ( 'pending' === $dns_manager ) : ?>

	<div class="dol-my-6">
		<?php
		dollie()->load_template(
			'notice',
			[
				'type'    => 'info',
				'icon'    => 'fas fa-exclamation-circle',
				'title'   => sprintf( __( 'Please hold on whilst "%s" is getting ready to be used', 'dollie' ), get_post_meta( get_the_ID(), 'wpd_domain_pending', true ) ),
				'message' => __( 'Your domain\'s nameservers are being checked. Once we confirm all your nameservers are set correctly, we will automatically replace your website\'s URL and enable the DNS manager.', 'dollie' ),
			],
			true
		);
		?>

		<div class="dol-rounded dol-overflow-hidden dol-shadow dol-mt-6">
			<div class="dol-p-4 lg:dol-px-8 lg:dol-py-6">
				<h4 class="dol-text-lg"><?php esc_html_e( 'Your domain will get validated once we detect the following nameservers attached to it.', 'dollie' ); ?></h4>
				<ul class="dol-m-0">
					<li>pdns1.stratus5.com</li>
					<li>pdns2.stratus5.com</li>
					<li>pdns3.stratus5.com</li>
				</ul>
				<span class="dol-block dol-mb-4 dol-mt-6"><?php esc_html_e( 'Want to change the domain or have you typed in the wrong domain name? You can cancel at any time and try again!', 'dollie' ); ?></span>
				<form action="<?php echo get_permalink( get_the_ID() ); ?>?remove-domain" method="post">
					<button name="remove_customer_dns" id="remove_customer_dns" type="submit" class="dol-px-4 dol-py-2 dol-bg-red-600 dol-text-white dol-rounded">
						<?php echo dollie()->icon()->delete(); ?>
						<?php esc_html_e( 'Cancel', 'dollie' ); ?>
					</button>
				</form>
			</div>
		</div>
	</div>
<?php endif; ?>

<?php if ( ! empty( $routes ) ) : ?>
	<?php

	// dollie()->load_template(
	// 'widgets/site/pages/domain/dns-manager',
	// [
	// 'domain'      => $domain,
	// 'dns_manager' => $dns_manager,
	// ],
	// true
	// );

	?>

	<div class="dol-rounded dol-overflow-hidden dol-shadow dol-mb-6">
		<div class="dol-p-4 lg:dol-px-8 lg:dol-py-4 dol-bg-gray-200">
			<h4 class="dol-p-0 dol-m-0 dol-text-base md:dol-text-xl">
				<?php esc_html_e( 'Live domain linked', 'dollie' ); ?>
			</h4>
		</div>
		<div class="dol-p-4 lg:dol-px-8 lg:dol-py-6">
			<div class="dol-mb-4"><?php esc_html_e( 'Congrats! You have linked your domain. You can always change your domain name by removing the current one and adding a new one.', 'dollie' ); ?></div>

			<div class="dol-font-bold"><?php esc_html_e( 'Your linked domains:', 'dollie' ); ?></div>
			<ul class="dol-m-0 dol-p-0 dol-list-disc dol-list-inside dol-mb-6">
				<?php foreach ( $routes as $route ) : ?>
					<li><?php echo $route['name']; ?></li>
				<?php endforeach; ?>
			</ul>

			<p class="dol-mt-2"><?php esc_html_e( 'Please note that your linked domain will always have to point to the following IP, otherwise your site will be innacesible:', 'dollie' ); ?></p>

			<div class="dol-border-0 dol-border-b dol-border-t dol-border-solid dol-border-gray-200 dol-py-4 dol-px-10 dol-mb-6 dol-text-sm">
				<div class="dol-flex dol-flex-wrap dol-font-bold">
					<div class="dol-w-4/12"><?php esc_html_e( 'TYPE', 'dollie' ); ?></div>
					<div class="dol-w-4/12"><?php esc_html_e( 'CONTENT', 'dollie' ); ?></div>
					<div class="dol-w-4/12"><?php esc_html_e( 'IP ADDRESS', 'dollie' ); ?></div>
				</div>
				<div class="dol-flex dol-flex-wrap">
					<div class="dol-w-4/12">A</div>
					<div class="dol-w-4/12">@</div>
					<div class="dol-w-4/12"><?php echo esc_html( $credentials['ip'] ); ?></div>
				</div>
				<div class="dol-flex dol-flex-wrap">
					<div class="dol-w-4/12">A</div>
					<div class="dol-w-4/12">www</div>
					<div class="dol-w-4/12"><?php echo esc_html( $credentials['ip'] ); ?></div>
				</div>
			</div>

			<form action="<?php echo get_permalink( get_the_ID() ); ?>" method="post">
				<button name="remove_route" id="remove_route" type="submit" class="dol-px-4 dol-py-2 dol-bg-red-600 dol-text-white dol-rounded">
					<?php echo dollie()->icon()->delete(); ?>
					<?php esc_html_e( 'Remove Domain', 'dollie' ); ?>
				</button>
			</form>
		</div>
	</div>
<?php endif; ?>

<?php if ( ! \Dollie\Core\Forms\DomainConnect::instance()->is_form_restricted() ) : ?>
	<div class="dol-mt-6">
		<?php echo do_shortcode( '[dollie_form form="form_dollie_domain_connect"]' ); ?>
	</div>
<?php endif; ?>
