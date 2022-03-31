<h2 class="dol-text-gray-500 text-s dol-font-small dol-uppercase dol-tracking-wide dol-mb-1 dol-text-xl">
	<?php printf( __( 'Recently Launched %s', 'dollie' ), dollie()->get_site_type_plural_string() ); ?>
</h2>

<section aria-labelledby="profile-overview-title">
	<?php

	$args = [
		'posts_per_page' => 3,
		'post_type'      => 'container',
		'post_status'    => 'publish',
	];

	dollie()->load_template(
		'loop/sites-dashboard',
		[
			'containers' => new WP_Query( $args ),
			'query_data' => [
				'permalink' => get_the_permalink(),
			],
		],
		true
	);

	?>
</section>
