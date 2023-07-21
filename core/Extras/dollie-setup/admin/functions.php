<?php
/**
 * DOLLIE_SETUP Admin Common Functions
 *
 * @since 0.3
 *
 * @package Dollie_Setup
 * @subpackage Adminstration
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function dollie_is_wizard_complete() {
	if ( isset( $_GET['dollie-show-wizard'] ) ) {
		return false;
	}

	return ! empty( get_option( 'dollie_imported_templates', [] ) ) || get_option( 'options_wpd_welcome_wizard' );
}

/**
 * Check to see if DOLLIE_SETUP is correctly setup.
 *
 * @return bool
 * @uses dollie_setup_get_installed_revision_date() Get the DOLLIE_SETUP revision date from the DB
 * @uses dollie_setup_is_upgraded() Check to see if DOLLIE_SETUP just upgraded
 * @uses dollie_setup_is_bp_maintenance_mode() Check to see if BuddyPress is in maintenance mode
 * @since 0.3
 *
 */
function dollie_setup_is_setup() {
	// we haven't saved the revision date into the DB yet
	if ( ! dollie_setup_get_installed_revision_date() ) {
		return false;
	}

	// DOLLIE_SETUP is installed, but we just upgraded to a
	// newer version of DOLLIE_SETUP
	if ( dollie_setup_is_upgraded() ) {
		return false;
	}

	if ( dollie_setup_get_setup_step() ) {
		return false;
	}

	return true;
}

/**
 * Check to see if DOLLIE_SETUP has just upgraded.
 *
 * @return bool
 * @uses dollie_setup_get_installed_revision_date() Gets the DOLLIE_SETUP revision date from the DB
 * @uses dollie_setup_get_current_revision_date() Gets the current DOLLIE_SETUP revision date from Dollie_Setup::setup_globals()
 * @since 0.3
 *
 */
function dollie_setup_is_upgraded() {
	if ( dollie_setup_get_installed_revision_date() && ( dollie_setup_get_current_revision_date() > dollie_setup_get_installed_revision_date() ) ) {
		return true;
	}

	return false;
}


/**
 * Outputs the DOLLIE_SETUP version
 *
 * @since 0.3
 *
 * @uses dollie_setup_get_version() To get the DOLLIE_SETUP version
 */
function dollie_setup_version() {
	echo dollie_setup_get_version();
}

/**
 * Return the DOLLIE_SETUP version
 *
 * @return string The DOLLIE_SETUP version
 * @since 0.3
 *
 */
function dollie_setup_get_version() {
	return dollie_setup()->version;
}

/**
 * Bumps the DOLLIE_SETUP revision date in the DB
 *
 * @return mixed String of date on success. Boolean false on failure
 * @since 0.3
 *
 */
function dollie_setup_bump_revision_date() {
	update_site_option( '_dollie_setup_revision_date', dollie_setup()->revision_date );
}

/**
 * Get the current DOLLIE_SETUP setup step.
 *
 * This should only be used if {@link dollie_setup_is_setup()} returns false.
 *
 * @return string The current DOLLIE_SETUP setup step.
 * @uses dollie_setup_is_bp_maintenance_mode() Check to see if BuddyPress is in maintenance mode
 * @since 0.3
 *
 */
function dollie_setup_get_setup_step() {
	$step = '';

	// No package.
	if ( ! dollie_setup_get_current_package_id() ) {
		return 'no-package';
		// Plugin updates available.
	}

	return $step;
}

/**
 * Get a specific admin property for use with DOLLIE_SETUP.
 *
 * @param string $prop Prop to fetch. Either 'menu' or 'url'.
 * @param mixed $arg Function argument passed for use.
 *
 * @return string
 * @since 1.1.0
 *
 */
function dollie_setup_admin_prop( $prop = '', $arg = '' ) {
	$retval = '';

	if ( 'menu' === $prop ) {
		$retval = is_network_admin() ? 'network_admin_menu' : 'admin_menu';
	} elseif ( 'url' === $prop ) {
		$retval = self_admin_url( $arg );
		if ( ! dollie_setup_is_main_site() ) {
			$retval = network_admin_url( $arg );
		}
	}

	return $retval;
}

/**
 * Wrapper for wp_get_theme() to account for main site ID.
 *
 * @param string|null $stylesheet Directory name for the theme. Optional. Defaults to current theme.
 *
 * @since 1.1.0
 *
 */
