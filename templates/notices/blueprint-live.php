<?php

if ( ! isset( $container ) ) {
	$container = dollie()->get_container();
}

?>

<div class="dol-fixed dol-w-full dol-bg-orange-700 dol-p-2 dol-text-white dol-bottom-0 dol-left-0 dol-z-50 dol-text-center">
	<a class="dol-text-white hover:dol-text-white dol-text-sm" href="<?php echo $container->get_permalink( 'blueprints' ); ?>">
		<?php echo dollie()->icon()->blueprint(); ?><strong> <?php esc_html_e( 'Live', 'dollie' ); ?></strong> -
		<?php printf( __( 'This Blueprint was last published at %1$s. Make sure to publish it again if you\'ve done any changes.', 'dollie' ), $container->get_changes_update_time() ); ?>
	</a>
</div>
