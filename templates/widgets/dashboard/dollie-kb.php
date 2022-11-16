<?php

dollie()->load_template(
	'loop/kb',
	[
		'title' => __( 'Latest News', 'dollie' ),
		'posts' => $posts,
	],
	true
);
