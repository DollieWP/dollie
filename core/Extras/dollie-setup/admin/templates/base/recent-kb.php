<h2 class="dol-text-gray-500 dol-uppercase dol-tracking-wide dol-mb-1 dol-text-xl">
	<?php esc_html_e( 'Articles', 'dollie' ); ?>
</h2>

<section>
	<?php
	dollie()->load_template(
		'widgets/dashboard/dollie-kb',
		[
			'posts' => dollie()->insights()->get_kb_articles(),
		],
		true
	);
	?>
</section>
