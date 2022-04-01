<?php

if ( ! isset( $$container ) ) {
	$container = dollie()->get_container();
}

?>

<div class="dol-fixed dol-w-full dol-bg-secondary dol-p-3 dol-text-white dol-bottom-0 dol-left-0 dol-z-50 dol-text-center">
	<a class="dol-text-white hover:dol-text-white" href="<?php echo $container->get_permalink( 'blueprints' ); ?>">
		<?php echo dollie()->icon()->blueprint(); ?><strong> <?php esc_html_e( 'Live', 'dollie' ); ?></strong> -
		<?php printf( __( 'This Blueprint was last updated at %1$s. Made changes since then? Don’t forget to update this blueprint.', 'dollie' ), $updated_time ); ?>
	</a>
</div>
