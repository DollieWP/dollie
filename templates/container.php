<?php

get_header();

while ( have_posts() ) {
	the_post();

	$template_id = dollie()->get_site_template_id();

	if ( $template_id ) {
		echo do_shortcode( '[elementor-template id="' . $template_id . '"]' );
	}
}

get_footer();
