<div class="dol-my-6">
	<?php
	\Dollie\Core\Utils\Tpl::load(
		'notice',
		[
			'type'    => 'info',
			'icon'    => 'fal fa-question',
			'title'   => __( 'Delete sites as Site Administrator', 'dollie' ),
			'message' => __( 'Please be aware that there might be a slight delay before the site is completely undeployed from your Dollie infrastructure. This is to ensure accidental deletion of sites by you or your customers.', 'dollie' ),
			'links'   => [
				[
					'title' => __( 'Learn more', 'dollie' ),
					'url'   => 'https://partners.getdollie.com/knowledge-base/how-can-i-delete-sites-as-a-dollie-administrator/',
				],
			],
		],
		true
	);
	?>
</div>

<div class="dol-mt-6">
	<?php echo do_shortcode( '[dollie_form form="form_dollie_delete_site"]' ); ?>
</div>
