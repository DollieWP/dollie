<?php

if ( ! isset( $invalid_container ) ) {
	$invalid_container = false;
}

if ( ! isset( $missing_details ) ) {
	$missing_details = false;
}

if ( ! isset( $container ) ) {
	$container = dollie()->get_container( dollie()->get_current_post_id() );
}

?>

<?php if ( $invalid_container ) : ?>
	<div class="dol-border dol-border-solid dol-border-primary-100 dol-rounded dol-overflow-hidden">
		<div class="dol-flex dol-items-center dol-bg-red-600">
			<div class="dol-p-4 lg:dol-px-8 lg:dol-py-4 dol-bg-red-700 dol-flex dol-items-center dol-justify-center">
				<i class="fas fa-exclamation-circle dol-text-white dol-text-2xl"></i>
			</div>
			<h4 class="dol-px-4 lg:dol-px-8 lg:dol-py-4 dol-m-0 dol-p-0 dol-text-white dol-text-base md:dol-text-xl">
				<?php esc_html_e( 'Wrong site type', 'dollie' ); ?>
			</h4>
		</div>
		<div class="dol-px-4 dol-py-2 lg:dol-px-8 lg:dol-py-6 dol-bg-gray-100">
			<div class="dol-mb-4">
				<?php esc_html_e( 'It seems like this site has encountered some errors while deploying. Please remove it and try again.', 'dollie' ); ?>
			</div>
		</div>
	</div>
<?php elseif ( $missing_details ) : ?>
	<div class="dol-border dol-border-solid dol-border-primary-100 dol-rounded dol-overflow-hidden">
		<div class="dol-flex dol-items-center dol-bg-red-600">
			<div class="dol-p-4 lg:dol-px-8 lg:dol-py-4 dol-bg-red-700 dol-flex dol-items-center dol-justify-center">
				<i class="fas fa-exclamation-circle dol-text-white dol-text-2xl"></i>
			</div>
			<h4 class="dol-px-4 lg:dol-px-8 lg:dol-py-4 dol-m-0 dol-p-0 dol-text-white dol-text-base md:dol-text-xl">
				<?php esc_html_e( 'Sorry, we could not retrieve your site details', 'dollie' ); ?>
			</h4>
		</div>
		<div class="dol-px-4 dol-py-2 lg:dol-px-8 lg:dol-py-6 dol-bg-gray-100">
			<div class="dol-mb-4">
				<?php esc_html_e( 'We could not connect to your site to retrieve its details. This is usually caused by your WordPress site being unavailable or having a site-breaking error.', 'dollie' ); ?>
			</div>

			<div>
				<a href="<?php echo $container->get_permalink( '', [ 'get-details' => 1 ] ); ?>"
					class="dol-text-sm dol-text-white hover:dol-text-white dol-inline-block dol-px-4 dol-py-2 dol-bg-gray-800 hover:dol-bg-gray-900 dol-rounded">
					<?php esc_html_e( 'Retry', 'dollie' ); ?>
				</a>

				<a href="<?php echo esc_url( get_site_url() . '/support' ); ?>"
					class="dol-text-sm dol-text-white hover:dol-text-white dol-inline-block dol-px-4 dol-py-2 dol-bg-gray-500 hover:dol-bg-gray-600 dol-rounded">
					<?php esc_html_e( 'Create a support ticket', 'dollie' ); ?>
				</a>
			</div>
		</div>
	</div>
<?php endif; ?>
