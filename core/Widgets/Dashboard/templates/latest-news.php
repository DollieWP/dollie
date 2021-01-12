<?php

\Dollie\Core\Utils\Tpl::load(
	DOLLIE_WIDGETS_PATH . 'templates/posts',
	[
		'title' => __( 'Latest news', 'dollie' ),
		'posts' => $posts,
	],
	true
);
