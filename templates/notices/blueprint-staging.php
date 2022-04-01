<?php

if ( ! isset( $$container ) ) {
	$container = dollie()->get_container();
}

?>

<div class="dol-fixed dol-w-full dol-bg-gray-700 dol-p-3 dol-text-white dol-bottom-0 dol-left-0 dol-z-50 dol-text-center">
	<?php echo dollie()->icon()->blueprint(); ?>
	<a class="dol-text-white hover:dol-text-white" href=" <?php echo $container->get_permalink(); ?>/blueprints">
		<strong><?php esc_html_e( 'Staging', 'dollie' ); ?></strong>
		- <?php esc_html_e( 'This Blueprint is still in staging mode. Click here to make it available for your customers.', 'dollie' ); ?>
	</a>
</div>
