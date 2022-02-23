<?php
function ocdi_import_files_string()
{
	return [
		[
			'import_file_name'           => 'Hosting Example Content',
			'categories'                 => ['Category 1', 'Category 2'],
			'local_import_file'            => DOLLIE_PATH . 'core/Extras/dollie-setup/includes/hosting/defaults/dollie-products.xml',
			// 'import_customizer_file_url' => 'http://www.your_domain.com/ocdi/customizer.dat',
			'import_preview_image_url'   => 'http://www.your_domain.com/ocdi/preview_import_image1.jpg',
			'preview_url'                => 'http://www.your_domain.com/my-demo-1',
		]
	];
}
add_filter('ocdi/import_files', 'ocdi_import_files_string');

function ocdi_plugin_page_setup($default_settings)
{
	$default_settings['parent_slug'] = 'admin.php?page=dollie_setup';
	$default_settings['page_title']  = esc_html__('Setup Wizard', 'one-click-demo-import');
	$default_settings['menu_title']  = esc_html__('Setup Your Hosting Platform', 'one-click-demo-import');
	$default_settings['capability']  = 'import';
	$default_settings['menu_slug']   = 'dollie-setup-wizard';

	return $default_settings;
}
add_filter('ocdi/plugin_page_setup', 'ocdi_plugin_page_setup');


