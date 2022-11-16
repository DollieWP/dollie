<?php

if ( ! isset( $container ) || ! $container->is_blueprint() ) {
	return;
}

if ( get_field( 'wpd_blueprint_image', $container->get_id() ) === 'custom' ) {
	$image = get_field( 'wpd_blueprint_custom_image', $container->get_id() );
} elseif ( get_field( 'wpd_blueprint_image', $container->get_id() ) === 'theme' ) {
	$image = get_post_meta( $container->get_id(), 'wpd_blueprint_active_theme_screenshot_url', true );
} else {
	$image = get_the_post_thumbnail_url( $container->get_id(), 'full' );
}

?>

<div class="dol-w-full dol-rounded dol-relative">
<img data-toggle="tooltip"
	data-placement="bottom"
	data-tooltip="<?php echo esc_attr( $container->get_saved_description() ); ?>"
	class="w-full rounded fw-blueprint-screenshot acf__tooltip" src="<?php echo $image; ?>">
	<?php echo esc_html( $container->get_saved_title() ); ?>
		<?php if ( $container->is_vip() ) : ?>
			<span data-toggle="tooltip"
	data-placement="bottom"
	data-tooltip="This Blueprint is only available for VIPs">
			<?php echo dollie()->icon()->vip( 'dol-text-secondary dol-text-s' ); ?>
		</span>
<?php endif; ?>
				<?php
				$icon           = dollie()->icon()->preview( 'dol-pr-2 dol-t' );

				$path = $container->get_url();
				?>

				<a href="<?php echo esc_url( $path ); ?>" target="_blank" class="dol-absolute dol-top-3 dol-right-3 dol-badge dol-badge-accent dol-text-white dol-text-xs dol-font-semibold dol-p-2 dol-uppercase">
					<?php echo wp_kses_post( sprintf( __( '%s Preview', 'dollie' ), $icon ) ); ?>
				</a>
</div>

