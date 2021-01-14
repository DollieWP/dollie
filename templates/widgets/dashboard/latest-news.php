<?php

\Dollie\Core\Utils\Tpl::load(
	'loop/posts',
	[
		'title' => __( 'Latest news', 'dollie' ),
		'posts' => $posts,
	],
	true
);
