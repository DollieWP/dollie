<?php
add_filter( 'woocommerce_enable_setup_wizard', 'mywoo_disable_wizard' );
function mywoo_disable_wizard() {
	return false;
}
// Set Elementor Tracker Notice
update_option( 'elementor_tracker_notice', 1 );


function ocdi_import_files_string() {
	return array(
		array(
			'import_file_name'         => 'Agency Clean',
			'categories'               => array( 'Category 1', 'Category 2' ),
			'local_import_file'        => DOLLIE_PATH . 'core/Extras/dollie-setup/includes/agency/defaults/dollie-agency.xml',
			// 'import_customizer_file_url' => 'http://www.your_domain.com/ocdi/customizer.dat',
			'import_preview_image_url' => 'http://www.your_domain.com/ocdi/preview_import_image1.jpg',
			'preview_url'              => 'http://www.your_domain.com/my-demo-1',
		),
	);
}
add_filter( 'ocdi/import_files', 'ocdi_import_files_string' );

function dollie_hub_plugin_setup( $default_settings ) {
	//$default_settings['parent_slug'] = 'admin.php?page=dollie_setup';
	$default_settings['page_title']  = esc_html__( 'Setup Wizard', 'one-click-demo-import' );
	$default_settings['menu_title']  = esc_html__( 'Setup Your Agency Platform', 'one-click-demo-import' );
	$default_settings['capability']  = 'import';
	//$default_settings['menu_slug']   = 'dollie-setup-wizard';

	return $default_settings;
}
add_filter('ocdi/plugin_page_setup', 'dollie_hub_plugin_setup');
