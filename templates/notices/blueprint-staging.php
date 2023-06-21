<?php

if ( ! isset( $container ) ) {
	$container = dollie()->get_container();
}

?>

<div class="dol-fixed dol-w-full dol-bg-gray-700 dol-p-2 dol-text-white dol-bottom-0 dol-left-0 dol-z-50 dol-text-center">
	<?php echo dollie()->icon()->blueprint(); ?>
	<span class="dol-text-sm">
		<?php
			wp_kses_post(
				printf(
					__( 'This Blueprint has not been published yet. Visit <a class="dol-text-white hover:dol-text-white dol-underline" href="%s">Blueprint Setup</a> to publish and make it available for your customers.', 'dollie' ),
					$container->get_permalink( 'blueprints' )
				)
			)
			?>
	</span>
</div>
