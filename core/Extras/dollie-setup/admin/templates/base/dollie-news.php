<h2 class="dol-text-gray-500 text-s dol-font-small dol-uppercase dol-tracking-wide dol-mb-1 dol-text-xl">
	Dollie Updates
</h2>

<section>
	<?php
	$data = [
		'posts'    => dollie()->get_dashboard_posts()
	];
	dollie()->load_template('widgets/dashboard/dollie-news', $data, true);
	?>
</section>
