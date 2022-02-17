<h2 class="dol-text-gray-500 text-s dol-font-small dol-uppercase dol-tracking-wide dol-mb-1 dol-text-xl">
	Recently Launched Sites
</h2>

<section aria-labelledby="profile-overview-title">
	<?php
	$args = [
		'posts_per_page' => 3,
		'post_type'      => 'container',
		'post_status'    => 'publish',
	];

	$sites = new WP_Query($args);

	$data = [
		'sites'      => $sites,
		'query_data' => [
			'permalink'    => get_the_permalink(),
		],
	];
	dollie()->load_template('loop/sites-dashboard', $data, true);
	?>
</section>
