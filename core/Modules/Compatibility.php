<?php
//Check if Astra is active
if ('astra' == get_template()) {

	// Update the container layout based on Custom Post Type.
	add_filter('astra_get_content_layout', 'custom_post_type_content_layout');
	function custom_post_type_content_layout($layout)
	{
		// Add your CPT instead of newspaper, book. If it's only for one CPT then add just one CPT.
		if ( dollie()->has_dollie_layout_widget() ) {
			$layout = 'page-builder'; // This is Boxed layout
		}
		return $layout;
	}
	/**
	 *
	 *
	 */
	function dol_set_page_template() {
		global $wp_query;
		include_once ABSPATH . 'wp-admin/includes/theme.php';
		$template = 'elementor_canvas';
		if ( dollie()->has_dollie_layout_widget() && in_array($template, get_page_templates()) ) {
			update_post_meta($wp_query->post->ID, '_wp_page_template', $wishedTemplate);
		}

	}
	add_action( 'template_redirect','dol_set_page_template' );

}
