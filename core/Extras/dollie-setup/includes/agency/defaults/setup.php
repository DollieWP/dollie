<?php
function ocdi_import_files_string()
{
	return [
		[
			'import_file_name'           => 'Agency Clean',
			'categories'                 => ['Category 1', 'Category 2'],
			'local_import_file'            => DOLLIE_PATH . 'core/Extras/dollie-setup/includes/agency/defaults/dollie-products.xml',
			// 'import_customizer_file_url' => 'http://www.your_domain.com/ocdi/customizer.dat',
			'import_preview_image_url'   => 'http://www.your_domain.com/ocdi/preview_import_image1.jpg',
			'preview_url'                => 'http://www.your_domain.com/my-demo-1',
		]
	];
}
add_filter('ocdi/import_files', 'ocdi_import_files_string');


