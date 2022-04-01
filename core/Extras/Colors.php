<?php

use Mexitek\PHPColors\Color;

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
		[
			'title'       => __('Dollie', 'dollie'),
			'description' => __('Set colors for Dollie widgets', 'dollie'),
			'priority'    => 9,
		]
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
		[
			'type'              => 'option',
			'sanitize_callback' => 'dol_sanitize_hex_color',
			// 'transport' => 'postMessage'
		]
	);

	$wp_customize->add_setting(
		// $id
		'dollie_color_secondary',
		// $args
		[
			'type'              => 'option',
			'sanitize_callback' => 'dol_sanitize_hex_color',
			// 'transport' => 'postMessage'
		]
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
			[
				'settings'    => 'dollie_color_primary',
				'section'     => 'dollie_colors_section',
				'label'       => __('Primary Color', 'dollie'),
				'description' => __('Select the primary color used across Dollie.', 'dollie'),
			]
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			// $wp_customize object
			$wp_customize,
			// $id
			'dollie_color_secondary_control',
			// $args
			[
				'settings'    => 'dollie_color_secondary',
				'section'     => 'dollie_colors_section',
				'label'       => __('Secondary Color', 'dollie'),
				'description' => __('Select the secondary color used across Dollie', 'dollie'),
			]
		)
	);
}


/**
 * Writes the Header Background related controls' values out to the 'head' element of the document
 * by reading the value(s) from the theme mod value in the options table.
 */
