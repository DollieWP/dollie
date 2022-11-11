<?php
/**
 * This file represents an example of the code that themes would use to register
 * the required plugins.
 *
 * It is expected that theme authors would copy and paste this code into their
 * functions.php file, and amend to suit.
 *
 * @see http://tgmpluginactivation.com/configuration/ for detailed documentation.
 *
 * @package    TGM-Plugin-Activation
 * @subpackage Example
 * @version    2.6.1 for plugin Dollie
 * @author     Thomas Griffin, Gary Jones, Juliette Reinders Folmer
 * @copyright  Copyright (c) 2011, Thomas Griffin
 * @license    http://opensource.org/licenses/gpl-2.0.php GPL v2 or later
 * @link       https://github.com/TGMPA/TGM-Plugin-Activation
 */

/**
 * Include the TGM_Plugin_Activation class.
 *
 * Depending on your implementation, you may want to change the include call:
 *
 * Parent Theme:
 * require_once get_template_directory() . '/path/to/class-tgm-plugin-activation.php';
 *
 * Child Theme:
 * require_once get_stylesheet_directory() . '/path/to/class-tgm-plugin-activation.php';
 *
 * Plugin:
 * require_once dirname( __FILE__ ) . '/path/to/class-tgm-plugin-activation.php';
 */
require_once dirname( __FILE__ ) . '/class-tgm-plugin-activation.php';

add_action( 'tgmpa_register', 'dollie_register_required_plugins' );

/**
 * Register the required plugins for Dollie.
 *
 * In this example, we register five plugins:
 * - one included with the TGMPA library
 * - two from an external source, one from an arbitrary source, one from a GitHub repository
 * - two from the .org repo, where one demonstrates the use of the `is_callable` argument
 *
 * The variables passed to the `tgmpa()` function should be:
 * - an array of plugin arrays;
 * - optionally a configuration array.
 * If you are not changing anything in the configuration array, you can remove the array and remove the
 * variable from the function call: `tgmpa( $plugins );`.
 * In that case, the TGMPA default settings will be used.
 *
 * This function is hooked into `tgmpa_register`, which is fired on the WP `init` action on priority 10.
 */
