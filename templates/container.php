<?php if ( dollie()->has_dollie_layout_widget() ) : ?>
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
		if ( $template_id ) {
			echo do_shortcode( '[elementor-template id="' . $template_id . '"]' );
		}
		?>
		<div id="dol-deploying-site" class="dol-hidden" data-container="<?php echo esc_attr( get_the_ID() ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'check_deploy_nonce' ) ); ?>" data-ajax-url="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>"></div>
		<?php
	}
	?>

<?php if ( dollie()->has_dollie_layout_widget() ) : ?>
		<?php wp_footer(); ?>
	</body>

	</html>
<?php else : ?>
	<?php get_footer(); ?>
<?php endif; ?>
