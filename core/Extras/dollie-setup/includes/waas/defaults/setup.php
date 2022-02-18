<?php
function ocdi_import_files_string()
{
	return [
		[
			'import_file_name'           => 'WaaS Clean',
			'categories'                 => ['Category 1', 'Category 2'],
			'local_import_file'            => DOLLIE_PATH . 'core/Extras/dollie-setup/includes/agency/defaults/dollie-products.xml',
			// 'import_customizer_file_url' => 'http://www.your_domain.com/ocdi/customizer.dat',
			'import_preview_image_url'   => 'http://www.your_domain.com/ocdi/preview_import_image1.jpg',
			'preview_url'                => 'http://www.your_domain.com/my-demo-1',
		],
		[
			'import_file_name'           => 'Demo Import 2',
			'categories'                 => ['New category', 'Old category'],
			'import_file_url'            => 'http://www.your_domain.com/ocdi/demo-content2.xml',
			'import_widget_file_url'     => 'http://www.your_domain.com/ocdi/widgets2.json',
			'import_customizer_file_url' => 'http://www.your_domain.com/ocdi/customizer2.dat',
			'import_redux'               => [
				[
					'file_url'    => 'http://www.your_domain.com/ocdi/redux.json',
					'option_name' => 'redux_option_name',
				],
				[
					'file_url'    => 'http://www.your_domain.com/ocdi/redux2.json',
					'option_name' => 'redux_option_name_2',
				],
			],
			'import_preview_image_url'   => 'http://www.your_domain.com/ocdi/preview_import_image2.jpg',
			'preview_url'                => 'http://www.your_domain.com/my-demo-2',
		],
	];
}
add_filter('ocdi/import_files', 'ocdi_import_files_string');
