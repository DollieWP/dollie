<?php
add_filter( 'woocommerce_enable_setup_wizard', 'mywoo_disable_wizard' );
function mywoo_disable_wizard() {
	return false;
}
// Set Elementor Tracker Notice
update_option( 'elementor_tracker_notice', 1 );



function ocdi_import_files() {
	return array(
		array(
			'import_file_name'           => 'Elementor Hub Starter',
			'categories'                 => array( 'Category 1', 'Category 2' ),
			'import_file_url'            => 'http://www.your_domain.com/ocdi/demo-content.xml',
			'import_widget_file_url'     => 'http://www.your_domain.com/ocdi/widgets.json',
			'import_customizer_file_url' => 'http://www.your_domain.com/ocdi/customizer.dat',
			'import_redux'               => array(
				array(
					'file_url'    => 'http://www.your_domain.com/ocdi/redux.json',
					'option_name' => 'redux_option_name',
				),
			),
			'import_preview_image_url'   => 'http://www.your_domain.com/ocdi/preview_import_image1.jpg',
			'import_notice'              => __( 'After you import this demo, you will have to setup the slider separately.', 'your-textdomain' ),
			'preview_url'                => 'http://www.your_domain.com/my-demo-1',
		),
		array(
			'import_file_name'           => 'Bricks Hub Starter',
			'categories'                 => array( 'New category', 'Old category' ),
			'import_file_url'            => 'http://www.your_domain.com/ocdi/demo-content2.xml',
			'import_widget_file_url'     => 'http://www.your_domain.com/ocdi/widgets2.json',
			'import_customizer_file_url' => 'http://www.your_domain.com/ocdi/customizer2.dat',
			'import_redux'               => array(
				array(
					'file_url'    => 'http://www.your_domain.com/ocdi/redux.json',
					'option_name' => 'redux_option_name',
				),
				array(
					'file_url'    => 'http://www.your_domain.com/ocdi/redux2.json',
					'option_name' => 'redux_option_name_2',
				),
			),
			'import_preview_image_url'   => 'http://www.your_domain.com/ocdi/preview_import_image2.jpg',
			'import_notice'              => __( 'A special note for this import.', 'your-textdomain' ),
			'preview_url'                => 'http://www.your_domain.com/my-demo-2',
		),
	);
}
add_filter( 'ocdi/import_files', 'ocdi_import_files' );

function dollie_hub_plugin_setup( $default_settings ) {
	$default_settings['parent_slug'] = 'dollie_setup';
	$default_settings['page_title']  = esc_html__( 'Setup Wizard', 'one-click-demo-import' );
	$default_settings['menu_title']  = esc_html__( 'Setup Your Agency Platform', 'one-click-demo-import' );
	$default_settings['capability']  = 'import';
	$default_settings['menu_slug']   = 'dollie-setup-wizard';

	return $default_settings;
}
add_filter('ocdi/plugin_page_setup', 'dollie_hub_plugin_setup');

function ocdi_register_plugins( $plugins ) {
  $theme_plugins = [
    [ // A WordPress.org plugin repository example.
      'name'     => 'Advanced Custom Fields', // Name of the plugin.
      'slug'     => 'advanced-custom-fields', // Plugin slug - the same as on WordPress.org plugin repository.
      'required' => true,                     // If the plugin is required or not.
    ],
    [ // A locally theme bundled plugin example.
      'name'     => 'Some Bundled Plugin',
      'slug'     => 'bundled-plugin',         // The slug has to match the extracted folder from the zip.
      'source'   => get_template_directory_uri() . '/bundled-plugins/bundled-plugin.zip',
      'required' => false,
    ],
    [
      'name'        => 'Self Hosted Plugin',
      'description' => 'This is the plugin description',
      'slug'        => 'self-hosted-plugin',  // The slug has to match the extracted folder from the zip.
      'source'      => 'https://example.com/my-site/self-hosted-plugin.zip',
      'preselected' => true,
    ],
  ];

  return array_merge( $plugins, $theme_plugins );
}
add_filter( 'ocdi/register_plugins', 'ocdi_register_plugins' );
