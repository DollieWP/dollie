<?php
function dol_sanitize_hex_color($hex_color, $setting)
{
	// Sanitize $input as a hex value.
	$hex_color = sanitize_hex_color($hex_color);

	// If $input is a valid hex value, return it; otherwise, return the default.
	return (!is_null($hex_color) ? $hex_color : $setting->default);
}


add_action('customize_register', 'dol_colors_init');
function dol_colors_init($wp_customize)
{
	/*
	* Failsafe is safe
	*/
	if (!isset($wp_customize)) {
		return;
	}


	/**
	 * Add Header Section for General Options.
	 *
	 * @uses $wp_customize->add_section() https://developer.wordpress.org/reference/classes/wp_customize_manager/add_section/
	 * @link $wp_customize->add_section() https://codex.wordpress.org/Class_Reference/WP_Customize_Manager/add_section
	 */
	$wp_customize->add_section(
		// $id
		'dollie_colors_section',
		// $args
		array(
			'title' => __('Dollie', 'dollie'),
			'description' => __('Set colors for Dollie widgets', 'dollie'),
			'priority' => 9
		)
	);


	/**
	 * Header Background Color setting.
	 *
	 * - Setting: Header Background Color
	 * - Control: WP_Customize_Color_Control
	 * - Sanitization: hex_color
	 *
	 * Uses a color wheel to configure the Header Background Color setting.
	 *
	 * @uses $wp_customize->add_setting() https://developer.wordpress.org/reference/classes/wp_customize_manager/add_setting/
	 * @link $wp_customize->add_setting() https://codex.wordpress.org/Class_Reference/WP_Customize_Manager/add_setting
	 */
	$wp_customize->add_setting(
		// $id
		'dollie_color_primary',
		// $args
		array(
			'type'              => 'option',
			'sanitize_callback' => 'dol_sanitize_hex_color',
			//'transport' => 'postMessage'
		)
	);

	$wp_customize->add_setting(
		// $id
		'dollie_color_secondary',
		// $args
		array(
			'type'              => 'option',
			'sanitize_callback' => 'dol_sanitize_hex_color',
			//'transport' => 'postMessage'
		)
	);


	/**
	 * Core Color control.
	 *
	 * - Control: Color
	 * - Setting: Header Background Color
	 * - Sanitization: hex_color
	 *
	 * Register "WP_Customize_Color_Control" to be used to configure the Header Background Color setting.
	 *
	 * @uses $wp_customize->add_control() https://developer.wordpress.org/reference/classes/wp_customize_manager/add_control/
	 * @link $wp_customize->add_control() https://codex.wordpress.org/Class_Reference/WP_Customize_Manager/add_control
	 *
	 * @uses WP_Customize_Color_Control() https://developer.wordpress.org/reference/classes/wp_customize_color_control/
	 * @link WP_Customize_Color_Control() https://codex.wordpress.org/Class_Reference/WP_Customize_Color_Control
	 */
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			// $wp_customize object
			$wp_customize,
			// $id
			'dollie_color_primary_control',
			// $args
			array(
				'settings' => 'dollie_color_primary',
				'section' => 'dollie_colors_section',
				'label' => __('Primary Color', 'dollie'),
				'description' => __('Select the primary color used across Dollie.', 'dollie'),
			)
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			// $wp_customize object
			$wp_customize,
			// $id
			'dollie_color_secondary_control',
			// $args
			array(
				'settings' => 'dollie_color_secondary',
				'section' => 'dollie_colors_section',
				'label' => __('Secondary Color', 'dollie'),
				'description' => __('Select the secondary color used across Dollie', 'dollie'),
			)
		)
	);

}


/**
 * Registers the Theme Customizer Preview with WordPress.
 *
 * @package    sk
 * @since      0.3.0
 * @version    0.3.0
 */
function dol_customizer_live_preview()
{
	wp_enqueue_script(
		'dol-theme-customizer',
		get_stylesheet_directory_uri() . '/js/theme-customizer.js',
		array('customize-preview'),
		'0.1.0',
		true
	);
} // end dol_customizer_live_preview
add_action('customize_preview_init', 'dol_customizer_live_preview');


/**
 * Writes the Header Background related controls' values out to the 'head' element of the document
 * by reading the value(s) from the theme mod value in the options table.
 */
