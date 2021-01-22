<?php?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<?php if (!current_theme_supports('title-tag')) : ?>
		<title><?php echo wp_get_document_title(); ?></title>
	<?php endif; ?>
	<?php wp_head(); ?>
	<?php
	?>
</head>

<body <?php body_class(); ?>>
	<?php
	while (have_posts()) {
		the_post();

		$template_id = dollie()->get_site_template_id();

		if ($template_id) {
			echo do_shortcode('[elementor-template id="' . $template_id . '"]');
		}
	}

	wp_footer();
	?>
</body>

</html>