function dol_customizer_css()
{
	if (!class_exists(Color::class)) {
		return;
	}

	$primary_option   = get_option('dollie_color_primary');
	$secondary_option = get_option('dollie_color_secondary');

	if ($primary_option) {
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

	// Take converted HEX values and format them correctly. Encount for trailing zeros and round numbers (50, 40 etc)
	$P_H = round($primary['H'], 0);
	$P_S = substr($primary['S'], 0, 4);
	$P_L = substr($primary['L'], 0, 4);

	$secondary = $secondary_color->getHsl();

	// Take converted HEX values and format them correctly. Encount for trailing zeros and round numbers (50, 40 etc)
	$S_H = round($secondary['H'], 0);
	$S_S = substr($secondary['S'], 0, 4);
	$S_L = substr($secondary['L'], 0, 4);

	echo '<style>
	:root {
	--d-primary-color: ' . $P_H . ', ' . substr($P_S, 2) . '%;
	--d-primary-color-l: ' . substr($P_L, 2) . '%;
	--d-primary: hsl(var(--d-primary-color), calc(var(--d-primary-color-l) * 1));
	--d-primary-100: hsl(var(--d-primary-color), calc(var(--d-primary-color-l) * 1.85));
	--d-primary-200: hsl(var(--d-primary-color), calc(var(--d-primary-color-l) * 1.65));
	--d-primary-300: hsl(var(--d-primary-color), calc(var(--d-primary-color-l) * 1.45));
	--d-primary-400: hsl(var(--d-primary-color), calc(var(--d-primary-color-l) * 1.25));
	--d-primary-500: hsl(var(--d-primary-color), calc(var(--d-primary-color-l) * 0.9));
	--d-primary-600: hsl(var(--d-primary-color), calc(var(--d-primary-color-l) * 0.8));
	--d-primary-700: hsl(var(--d-primary-color), calc(var(--d-primary-color-l) * 0.6));
	--d-primary-800: hsl(var(--d-primary-color), calc(var(--d-primary-color-l) * 0.4));
	--d-primary-900: hsl(var(--d-primary-color), calc(var(--d-primary-color-l) * 0.2));
	--d-secondary-color: ' . $S_H . ', ' . substr($S_S, 2) . '%;
	--d-secondary-color-l: ' . substr($S_L, 2) . '%;
	--d-secondary: hsl(var(--d-secondary-color), calc(var(--d-secondary-color-l) * 1));
	--d-secondary-100: hsl(var(--d-secondary-color), calc(var(--d-secondary-color-l) * 1.85));
	--d-secondary-200: hsl(var(--d-secondary-color), calc(var(--d-secondary-color-l) * 1.65));
	--d-secondary-300: hsl(var(--d-secondary-color), calc(var(--d-secondary-color-l) * 1.45));
	--d-secondary-400: hsl(var(--d-secondary-color), calc(var(--d-secondary-color-l) * 1.25));
	--d-secondary-500: hsl(var(--d-secondary-color), calc(var(--d-secondary-color-l) * 0.9));
	--d-secondary-600: hsl(var(--d-secondary-color), calc(var(--d-secondary-color-l) * 0.8));
	--d-secondary-700: hsl(var(--d-secondary-color), calc(var(--d-secondary-color-l) * 0.6));
	--d-secondary-800: hsl(var(--d-secondary-color), calc(var(--d-secondary-color-l) * 0.4));
	--d-secondary-900: hsl(var(--d-secondary-color), calc(var(--d-secondary-color-l) * 0.2));
	}
	</style>';

?>
<?php
} // end dol_customizer_css

add_action('wp_head', 'dol_customizer_css');
add_action('admin_head', 'dol_customizer_css');


/**
 * Add filterable classes for styling widgets
 *
 * @since 1.0.0
 */
function dol_widgets_layout()
{
	echo apply_filters('dol_update_widget_classes', 'dol-dol-bg-white dol-shadow dol-rounded-md dol-widget-custom');
}

add_action('dol_add_widget_classes', 'dol_widgets_layout');

function dol_register_nav_menu()
{
	register_nav_menus(
		[
			'dol_top_menu'           => __('Dollie - Primary Menu', 'text_domain'),
			'dol_notifications_menu' => __('Dollie - Notifications Menu', 'text_domain'),
		]
	);
}

add_action('after_setup_theme', 'dol_register_nav_menu', 0);

/**
 * Enqueue script for custom customize control.
 */
function custom_customize_enqueue()
{
	wp_enqueue_script(
		'custom-customize',
		DOLLIE_ASSETS_URL . '/js/customize.js',
		[
			'jquery',
			'customize-controls',
		],
		false,
		true
	);
}

add_action('customize_controls_enqueue_scripts', 'custom_customize_enqueue');



function dol_theme_body_start()
{
?>
	<div class="dol-theme-wrap" data-theme="dol_theme_base">

		<!-- <a href="#" class="dol-btn dol-btn-primary dol-text-white">Testing our buttons</a>
		<div class="dol-overflow-x-auto">
			<div class="dol-whitespace-nowrap">
				<table>
					<thead>
						<tr>
							<th></th>
							<th>Color name + description</th>
							<th>Required or optional for themes</th>
							<th>Example use</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><span class="dol-badge dol-relative dol-top-4 dol-bg-primary"></span></td>
							<td><span class="dol-font-mono dol-font-bold">primary</span> <br> <span class="dol-text-xs dol-opacity-60">Primary color</span></td>
							<td><span class="dol-badge dol-badge-sm">required</span></td>
							<td><span class="dol-font-mono dol-text-xs dol-opacity-60">Class name: <code>dol-bg-primary</code></span> <br> <span class="dol-text-xs dol-opacity-60 dol-font-mono">CSS variable: <code>hsl(--p)</code></span></td>
						</tr>
						<tr>
							<td><span class="dol-badge dol-relative dol-top-4 dol-bg-primary-focus"></span></td>
							<td><span class="dol-font-mono dol-font-bold">primary-focus</span> <br> <span class="dol-text-xs dol-opacity-60">Primary color when focused</span></td>
							<td><span class="dol-badge dol-badge-sm dol-badge-ghost">optional</span> <br> <span class="dol-text-xs dol-opacity-60">Will be a darker tone of primary if not specified</span></td>
							<td><span class="dol-font-mono dol-text-xs dol-opacity-60">Class name: <code>dol-bg-primary-focus</code></span> <br> <span class="dol-text-xs dol-opacity-60 dol-font-mono">CSS variable: <code>hsl(--pf)</code></span></td>
						</tr>
						<tr>
							<td><span class="dol-badge dol-relative dol-top-4 dol-bg-primary-content"></span></td>
							<td><span class="dol-font-mono dol-font-bold">primary-content</span> <br> <span class="dol-text-xs dol-opacity-60">Foreground content color to use on primary color</span></td>
							<td><span class="dol-badge dol-badge-sm dol-badge-ghost">optional</span> <br> <span class="dol-text-xs dol-opacity-60">Will be a readable tone of primary if not specified</span></td>
							<td><span class="dol-font-mono dol-text-xs dol-opacity-60">Class name: <code>dol-bg-primary-content</code></span> <br> <span class="dol-text-xs dol-opacity-60 dol-font-mono">CSS variable: <code>hsl(--pc)</code></span></td>
						</tr>
						<tr>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
						</tr>
						<tr>
							<td><span class="dol-badge dol-relative dol-top-4 dol-bg-secondary"></span></td>
							<td><span class="dol-font-mono dol-font-bold">secondary</span> <br> <span class="dol-text-xs dol-opacity-60">Secondary color</span></td>
							<td><span class="dol-badge dol-badge-sm">required</span></td>
							<td><span class="dol-font-mono dol-text-xs dol-opacity-60">Class name: <code>dol-bg-secondary</code></span> <br> <span class="dol-text-xs dol-opacity-60 dol-font-mono">CSS variable: <code>hsl(--s)</code></span></td>
						</tr>
						<tr>
							<td><span class="dol-badge dol-relative dol-top-4 dol-bg-secondary-focus"></span></td>
							<td><span class="dol-font-mono dol-font-bold">secondary-focus</span> <br> <span class="dol-text-xs dol-opacity-60">Secondary color when focused</span></td>
							<td><span class="dol-badge dol-badge-sm dol-badge-ghost">optional</span> <br> <span class="dol-text-xs dol-opacity-60">Will be a darker tone of secondary if not specified</span></td>
							<td><span class="dol-font-mono dol-text-xs dol-opacity-60">Class name: <code>dol-bg-secondary-focus</code></span> <br> <span class="dol-text-xs dol-opacity-60 dol-font-mono">CSS variable: <code>hsl(--sf)</code></span></td>
						</tr>
						<tr>
							<td><span class="dol-badge dol-relative dol-top-4 dol-bg-secondary-content"></span></td>
							<td><span class="dol-font-mono dol-font-bold">secondary-content</span> <br> <span class="dol-text-xs dol-opacity-60">Foreground content color to use on secondary color</span></td>
							<td><span class="dol-badge dol-badge-sm dol-badge-ghost">optional</span> <br> <span class="dol-text-xs dol-opacity-60">Will be a readable tone of secondary if not specified</span></td>
							<td><span class="dol-font-mono dol-text-xs dol-opacity-60">Class name: <code>dol-bg-secondary-content</code></span> <br> <span class="dol-text-xs dol-opacity-60 dol-font-mono">CSS variable: <code>hsl(--sc)</code></span></td>
						</tr>
						<tr>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
						</tr>
						<tr>
							<td><span class="dol-badge dol-relative dol-top-4 dol-bg-accent"></span></td>
							<td><span class="dol-font-mono dol-font-bold">accent</span> <br> <span class="dol-text-xs dol-opacity-60">Accent color</span></td>
							<td><span class="dol-badge dol-badge-sm">required</span></td>
							<td><span class="dol-font-mono dol-text-xs dol-opacity-60">Class name: <code>dol-bg-accent</code></span> <br> <span class="dol-text-xs dol-opacity-60 dol-font-mono">CSS variable: <code>hsl(--a)</code></span></td>
						</tr>
						<tr>
							<td><span class="dol-badge dol-relative dol-top-4 dol-bg-accent-focus"></span></td>
							<td><span class="dol-font-mono dol-font-bold">accent-focus</span> <br> <span class="dol-text-xs dol-opacity-60">Accent color when focused</span></td>
							<td><span class="dol-badge dol-badge-sm dol-badge-ghost">optional</span> <br> <span class="dol-text-xs dol-opacity-60">Will be a darker tone of accent if not specified</span></td>
							<td><span class="dol-font-mono dol-text-xs dol-opacity-60">Class name: <code>dol-bg-accent-focus</code></span> <br> <span class="dol-text-xs dol-opacity-60 dol-font-mono">CSS variable: <code>hsl(--af)</code></span></td>
						</tr>
						<tr>
							<td><span class="dol-badge dol-relative dol-top-4 dol-bg-accent-content"></span></td>
							<td><span class="dol-font-mono dol-font-bold">accent-content</span> <br> <span class="dol-text-xs dol-opacity-60">Foreground content color to use on accent color</span></td>
							<td><span class="dol-badge dol-badge-sm dol-badge-ghost">optional</span> <br> <span class="dol-text-xs dol-opacity-60">Will be a readable tone of accent if not specified</span></td>
							<td><span class="dol-font-mono dol-text-xs dol-opacity-60">Class name: <code>dol-bg-accent-content</code></span> <br> <span class="dol-text-xs dol-opacity-60 dol-font-mono">CSS variable: <code>hsl(--ac)</code></span></td>
						</tr>
						<tr>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
						</tr>
						<tr>
							<td><span class="dol-badge dol-relative dol-top-4 dol-bg-neutral"></span></td>
							<td><span class="dol-font-mono dol-font-bold">neutral</span> <br> <span class="dol-text-xs dol-opacity-60">Neutral color</span></td>
							<td><span class="dol-badge dol-badge-sm">required</span></td>
							<td><span class="dol-font-mono dol-text-xs dol-opacity-60">Class name: <code>dol-bg-neutral</code></span> <br> <span class="dol-text-xs dol-opacity-60 dol-font-mono">CSS variable: <code>hsl(--n)</code></span></td>
						</tr>
						<tr>
							<td><span class="dol-badge dol-relative dol-top-4 dol-bg-neutral-focus"></span></td>
							<td><span class="dol-font-mono dol-font-bold">neutral-focus</span> <br> <span class="dol-text-xs dol-opacity-60">Neutral color when focused</span></td>
							<td><span class="dol-badge dol-badge-sm dol-badge-ghost">optional</span> <br> <span class="dol-text-xs dol-opacity-60">Will be a darker tone of neutral if not specified</span></td>
							<td><span class="dol-font-mono dol-text-xs dol-opacity-60">Class name: <code>dol-bg-neutral-focus</code></span> <br> <span class="dol-text-xs dol-opacity-60 dol-font-mono">CSS variable: <code>hsl(--nf)</code></span></td>
						</tr>
						<tr>
							<td><span class="dol-badge dol-relative dol-top-4 dol-bg-neutral-content"></span></td>
							<td><span class="dol-font-mono dol-font-bold">neutral-content</span> <br> <span class="dol-text-xs dol-opacity-60">Foreground content color to use on neutral color</span></td>
							<td><span class="dol-badge dol-badge-sm dol-badge-ghost">optional</span> <br> <span class="dol-text-xs dol-opacity-60">Will be a readable tone of neutral if not specified</span></td>
							<td><span class="dol-font-mono dol-text-xs dol-opacity-60">Class name: <code>dol-bg-neutral-content</code></span> <br> <span class="dol-text-xs dol-opacity-60 dol-font-mono">CSS variable: <code>hsl(--nc)</code></span></td>
						</tr>
						<tr>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
						</tr>
						<tr>
							<td><span class="dol-badge dol-relative dol-top-4 dol-bg-base-100"></span></td>
							<td><span class="dol-font-mono dol-font-bold">base-100</span> <br> <span class="dol-text-xs dol-opacity-60">Base color of page, used for blank backgrounds</span></td>
							<td><span class="dol-badge dol-badge-sm">required</span></td>
							<td><span class="dol-font-mono dol-text-xs dol-opacity-60">Class name: <code>dol-bg-base-100</code></span> <br> <span class="dol-text-xs dol-opacity-60 dol-font-mono">CSS variable: <code>hsl(--b1)</code></span></td>
						</tr>
						<tr>
							<td><span class="dol-badge dol-relative dol-top-4 dol-bg-base-200"></span></td>
							<td><span class="dol-font-mono dol-font-bold">base-200</span> <br> <span class="dol-text-xs dol-opacity-60">Base color, a little darker</span></td>
							<td><span class="dol-badge dol-badge-sm dol-badge-ghost">optional</span> <br> <span class="dol-text-xs dol-opacity-60">Will be a darker tone of base-100 if not specified</span></td>
							<td><span class="dol-font-mono dol-text-xs dol-opacity-60">Class name: <code>dol-bg-base-200</code></span> <br> <span class="dol-text-xs dol-opacity-60 dol-font-mono">CSS variable: <code>hsl(--b2)</code></span></td>
						</tr>
						<tr>
							<td><span class="dol-badge dol-relative dol-top-4 dol-bg-base-300"></span></td>
							<td><span class="dol-font-mono dol-font-bold">base-300</span> <br> <span class="dol-text-xs dol-opacity-60">Base color, even more darker</span></td>
							<td><span class="dol-badge dol-badge-sm dol-badge-ghost">optional</span> <br> <span class="dol-text-xs dol-opacity-60">Will be a darker tone of base-200 if not specified</span></td>
							<td><span class="dol-font-mono dol-text-xs dol-opacity-60">Class name: <code>dol-bg-base-300</code></span> <br> <span class="dol-text-xs dol-opacity-60 dol-font-mono">CSS variable: <code>hsl(--b3)</code></span></td>
						</tr>
						<tr>
							<td><span class="dol-badge dol-relative dol-top-4 dol-bg-base-content"></span></td>
							<td><span class="dol-font-mono dol-font-bold">base-content</span> <br> <span class="dol-text-xs dol-opacity-60">Foreground content color to use on base color</span></td>
							<td><span class="dol-badge dol-badge-sm dol-badge-ghost">optional</span> <br> <span class="dol-text-xs dol-opacity-60">Will be a readable tone of base-100 if not specified</span></td>
							<td><span class="dol-font-mono dol-text-xs dol-opacity-60">Class name: <code>dol-bg-base-content</code></span> <br> <span class="dol-text-xs dol-opacity-60 dol-font-mono">CSS variable: <code>hsl(--bc)</code></span></td>
						</tr>
						<tr>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
						</tr>
						<tr>
							<td><span class="dol-badge dol-relative dol-top-4 dol-bg-info"></span></td>
							<td><span class="dol-font-mono dol-font-bold">info</span> <br> <span class="dol-text-xs dol-opacity-60">Info color</span></td>
							<td><span class="dol-badge dol-badge-sm dol-badge-ghost">optional</span> <br> <span class="dol-text-xs dol-opacity-60">Will be a default blue color if not specified</span></td>
							<td><span class="dol-font-mono dol-text-xs dol-opacity-60">Class name: <code>dol-bg-info</code></span> <br> <span class="dol-text-xs dol-opacity-60 dol-font-mono">CSS variable: <code>hsl(--in)</code></span></td>
						</tr>
						<tr>
							<td><span class="dol-badge dol-relative dol-top-4 dol-bg-info-content"></span></td>
							<td><span class="dol-font-mono dol-font-bold">info-content</span> <br> <span class="dol-text-xs dol-opacity-60">Foreground content color to use on info color</span></td>
							<td><span class="dol-badge dol-badge-sm dol-badge-ghost">optional</span> <br> <span class="dol-text-xs dol-opacity-60">Will be a readable tone of info if not specified</span></td>
							<td><span class="dol-font-mono dol-text-xs dol-opacity-60">Class name: <code>dol-bg-info-content</code></span> <br> <span class="dol-text-xs dol-opacity-60 dol-font-mono">CSS variable: <code>hsl(--inc)</code></span></td>
						</tr>
						<tr>
							<td><span class="dol-badge dol-relative dol-top-4 dol-bg-success"></span></td>
							<td><span class="dol-font-mono dol-font-bold">success</span> <br> <span class="dol-text-xs dol-opacity-60">Success color</span></td>
							<td><span class="dol-badge dol-badge-sm dol-badge-ghost">optional</span> <br> <span class="dol-text-xs dol-opacity-60">Will be a default green color if not specified</span></td>
							<td><span class="dol-font-mono dol-text-xs dol-opacity-60">Class name: <code>dol-bg-success</code></span> <br> <span class="dol-text-xs dol-opacity-60 dol-font-mono">CSS variable: <code>hsl(--su)</code></span></td>
						</tr>
						<tr>
							<td><span class="dol-badge dol-relative dol-top-4 dol-bg-success-content"></span></td>
							<td><span class="dol-font-mono dol-font-bold">success-content</span> <br> <span class="dol-text-xs dol-opacity-60">Foreground content color to use on success color</span></td>
							<td><span class="dol-badge dol-badge-sm dol-badge-ghost">optional</span> <br> <span class="dol-text-xs dol-opacity-60">Will be a readable tone of success if not specified</span></td>
							<td><span class="dol-font-mono dol-text-xs dol-opacity-60">Class name: <code>dol-bg-success-content</code></span> <br> <span class="dol-text-xs dol-opacity-60 dol-font-mono">CSS variable: <code>hsl(--suc)</code></span></td>
						</tr>
						<tr>
							<td><span class="dol-badge dol-relative dol-top-4 dol-bg-warning"></span></td>
							<td><span class="dol-font-mono dol-font-bold">warning</span> <br> <span class="dol-text-xs dol-opacity-60">Warning color</span></td>
							<td><span class="dol-badge dol-badge-sm dol-badge-ghost">optional</span> <br> <span class="dol-text-xs dol-opacity-60">Will be a default orange color if not specified</span></td>
							<td><span class="dol-font-mono dol-text-xs dol-opacity-60">Class name: <code>dol-bg-warning</code></span> <br> <span class="dol-text-xs dol-opacity-60 dol-font-mono">CSS variable: <code>hsl(--wa)</code></span></td>
						</tr>
						<tr>
							<td><span class="dol-badge dol-relative dol-top-4 dol-bg-warning-content"></span></td>
							<td><span class="dol-font-mono dol-font-bold">warning-content</span> <br> <span class="dol-text-xs dol-opacity-60">Foreground content color to use on warning color</span></td>
							<td><span class="dol-badge dol-badge-sm dol-badge-ghost">optional</span> <br> <span class="dol-text-xs dol-opacity-60">Will be a readable tone of warning if not specified</span></td>
							<td><span class="dol-font-mono dol-text-xs dol-opacity-60">Class name: <code>dol-bg-warning-content</code></span> <br> <span class="dol-text-xs dol-opacity-60 dol-font-mono">CSS variable: <code>hsl(--wac)</code></span></td>
						</tr>
						<tr>
							<td><span class="dol-badge dol-relative dol-top-4 dol-bg-error"></span></td>
							<td><span class="dol-font-mono dol-font-bold">error</span> <br> <span class="dol-text-xs dol-opacity-60">Error color</span></td>
							<td><span class="dol-badge dol-badge-sm dol-badge-ghost">optional</span> <br> <span class="dol-text-xs dol-opacity-60">Will be a default red color if not specified</span></td>
							<td><span class="dol-font-mono dol-text-xs dol-opacity-60">Class name: <code>dol-bg-error</code></span> <br> <span class="dol-text-xs dol-opacity-60 dol-font-mono">CSS variable: <code>hsl(--er)</code></span></td>
						</tr>
						<tr>
							<td><span class="dol-badge dol-relative dol-top-4 dol-bg-error-content"></span></td>
							<td><span class="dol-font-mono dol-font-bold">error-content</span> <br> <span class="dol-text-xs dol-opacity-60">Foreground content color to use on error color</span></td>
							<td><span class="dol-badge dol-badge-sm dol-badge-ghost">optional</span> <br> <span class="dol-text-xs dol-opacity-60">Will be a readable tone of error if not specified</span></td>
							<td><span class="dol-font-mono dol-text-xs dol-opacity-60">Class name: <code>dol-bg-error-content</code></span> <br> <span class="dol-text-xs dol-opacity-60 dol-font-mono">CSS variable: <code>hsl(--erc)</code></span></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div> -->
	<?php }
add_action('wp_body_open', 'dol_theme_body_start');

function dol_theme_body_close()
{
	$container_meta = get_post_meta(get_queried_object_id());
	$site_meta = array_filter($container_meta, function ($key) {
		return strpos($key, 'wpd_') === 0;
	}, ARRAY_FILTER_USE_KEY);
	print("<pre>" . print_r($site_meta, true) . "</pre>");
	echo '</div>';
}
add_action('wp_footer', 'dol_theme_body_close');
