<?php?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">

	<?php if ( ! current_theme_supports( 'title-tag' ) ) : ?>
		<title><?php echo wp_get_document_title(); ?></title>
	<?php endif; ?>

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<?php
	while ( have_posts() ) {
		the_post();

		if ( ! \Elementor\Plugin::instance()->editor->is_edit_mode() ) {
			wp_enqueue_script( 'dollie-site-content' );
		}

		$template_id = dollie()->get_site_template_id();

		if ( $template_id ) {
			echo do_shortcode( '[elementor-template id="' . $template_id . '"]' );
		}

		if ( dollie()->has_deploying_template() ) {
			dollie()->load_template( 'parts/site-deploying-checker', [], true );
		}
	}

	wp_footer();
	?>
</body>

</html>
