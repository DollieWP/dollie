<h2 class="dol-text-gray-500 text-s dol-font-small dol-uppercase dol-tracking-wide dol-mb-1 dol-text-xl">
	Articles
</h2>

<section>
	<?php
	$data = [
		'posts'    => dollie()->get_dashboard_kb_articles()
	];
	dollie()->load_template('widgets/dashboard/dollie-kb', $data, true);
	?>
</section>
