<?php
/**
 * Package: Agency Core class
 *
 * @package    Dollie_Setup
 * @subpackage Package
 * @since      1.1.0
 */

/**
 * The "agency" DOLLIE_SETUP package.
 *
 * For plugin manifest, see {@link Dollie_Setup_Plugins_Agency}.
 * For admin settings page, see {@link Dollie_Setup_Settings_Agency}.
 *
 * @todo Name subject to change.
 *
 * @since 1.1.0
 */
class Dollie_Setup_Package_Agency extends Dollie_Setup_Package {
	/**
	 * @var string Display name for our package.
	 */
	public static $name = 'Hub';

	/**
	 * @var array Configuration holder.
	 */
	protected static $config = array();

	/**
	 * Package configuration.
	 *
	 * @since 1.1.0
	 */
	protected static function config() {
		return array(
			'icon_url'          => DOLLIE_ASSETS_URL . 'wizard/agency.svg',
			'settings_key'      => '_dollie_setup_admin_settings',
			'documentation_url' => 'http://commonsinabox.org/dollie_setup-agency-overview/?modal=1',
		);
	}

	/**
	 * Register theme.
	 *
	 * @since 1.1.0
	 */
	protected static function theme() {
		return array(
			'name'           => 'Hello Dollie',
			'version'        => '1.0.0',
			'directory_name' => 'hello-dollie',
			'download_url'   => DOLLIE_SETUP_PLUGIN_DIR . 'includes/zip/hello-dollie-1.0.0.zip',
			'admin_settings' => 'themes.php',
			'screenshot_url' => dollie_setup()->plugin_url( 'admin/images/screenshot_dollie_setup_theme.png' ),
		);
	}

