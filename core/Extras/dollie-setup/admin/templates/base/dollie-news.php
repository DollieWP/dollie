<h2 class="dol-text-gray-500 text-s dol-font-small dol-uppercase dol-tracking-wide dol-mb-1 dol-text-xl">
	Dollie Updates
</h2>

<section>
	<?php
	dollie()->load_template(
		'widgets/dashboard/dollie-news',
		[
			'posts' => dollie()->insights()->get_dashboard_news(),
		],
		true
	);
	?>
</section>
