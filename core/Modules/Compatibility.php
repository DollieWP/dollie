<?php
//Check if Astra is active
if ('astra' == get_template()) {

	add_filter('astra_get_content_layout', 'custom_post_type_content_layout');
	// Update the container layout based on Custom Post Type.
	function custom_post_type_content_layout($layout)
	{
		// Add your CPT instead of newspaper, book. If it's only for one CPT then add just one CPT.
		if (is_singular(array('container')) && dollie()->has_dollie_layout_widget()) {
			$layout = 'page-builder'; // This is Boxed layout
		}
		return $layout;
	}

}