	/**
	 * Custom hooks used during package initialization.
	 *
	 * @since 1.1.0
	 */
	protected function custom_init() {
			acf_add_local_field_group(
				array(
					'key'                   => 'group_626681d4e39e8',
					'title'                 => 'Agency Onboarding',
					'fields'                => array(
						array(
							'key'               => 'field_6267d55edab0a',
							'label'             => __( 'About Your Agency', 'dollie' ),
							'name'              => '',
							'type'              => 'message',
							'instructions'      => '',
							'required'          => 0,
							'conditional_logic' => 0,
							'wrapper'           => array(
								'width' => '',
								'class' => 'dollie-field-break',
								'id'    => '',
							),
							'dollie_admin_only' => 0,
							'hide_admin'        => 0,
							'message'           => '',
							'new_lines'         => 'wpautop',
							'esc_html'          => 0,
						),
						array(
							'key'               => 'field_6267df499bce9',
							'label'             => __( 'What is your Agency called?', 'dollie' ),
							'name'              => 'wpd_onboarding_partner_business_name',
							'type'              => 'text',
							'instructions'      => __( 'We only use this info inside your dashboard for customisation.', 'dollie' ),
							'required'          => 1,
							'conditional_logic' => 0,
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'dollie_admin_only' => 0,
							'hide_admin'        => 0,
							'default_value'     => '',
							'placeholder'       => '',
							'prepend'           => '',
							'append'            => '',
							'maxlength'         => '',
						),
						array(
							'key'               => 'field_626682f91629b',
							'label'             => __( 'Do you have plugins or theme that your Agency uses on every project?', 'dollie' ),
							'name'              => 'wpd_onboarding_enable_blueprint',
							'type'              => 'true_false',
							'instructions'      => __( 'Does your Agency have a suite of standard plugins and themes you use for every new client project? Internally you might call this your boilerplate or starter template.', 'dollie' ),
							'required'          => 1,
							'conditional_logic' => 0,
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'dollie_admin_only' => 0,
							'hide_admin'        => 0,
							'message'           => '',
							'default_value'     => 0,
							'ui'                => 1,
							'ui_on_text'        => 'Yes',
							'ui_off_text'       => 'No',
						),
						array(
							'key'               => 'field_626684951629c',
							'label'             => __( 'Do you manage everything for your clients, or do (some of them) also work on their own sites?', 'dollie' ),
							'name'              => 'wpd_onboarding_developer_tools',
							'type'              => 'radio',
							'instructions'      => __( 'With Dollie you can enable powerful self-service features for your clients that gives them easy access to tools like SFTP, a Code Editor, Database Manager and DNS management. You can enable these tools for them easily, or decide to keep them only available for your team.', 'dollie' ),
							'required'          => 1,
							'conditional_logic' => 0,
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'dollie_admin_only' => 0,
							'hide_admin'        => 0,
							'choices'           => array(
								'white-glove'  => 'We do everything for the client, they don\'t use developer tools.',
								'self-service' => 'Our clients use some developer tools themselves to work on their site.',
							),
							'allow_null'        => 0,
							'other_choice'      => 0,
							'default_value'     => '',
							'layout'            => 'vertical',
							'return_format'     => 'value',
							'save_other_choice' => 0,
						),
						array(
							'key'               => 'field_6267bb31934d0',
							'label'             => __( 'Import Example Content', 'dollie' ),
							'name'              => '',
							'type'              => 'message',
							'instructions'      => '',
							'required'          => 0,
							'conditional_logic' => 0,
							'wrapper'           => array(
								'width' => '',
								'class' => 'dollie-field-break',
								'id'    => '',
							),
							'dollie_admin_only' => 0,
							'hide_admin'        => 0,
							'message'           => '',
							'new_lines'         => 'wpautop',
							'esc_html'          => 0,
						),
						array(
							'key'               => 'field_6267bb63934d1',
							'label'             => __( 'Dollie Core Pages', 'dollie' ),
							'name'              => 'wpd_onboarding_core_pages',
							'type'              => 'true_false',
							'instructions'      => __( 'The core Dollie pages.', 'dollie' ),
							'required'          => 0,
							'conditional_logic' => 0,
							'wrapper'           => array(
								'width' => '',
								'class' => 'dollie-field-small',
								'id'    => '',
							),
							'dollie_admin_only' => 0,
							'hide_admin'        => 0,
							'message'           => '',
							'default_value'     => 1,
							'ui'                => 1,
							'ui_on_text'        => '',
							'ui_off_text'       => '',
						),
						array(
							'key'               => 'field_6267ba5558c2d',
							'label'             => __( 'WooCommerce Products', 'dollie' ),
							'name'              => 'wpd_onboarding_example_products',
							'type'              => 'true_false',
							'instructions'      => __( 'Some example WooCommerce products configured for Dollie.', 'dollie' ),
							'required'          => 0,
							'conditional_logic' => 0,
							'wrapper'           => array(
								'width' => '',
								'class' => 'dollie-field-small',
								'id'    => '',
							),
							'dollie_admin_only' => 0,
							'hide_admin'        => 0,
							'message'           => '',
							'default_value'     => 1,
							'ui'                => 1,
							'ui_on_text'        => '',
							'ui_off_text'       => '',
						),
						array(
							'key'               => 'field_6267bac558c2e',
							'label'             => __( 'Landing Pages', 'dollie' ),
							'name'              => 'wpd_onboarding_example_landing',
							'type'              => 'true_false',
							'instructions'      => __( 'Some example Elementor landing pages to promote your Agency services', 'dollie' ),
							'required'          => 0,
							'conditional_logic' => 0,
							'wrapper'           => array(
								'width' => '',
								'class' => 'dollie-field-small',
								'id'    => '',
							),
							'dollie_admin_only' => 0,
							'hide_admin'        => 0,
							'message'           => '',
							'default_value'     => 1,
							'ui'                => 1,
							'ui_on_text'        => '',
							'ui_off_text'       => '',
						),
					),
					'location'              => array(
						array(
							array(
								'param'    => 'af_form',
								'operator' => '==',
								'value'    => 'form_62679f869499e',
							),
						),
					),
					'menu_order'            => 0,
					'position'              => 'normal',
					'style'                 => 'default',
					'label_placement'       => 'top',
					'instruction_placement' => 'label',
					'hide_on_screen'        => '',
					'active'                => true,
					'description'           => '',
					'show_in_rest'          => 0,
				)
			);
	}
}