function dol_customizer_css()
{
	require_once DOLLIE_PATH . 'vendor/mexitek/phpcolors/src/Mexitek/PHPColors/Color.php';

	$primary_option = get_option('dollie_color_primary');
	$secondary_option = get_option('dollie_color_secondary');

	if ( $primary_option ) {
		$primary_color = new Mexitek\PHPColors\Color(get_option('dollie_color_primary'));
	} else {
		$primary_color = new Mexitek\PHPColors\Color('#51AABF');
	}
	if ($secondary_option) {
		$secondary_color = new Mexitek\PHPColors\Color(get_option('dollie_color_secondary'));
	} else {
		$secondary_color = new Mexitek\PHPColors\Color('#f0a146');
	}


	$primary = $primary_color->getHsl();

	//Take converted HEX values and format them correctly. Encount for trailing zeros and round numbers (50, 40 etc)
	$P_H = round($primary['H'], 0);
	$P_S = substr($primary['S'], 0, 4);
	$P_L = substr($primary['L'], 0, 4);

	$secondary = $secondary_color->getHsl();

	//Take converted HEX values and format them correctly. Encount for trailing zeros and round numbers (50, 40 etc)
	$S_H = round($secondary['H'], 0);
	$S_S = substr($secondary['S'], 0, 4);
	$S_L = substr($secondary['L'], 0, 4);


	echo '<style>
	:root {
	--primary-color: ' . $P_H . ', ' . substr($P_S, 2) . '%;
	--primary-color-l: ' . substr($P_L, 2) . '%;
	--primary: hsl(var(--primary-color), calc(var(--primary-color-l) * 1));
	--primary-100: hsl(var(--primary-color), calc(var(--primary-color-l) * 1.85));
	--primary-200: hsl(var(--primary-color), calc(var(--primary-color-l) * 1.65));
	--primary-300: hsl(var(--primary-color), calc(var(--primary-color-l) * 1.45));
	--primary-400: hsl(var(--primary-color), calc(var(--primary-color-l) * 1.25));
	--primary-500: hsl(var(--primary-color), calc(var(--primary-color-l) * 0.9));
	--primary-600: hsl(var(--primary-color), calc(var(--primary-color-l) * 0.8));
	--primary-700: hsl(var(--primary-color), calc(var(--primary-color-l) * 0.6));
	--primary-800: hsl(var(--primary-color), calc(var(--primary-color-l) * 0.4));
	--primary-900: hsl(var(--primary-color), calc(var(--primary-color-l) * 0.2));
	--secondary-color: ' . $S_H . ', ' . substr($S_S, 2) . '%;
	--secondary-color-l: ' . substr($S_L, 2) . '%;
	--secondary: hsl(var(--secondary-color), calc(var(--secondary-color-l) * 1));
	--secondary-100: hsl(var(--secondary-color), calc(var(--secondary-color-l) * 1.85));
	--secondary-200: hsl(var(--secondary-color), calc(var(--secondary-color-l) * 1.65));
	--secondary-300: hsl(var(--secondary-color), calc(var(--secondary-color-l) * 1.45));
	--secondary-400: hsl(var(--secondary-color), calc(var(--secondary-color-l) * 1.25));
	--secondary-500: hsl(var(--secondary-color), calc(var(--secondary-color-l) * 0.9));
	--secondary-600: hsl(var(--secondary-color), calc(var(--secondary-color-l) * 0.8));
	--secondary-700: hsl(var(--secondary-color), calc(var(--secondary-color-l) * 0.6));
	--secondary-800: hsl(var(--secondary-color), calc(var(--secondary-color-l) * 0.4));
	--secondary-900: hsl(var(--secondary-color), calc(var(--secondary-color-l) * 0.2));
	}
	</style>';

?>
<?php
} // end dol_customizer_css

add_action('wp_head', 'dol_customizer_css');

//

/**
 * Add filterable classes for styling widgets
 *
 * @since 1.0.0
 *
 */
function dol_widgets_layout()
{
	echo apply_filters('dol_update_widget_classes', 'dol-bg-white dol-rounded-md dol-widget-custom dark:dol-bg-gray-800');
}

add_action('dol_add_widget_classes', 'dol_widgets_layout');

function dol_register_nav_menu()
{
	register_nav_menus(array(
		'dol_top_menu' => __('Dollie - Primary Menu', 'text_domain'),
		'dol_notifications_menu'  => __('Dollie - Notifications Menu', 'text_domain'),
	));
}
add_action('after_setup_theme', 'dol_register_nav_menu', 0);




