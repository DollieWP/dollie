<?php
if (dollie()->has_dollie_layout_widget()) { ?>
	<!DOCTYPE html>
	<html <?php language_attributes(); ?>>

	<head>
		<meta charset="<?php bloginfo('charset'); ?>">
		<?php if (!current_theme_supports('title-tag')) : ?>
			<title><?php echo wp_get_document_title(); ?></title>
		<?php endif; ?>
		<?php wp_head(); ?>
		<style type="text/css">
			.hidden {
				display: none;
			}
		</style>
		<script type="text/javascript">
			jQuery('html').addClass('hidden');

			jQuery(document).ready(function($) {
				$('html').removeClass('hidden');
			});
		</script>
	</head>

	<body <?php body_class(); ?>>
	<?php } else {
	get_header();
}
while (have_posts()) {
	the_post();

	$template_id = dollie()->get_site_template_id();

	if ($template_id) {
		echo do_shortcode('[elementor-template id="' . $template_id . '"]');
	}
}
if (dollie()->has_dollie_layout_widget()) { ?>
		<?php wp_footer(); ?>
	</body>

	</html>
<?php } else {
	get_footer();
}
