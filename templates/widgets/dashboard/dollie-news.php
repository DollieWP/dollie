<?php

dollie()->load_template(
	'loop/news',
	[
		'title' => __( 'Latest News', 'dollie' ),
		'posts' => $posts,
	],
	true
);
