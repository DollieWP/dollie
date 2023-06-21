<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header();

?>
    <div class="<?php echo apply_filters( 'dollie/builder_page_template/container_classes', 'dol-tpl-page dol-container dol-mx-auto' ); ?>">
		<?php
		/**
		 * Before page template content.
		 *
		 *
		 * @since 2.0.0
		 */
		do_action( 'dollie/builder_page_template/before_content' );

		while ( have_posts() ) :
			the_post();
			the_content();
		endwhile;
		/**
		 * After page template content.
		 *
		 *
		 * @since 2.0.0
		 */
		do_action( 'dollie/builder_page_template/after_content' );
		?>
    </div>
<?php

get_footer();
