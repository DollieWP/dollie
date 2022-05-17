<?php

if ( ! isset( $container ) ) {
	return;
}

if ( $container->is_deploying() ) {
	return;
}

?>

<div class="dol-fixed dol-w-full dol-bg-accent dol-p-3 dol-text-white dol-bottom-0 dol-left-0 dol-z-50 dol-text-center">
	<a class="dol-text-white hover:dol-text-white" href="<?php echo $container->get_permalink( 'blueprints' ); ?>">
		<?php echo dollie()->icon()->site(); ?><strong> <?php printf( __( 'Demo %1$s', 'dollie' ), $container->get_type_string() ); ?></strong> -
		<?php printf( __( 'This %1$s is not part of your platform but serves as an example so you can view your awesome dashboard. Pretty neat huh?', 'dollie' ), $container->get_type_string() ); ?>
	</a>
</div>
