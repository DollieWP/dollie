<div class="dol-my-6">
	<?php

	$container = get_post( get_the_ID() );
	\Dollie\Core\Utils\Tpl::load(
		'notice',
		[
			'type'    => 'info',
			'icon'    => 'fas fa-question',
			'title'   => __( 'Delete site', 'dollie' ),
			'message' => sprintf( __( 'You are about to completely remove <strong>%s</strong> site. Please be aware that the site will be deleted from our infrastructure and can\'t be recovered.', 'dollie' ), $container->post_name ),
		],
		true
	);
	?>
</div>

<div class="dol-mt-6">
	<?php echo do_shortcode( '[dollie_form form="form_dollie_delete_site"]' ); ?>
</div>