function dollie_register_required_plugins() {

	/*
	 * Array of plugin arrays. Required keys are name and slug.
	 * If the source is NOT from the .org repo, then source is also required.
	 */
	$plugins = [
		[
			'name'             => 'Advanced Custom Fields Pro',
			// The plugin name.
			'slug'             => 'advanced-custom-fields-pro',
			// The plugin slug (typically the folder name).
			'required'         => true,
			// If false, the plugin is only 'recommended' instead of required.
			'version'          => '3.0.10',
			// E.g. 1.0.0. If set, the active plugin must be this version or higher. If the plugin version is higher than the plugin version installed, the user will be notified to update the plugin.
			'force_activation' => false,
			// If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
			'source'           => 'https://manager.getdollie.com/releases/?action=download&slug=advanced-custom-fields-pro',
		],

		// This is an example of how to include a plugin from the WordPress Plugin Repository.
		[
			'name'     => 'User Switching (Allows you to login on behalf of customers)',
			'slug'     => 'user-switching',
			'required' => false,
		],

		// This is an example of the use of 'is_callable' functionality. A user could - for instance -
		// have WPSEO installed *or* WPSEO Premium. The slug would in that last case be different, i.e.
		// 'wordpress-seo-premium'.
		// By setting 'is_callable' to either a function from that plugin or a class method
		// `array( 'class', 'method' )` similar to how you hook in to actions and filters, TGMPA can still

	];

	$plugins = apply_filters( 'dollie/required_plugins', $plugins );

	/*
	 * Array of configuration settings. Amend each line as needed.
	 *
	 * TGMPA will start providing localized text strings soon. If you already have translations of our standard
	 * strings available, please help us make TGMPA even better by giving us access to these translations or by
	 * sending in a pull-request with .po file(s) with the translations.
	 *
	 * Only uncomment the strings in the config array if you want to customize the strings.
	 */
	$config = [
		'id'           => 'dollie',
		// Unique ID for hashing notices for multiple instances of TGMPA.
		'default_path' => '',
		// Default absolute path to bundled plugins.
		'menu'         => 'dollie-install-plugins',
		// Menu slug.
		'parent_slug'  => 'plugins.php',
		// Parent menu slug.
		'capability'   => 'manage_options',
		// Capability needed to view plugin install page, should be a capability associated with the parent menu used.
		'has_notices'  => true,
		// Show admin notices or not.
		'dismissable'  => true,
		// If false, a user cannot dismiss the nag message.
		'dismiss_msg'  => '',
		// If 'dismissable' is false, this message will be output at top of nag.
		'is_automatic' => false,
		// Automatically activate plugins after installation or not.
		'message'      => '<div id="setting-error-tgmpa" class="dollie-notice">
						<h3>
							Recommended Dollie Plugin Suite
						</h3>
						<p>
							Dollie relies on some plugins being installed and activated inside your WordPress installation. Below you can easily install them. Additionally there are some recommended plugins that you might want to install but are not mandatory.
						</p>
					</div>',
		// Message to output right before the plugins table.

		'strings'      => [
			'page_title'                      => __( 'Install Dollie Plugin Suite', 'dollie' ),
			'menu_title'                      => __( 'Dollie Plugins', 'dollie' ),
			'installing'                      => __( 'Installing Plugin: %s', 'dollie' ),
			'updating'                        => __( 'Updating Plugin: %s', 'dollie' ),
			'oops'                            => __( 'Something went wrong with the plugin API.', 'dollie' ),
			'notice_can_install_required'     => _n_noop(
				'Dollie requires the following plugin: %1$s.',
				'Dollie requires the following plugins: %1$s.',
				'dollie'
			),
			'notice_can_install_recommended'  => _n_noop(
				'Dollie recommends the following plugin: %1$s.',
				'Dollie recommends the following plugins: %1$s.',
				'dollie'
			),
			'notice_ask_to_update'            => _n_noop(
				'The following plugin needs to be updated to its latest version to ensure maximum compatibility with Dollie: %1$s.',
				'The following plugins need to be updated to their latest version to ensure maximum compatibility with Dollie: %1$s.',
				'dollie'
			),
			'notice_ask_to_update_maybe'      => _n_noop(
				'Dollie - There is an update available for: %1$s.',
				'Dollie - There are updates available for the following plugins: %1$s.',
				'dollie'
			),
			'notice_can_activate_required'    => _n_noop(
				'Dollie - The following required plugin is currently inactive: %1$s.',
				'Dollie - The following required plugins are currently inactive: %1$s.',
				'dollie'
			),
			'notice_can_activate_recommended' => _n_noop(
				'Dollie - The following recommended plugin is currently inactive: %1$s.',
				'Dollie - The following recommended plugins are currently inactive: %1$s.',
				'dollie'
			),
			'install_link'                    => _n_noop(
				'Begin installing plugin',
				'Begin installing plugins',
				'dollie'
			),
			'update_link'                     => _n_noop(
				'Begin updating plugin',
				'Begin updating plugins',
				'dollie'
			),
			'activate_link'                   => _n_noop(
				'Begin activating plugin',
				'Begin activating plugins',
				'dollie'
			),
			'return'                          => __( 'Return to Dollie Plugin Installer', 'dollie' ),
			'plugin_activated'                => __( 'Plugin activated successfully.', 'dollie' ),
			'activated_successfully'          => __( 'The following plugin was activated successfully:', 'dollie' ),
			'plugin_already_active'           => __( 'No action taken. Plugin %1$s was already active.', 'dollie' ),
			'plugin_needs_higher_version'     => __( 'Plugin not activated. A higher version of %s is needed for Dollie. Please update the plugin.', 'dollie' ),
			'complete'                        => __( 'All plugins installed and activated successfully. %1$s', 'dollie' ),
			'dismiss'                         => __( 'Dismiss this notice', 'dollie' ),
			'notice_cannot_install_activate'  => __( 'Dollie - There are one or more required or recommended plugins to install, update or activate.', 'dollie' ),
			'contact_admin'                   => __( 'Please contact the administrator of this site for help.', 'dollie' ),

			'nag_type'                        => 'notice-info',
			// Determines admin notice type - can only be one of the typical WP notice classes, such as 'updated', 'update-nag', 'notice-warning', 'notice-info' or 'error'. Some of which may not work as expected in older WP versions.
		],
	];

	tgmpa( $plugins, $config );
}
