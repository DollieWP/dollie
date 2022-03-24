<?php?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<?php if ( ! current_theme_supports( 'title-tag' ) ) : ?>
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
		if ( ! \Elementor\Plugin::instance()->editor->is_edit_mode() ) {
			wp_enqueue_script( 'dollie-site-content' );
		}
		$template_id = dollie()->get_site_template_id();

		if ( $template_id ) {
			echo do_shortcode('[elementor-template id="' . $template_id . '"]');
		}
		?>
		<div id="dol-deploying-site" class="dol-hidden" data-container="<?php echo esc_attr( get_the_ID() ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'check_deploy_nonce' ) ); ?>" data-ajax-url="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>"></div>
		<?php
	}

	wp_footer();
	?>
</body>

</html>
