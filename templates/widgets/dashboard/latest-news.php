<?php

dollie()->load_template(
	'loop/posts',
	[
		'title' => __( 'Latest news', 'dollie' ),
		'posts' => $posts,
	],
	true
);
