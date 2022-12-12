<?php if ( dollie()->has_layout_widget() ) : ?>
<!DOCTYPE html>
	<html <?php language_attributes(); ?>>

	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="profile" href="http://gmpg.org/xfn/11">
		<?php if ( ! current_theme_supports( 'title-tag' ) ) : ?>
			<title><?php echo wp_get_document_title(); ?></title>
		<?php endif; ?>
		<?php wp_head(); ?>
		<style>
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
	<?php do_action( 'dollie_open_body' ); ?>

<?php else : ?>
	<?php get_header(); ?>
<?php endif; ?>

	<?php
	while ( have_posts() ) {
		the_post();

		if ( ! \Elementor\Plugin::instance()->editor->is_edit_mode() ) {
			wp_enqueue_script( 'dollie-site-content' );
		}

		$template_id = dollie()->get_site_template_id();

        // if post type is elementor template
        if ( get_post_type( $template_id ) === 'elementor_library' ) {
            $shortcode = '[elementor-template id="' . $template_id . '"]';
        } else {
            $shortcode = '[dollie-post-data id="' . $template_id . '"]';
        }

		if ( $template_id ) {
			echo do_shortcode( $shortcode );
		}

		if ( dollie()->has_deploying_template() ) {
			dollie()->load_template( 'parts/site-deploying-checker', [], true );
		}
	}
	?>

<?php if ( dollie()->has_layout_widget() ) : ?>
		<?php wp_footer(); ?>
	</body>

	</html>
<?php else : ?>
	<?php get_footer(); ?>
<?php endif; ?>
