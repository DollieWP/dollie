<?php

use Dollie\Core\Services\ImportService;

add_filter( 'woocommerce_enable_setup_wizard', 'mywoo_disable_wizard' );
function mywoo_disable_wizard() {
	return false;
}

// Set Elementor Tracker Notice
update_option( 'elementor_tracker_notice', 1 );

function register_forms() {
	af_register_form(
		array(
			'key'            => 'form_649c73282122d',
			'title'          => 'Onboarding Form',
			'display'        => array(
				'description'     => '',
				'success_message' => '',
			),
			'create_entries' => false,
			'is_launch_site' => false,
			'site_blueprint' => false,
			'restrictions'   => array(
				'entries'  => false,
				'user'     => false,
				'schedule' => false,
			),
			'emails'         => false,
		)
	);
}

add_action( 'af/register_forms', 'register_forms' );

/*
 * Onboarding after form submission
 */
add_action( 'af/form/before_submission/key=form_649c73282122d', function ( $form, $fields, $args ) {

	$builder        = af_get_field( 'wpd_choose_builder' );
	$enable_billing = af_get_field( 'wpd_charge_for_deployments' );
	$domain         = af_get_field( 'wpd_api_domain' );

	// Import page builder data
	if ( $builder === 'elementor' ) {
		ImportService::instance()->import_elementor_template();
	} elseif ( $builder === 'gutenberg' ) {
		ImportService::instance()->import_gutenberg_template();
	}

	// Set domain
	if ( $domain ) {
		update_option( 'options_wpd_api_domain', sanitize_text_field( $domain ) );
	}

	// Set billing status
	update_option( 'options_wpd_charge_for_deployments', $enable_billing );

	//set wizard as complete
	update_option( 'options_wpd_welcome_wizard', 1 );

	wp_redirect( admin_url( '/admin.php?page=dollie_setup' ) );
	exit();
}, 10, 3 );


add_filter( 'acf/load_field', function ( $field ) {
	if ( 'wpd_billing_integration' === $field['name'] ) {
		$field['disabled'] = array(
			'edd',
			'memberpress',
			'pmpro',
			'woocommerce',
		);
	}

	return $field;
} );

function ocdi_import_files() {
	return array(
		array(
			'import_file_name'           => 'Elementor Hub Starter',
			'categories'                 => array( 'Agencies', 'WaaS' ),
			'import_file_url'            => 'http://www.your_domain.com/ocdi/demo-content.xml',
			'import_widget_file_url'     => 'http://www.your_domain.com/ocdi/widgets.json',
			'import_customizer_file_url' => 'http://www.your_domain.com/ocdi/customizer.dat',
			'import_redux'               => array(
				array(
					'file_url'    => 'http://www.your_domain.com/ocdi/redux.json',
					'option_name' => 'redux_option_name',
				),
			),
			'import_preview_image_url'   => DOLLIE_ASSETS_URL . 'img/template-elementor.jpg',
			'import_notice'              => __( 'Please make sure you have an Elementor Pro license key to ensure updates and support.', 'your-textdomain' ),
			'preview_url'                => 'https://hub-elementor.wp-site.xyz/',
		),
		array(
			'import_file_name'           => 'Gutenberg Hub Starter',
			'categories'                 => array( 'Agencies', 'WaaS' ),
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
			'import_preview_image_url'   => DOLLIE_ASSETS_URL . 'img/template-gutenberg.jpg',
			'import_notice'              => __( 'Please make sure you have an Bricks Builder license key to ensure updates and support.', 'your-textdomain' ),
			'preview_url'                => 'https://hub-gutenberg.wp-site.xyz/',
		),
	);
}

add_filter( 'ocdi/import_files', 'ocdi_import_files' );

function dollie_hub_plugin_setup( $default_settings ) {
	$default_settings['parent_slug'] = 'dollie_setup';
	$default_settings['page_title']  = esc_html__( 'Setup Wizard', 'one-click-demo-import' );
	$default_settings['menu_title']  = esc_html__( 'Import Hub Design', 'one-click-demo-import' );
	$default_settings['capability']  = 'import';
	$default_settings['menu_slug']   = 'dollie-setup-wizard';

	return $default_settings;
}

add_filter( 'ocdi/plugin_page_setup', 'dollie_hub_plugin_setup' );

function ocdi_plugin_intro_text( $default_text ) {
	$default_text = '<div class="ocdi__intro-text"><p>Here you can choose from one our pre-made Hub design carefully crafted by our team based on popular WordPress page builders. <br>Please note that we do strongly recommend to have an active license for these solutions before you start building your Hub.</p></div>';

	return $default_text;
}

add_filter( 'ocdi/plugin_intro_text', 'ocdi_plugin_intro_text' );

function my_text_strings( $translated_text, $text, $domain ) {
	switch ( $translated_text ) {
		case 'One Click Demo Import':
			$translated_text = __( 'Dollie Hub Starter Designs', 'woocommerce' );
			break;
		case 'Before We Import Your Demo':
			$translated_text = __( 'Before we import your starter Hub Design', 'woocommerce' );
			break;
	}

	return $translated_text;
}

add_filter( 'gettext', 'my_text_strings', 20, 3 );

function ocdi_register_plugins( $plugins ) {
	$theme_plugins = array();

	if ( isset( $_GET['step'] ) && $_GET['step'] === 'import' && isset( $_GET['import'] ) ) {
		// Elementor Blueprint
		if ( $_GET['import'] === '0' ) {
			$theme_plugins = array(
				array(
					'name'     => 'Elementor',
					'slug'     => 'elementor',
					'required' => true,
				),
			);
		}

		// Bricks Builder Blueprint
		if ( $_GET['import'] === '2' ) {
			$theme_plugins = array(
				array(
					'name'     => 'Bricks Builder',
					'slug'     => 'bricks-builder',
					'required' => true,
					'source'   => 'https://control.getdollie.com/releases/?action=get_metadata&slug=bricks-builder',
				),
			);
		}
	}

	return array_merge( $plugins, $theme_plugins );
}

add_filter( 'ocdi/register_plugins', 'ocdi_register_plugins' );
