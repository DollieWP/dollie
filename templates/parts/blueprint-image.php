<?php

if ( ! isset( $container ) || ! $container->is_blueprint() ) {
	return;
}

?>

<img data-toggle="tooltip"
	data-placement="bottom"
	data-tooltip="<?php echo esc_attr( $container->get_saved_description() ); ?>"
	class="fw-blueprint-screenshot acf__tooltip" src="<?php echo esc_url( $container->get_screenshot() ); ?>">
<?php echo esc_html( $container->get_saved_title() ); ?>
		<?php if ( $container->is_vip() ) : ?>
			<span data-toggle="tooltip"
	data-placement="bottom"
	data-tooltip="This Blueprint is only available for VIPs">
			<?php echo dollie()->icon()->vip( 'dol-text-secondary dol-text-s' ); ?>
		</span>
		<?php endif; ?>