function dollie_setup_get_theme( $stylesheet = '' ) {
	if ( ! dollie_setup_is_main_site() ) {
		switch_to_blog( dollie_setup_get_main_site_id() );
		$switched = true;
	}

	$theme = wp_get_theme( $stylesheet );

	if ( ! empty( $switched ) ) {
		restore_current_blog();
	}

	return $theme;
}

function dollie_admin_get_title() {
	if (isset($_GET['post_type']) && $_GET['post_type'] === 'container' && isset($_GET['blueprint'] ) ) {
		return __('Blueprints', 'dollie');
	}
	return get_admin_page_title();
}


/** TEMPLATE *************************************************************/

/**
 * Locate the highest priority DOLLIE_SETUP admin template file that exists.
 *
 * Tries to see if a registered DOLLIE_SETUP package has a template file.  If not,
 * fall back to the 'base' template.  Similar to {@link locate_template()}.
 *
 * @param string|array $template_names Template file(s) to search for, in order.
 * @param string $package_id The DOLLIE_SETUP package to grab the template for.
 * @param bool $load If true the template file will be loaded if it is found.
 * @param bool $require_once Whether to require_once or require. Default true. Has no effect if $load is false.
 *
 * @return string The template filename if one is located.
 * @since 1.1.0
 *
 */
function dollie_setup_locate_template( $template_names, $package_id = '', $load = false, $require_once = true ) {
	$located = '';
	foreach ( (array) $template_names as $template_name ) {
		if ( ! $template_name ) {
			continue;
		}

		$template_path = dollie_setup_get_package_prop( 'template_path', $package_id );
		if ( ! empty( $template_path ) && file_exists( trailingslashit( $template_path ) . $template_name ) ) {
			$located = trailingslashit( $template_path ) . $template_name;
			break;

		} elseif ( file_exists( DOLLIE_SETUP_PLUGIN_DIR . 'admin/templates/base/' . $template_name ) ) {
			$located = DOLLIE_SETUP_PLUGIN_DIR . 'admin/templates/base/' . $template_name;
			break;
		}
	}

	if ( $load && '' != $located ) {
		load_template( $located, $require_once );
	}

	return $located;
}

/**
 * Load a DOLLIE_SETUP admin template part.
 *
 * Basically, almost the same as {@link get_template_part()}.
 *
 * @param string $slug The slug name for the generic template.
 * @param string $package_id Optional. The DOLLIE_SETUP package to grab the template for. Defaults to current
 *                           package if available.
 *
 * @since 1.1.0
 *
 */
function dollie_setup_get_template_part( $slug, $package_id = '' ) {
	$templates   = array();
	$templates[] = "{$slug}.php";

	/**
	 * Fires before the specified template part file is loaded.
	 *
	 * The dynamic portion of the hook name, `$slug`, refers to the slug name
	 * for the generic template part.
	 *
	 * @param string $slug The slug name for the generic template.
	 * @param string $package_id The DOLLIE_SETUP package to grab the template for.
	 *
	 * @since 1.1.0
	 *
	 */
	do_action( 'dollie_setup_get_template_part', $slug, $package_id );

	dollie_setup_locate_template( $templates, $package_id, true, false );

	/**
	 * Fires after the specified template part file is loaded.
	 *
	 * The dynamic portion of the hook name, `$slug`, refers to the slug name
	 * for the generic template part.
	 *
	 * @param string $slug The slug name for the generic template.
	 * @param string $package_id The DOLLIE_SETUP package to grab the template for.
	 *
	 * @since 1.1.0
	 *
	 */
	do_action( 'dollie_setup_after_get_template_part', $slug, $package_id );
}

/**
 * Template tag to output CSS classes meant for the welcome panel admin block.
 *
 * @since 1.1.0
 */
function dollie_setup_welcome_panel_classes() {
	// Default class for our welcome panel container.
	$classes = 'welcome-panel';

	// Get our user's welcome panel setting.
	$option = get_user_meta( get_current_user_id(), 'show_dollie_setup_welcome_panel', true );

	// If welcome panel option isn't set, set it to "1" to show the panel by default
	if ( $option === '' ) {
		$option = 1;
	}

	// This sets the CSS class needed to hide the welcome panel if needed.
	if ( ! (int) $option ) {
		$classes .= ' hidden';
	}

	echo esc_attr( $classes );
}
