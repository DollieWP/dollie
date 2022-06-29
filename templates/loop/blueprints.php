<?php

if ( ! isset( $post ) ) {
	return;
}

$container = dollie()->get_container( $post );

if ( is_wp_error( $container ) || ! $container->is_blueprint() || $container->is_private() ) {
	return false;
}

$product_id = get_field( 'wpd_installation_blueprint_hosting_product' );

$checkout_link = '';

if ( ! empty( $checkout_url ) ) {
	$checkout_link = $checkout_url;
} elseif ( $product_id ) {
	$checkout_link = dollie()->subscription()->get_checkout_link(
		[
			'product_id'   => $product_id[0],
			'blueprint_id' => $container->get_id(),
		]
	);
}

$image = $container->get_screenshot();

if ( get_field( 'wpd_blueprint_image' ) === 'custom' ) {
	$image = get_field( 'wpd_blueprint_custom_image' );
}

?>
<li class="dol-m-0 dol-col-span-1 dol-flex dol-flex-col <?php do_action( 'dol_add_widget_classes' ); ?> dol-divide-y dol-divide-gray-200 dol-p-0 dol-widget-blueprint">
	<img class="dol-w-100 dol-h-100 dol-flex-shrink-0 dol-mx-auto" src="<?php echo $image; ?>" alt="">
	<div class="dol-flex-1 dol-flex dol-flex-col dol-p-6">
		<h3 class="dol-mt-6 dol-text-gray-900 dol-text-xl"><?php echo $container->get_saved_title(); ?></h3>
		<div class="dol-mt-1 dol-flex-grow dol-flex dol-flex-col dol-justify-between">
			<span class="dol-text-gray-500 dol-text-l"><?php echo $container->get_saved_description(); ?></span>
		</div>
	</div>
	<div>
		<div class="dol--mt-px dol-flex dol-divide-x dol-divide-gray-200">
			<div class="dol-w-0 dol-flex-1 dol-flex">
				<?php
				$icon           = dollie()->icon()->preview( 'dol-pr-2' );
				$view_demo_text = ! empty( $view_demo_text )
					? $view_demo_text . ' ' . $icon
					: sprintf( __( '%s View Demo', 'dollie' ), $icon );

				if ( get_field( 'wpd_enable_site_preview', 'option' ) ) {
					$path = dollie()->get_preview_url() . '/?product_id=' . $container->get_id();
				} else {
					$path = $container->get_url();
				}
				?>

				<a href="<?php echo esc_url( $path ); ?>" target="_blank" class="dol-bg-gray-100 dol-relative dol--mr-px dol-w-0 dol-flex-1 dol-inline-flex dol-items-center dol-justify-center dol-py-4 dol-text-sm dol-text-gray-700 dol-font-medium dol-border dol-border-transparent dol-rounded-bl-lg hover:dol-text-gray-500">
					<?php echo wp_kses_post( $view_demo_text ); ?>
				</a>

			</div>
			<?php if ( $checkout_link ) : ?>
				<div class="dol--ml-px dol-w-0 dol-flex-1 dol-flex">
					<a href="<?php echo esc_url( $checkout_link ); ?>" data-id="<?php echo esc_attr( $container->get_id() ); ?>" class="dol-relative dol-w-0 dol-flex-1 dol-inline-flex dol-items-center dol-justify-center dol-py-4 dol-text-sm dol-text-gray-700 dol-font-medium dol-border dol-border-transparent dol-rounded-br-lg hover:dol-text-gray-500 dol-bg-primary-200 dol-text-primary-600">
						<?php if ( ! empty( $launch_button_text ) ) : ?>
							<?php echo $launch_button_text; ?> <?php echo dollie()->icon()->launch( 'dol-pr-2' ); ?>
						<?php else : ?>
							<?php printf( esc_html__( '%1$s Launch %2$s', 'dollie' ), dollie()->icon()->launch( 'dol-pr-2' ), dollie()->string_variants()->get_site_type_string() ); ?>
						<?php endif; ?>
					</a>
				</div>
			<?php endif; ?>
		</div>
	</div>
</li>
