<h2 class="dol-text-gray-500 dol-uppercase dol-tracking-wide dol-mb-1 dol-text-xl">
	<?php printf( __( 'Recently Launched %s', 'dollie' ), dollie()->string_variants()->get_site_type_plural_string() ); ?>
</h2>

<section aria-labelledby="profile-overview-title">
	<?php

	$query = new WP_Query(
		[
			'posts_per_page' => 3,
			'post_type'      => 'container',
			'post_status'    => 'publish',
		]
	);

	$containers = $query->get_posts();

	dollie()->load_template(
		'loop/sites-dashboard',
		[
			'sites'      => $containers,
			'query_data' => [
				'permalink' => get_the_permalink(),
			],
		],
		true
	);

	?>
</section>
