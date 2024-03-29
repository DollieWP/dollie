<?php
/**
 * Global functions needed throughout Dollie Setup.
 *
 * @since 1.0
 *
 * @package Dollie_Setup
 * @subpackage Functions
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Are we looking at the WordPress admin?
 *
 * Because AJAX requests are sent to wp-admin/admin-ajax.php, WordPress's
 * is_admin() function returns true for AJAX requests. This is misleading for
 * our purposes, so this function acts as a wrapper.
 *
 * @since 1.0
 * @return bool
 */
function dollie_setup_is_admin() {
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		// if we're in the admin area, WP_NETWORK_ADMIN will be defined.
		// admin-ajax.php does not define this so this is a good check to see
		// if we're in the admin area
		$is_admin = defined( 'WP_NETWORK_ADMIN' );
	} else {
		$is_admin = is_admin();
	}

	return $is_admin;
}

/**
 * Convenience function to fetch the main site ID for the WordPress site.
 *
 * If using BuddyPress and have changed the root blog ID, BP gets precedence
 * over multisite's $current_site->blog_id.
 *
 * @since 1.1.0
 *
 * @return int
 */
function dollie_setup_get_main_site_id() {
	/*
	 * BuddyPress has precedence; not using bp_get_root_blog_id() b/c BuddyPress
	 * might not be active by the time this function is called.
	 */
	if ( defined( 'BP_ROOT_BLOG' ) ) {
		/** This filter is documented in /wp-content/plugins/buddypress/bp-core/bp-core-functions.php */
		return (int) apply_filters( 'bp_get_root_blog_id', constant( 'BP_ROOT_BLOG' ) );
	}

	/*
	 * Multisite & Multi-network; see ms_load_current_site_and_network().
	 *
	 * This might not be necessary for 99% of installs out there, but better safe
	 * than sorry!
	 *
	 * WP 4.9.8 introduced get_main_site_id() which is network-aware and returns
	 * the correct ID for the main site of the current network.
	 */
	if ( is_multisite() ) {
		return (int) get_main_site_id();
	}

	// Fallback to 1 if we've reached this part.
	return 1;
}

/**
 * Convenience function to test if we are on the main site of a network.
 *
 * @since 1.1.2
 *
 * @return bool True if main site, false otherwise.
 */
function dollie_setup_is_main_site() {
	// Always true on single site instances.
	if ( ! is_multisite() ) {
		return true;
	}

	// Check against current site.
	$site_id = (int) get_current_blog_id();
	if ( $site_id === dollie_setup_get_main_site_id() ) {
		return true;
	}

	// Can't be the main site.
	return false;
}

/**
 * Returns the current DOLLIE_SETUP revision date as set in
 * {@link Dollie_Setup::setup_globals()}.
 *
 * @since 0.3
 *
 * @return int The current DOLLIE_SETUP revision date as a unix timestamp.
 */
function dollie_setup_get_current_revision_date() {
	return strtotime( dollie_setup()->revision_date );
}

/**
 * Returns the DOLLIE_SETUP revision date from the current DOLLIE_SETUP install.
 *
 * @since 0.3
 *
 * @return mixed Integer of the installed DOLLIE_SETUP unix timestamp on success.  Boolean false on failure.
 */
function dollie_setup_get_installed_revision_date() {
	return strtotime( get_site_option( '_dollie_setup_revision_date' ) );
}

/**
 * Get all registered DOLLIE_SETUP packages.
 *
 * @since 1.1.0
 *
 * @return array Key/value pairs (package name => class name)
 */
function dollie_setup_get_packages() {
	/*
	 * Make some packages mandatory.
	 *
	 * @todo Might remove this restriction later.
	 */
	$default = array(
		'agency'  => 'Dollie_Setup_Package_Agency',
		'vendor'  => 'Dollie_Setup_Package_Vendor',
		'hosting' => 'Dollie_Setup_Package_Hosting',
		'waas'    => 'Dollie_Setup_Package_Waas',
	);

	/**
	 * Filter to register a custom package.
	 *
	 * @since 1.1.0
	 *
	 * @var array $packages Array key is your internal package name, value is class name to
	 *                      instantiate the class.
	 */
	$third_party = apply_filters( 'dollie_setup_register_packages', array() );

	return $default + (array) $third_party;
}

/**
 * Get the current, active DOLLIE_SETUP package.
 *
 * @since 1.1.0
 */
function dollie_setup_get_current_package_id() {
	$current = get_site_option( '_dollie_setup_current_package' );

	// // We've never saved a package into the DB before.
	// if ( dollie_setup_get_installed_revision_date() && empty( $current ) ) {
	// *
	// * If installed date is before 2018/09/01, save as 'agency' for backpat.
	// *
	// * @todo Change date to whenever we launch v1.1.0
	// */
	// if ( dollie_setup_get_installed_revision_date() < strtotime( '2018/09/01 UTC' ) ) {
	// $current = 'agency';
	// update_site_option( '_dollie_setup_current_package', $current );
	// }
	// }

	return $current;
}

/**
 * Get a specific property from a registered DOLLIE_SETUP package.
 *
 * @since 1.1.0
 *
 * @param  string $prop       The property to fetch from the DOLLIE_SETUP package.
 * @param  string $package_id The DOLLIE_SETUP package to query. If empty, falls back to current package ID.
 * @return mixed|false        Boolean false on failure, any other type on success.
 */
function dollie_setup_get_package_prop( $prop = '', $package_id = '' ) {
	if ( empty( $package_id ) ) {
		$package_id = dollie_setup_get_current_package_id();
	}

	if ( empty( $package_id ) ) {
		return false;
	}

	$packages = dollie_setup_get_packages();
	if ( isset( $packages[ $package_id ] ) && class_exists( $packages[ $package_id ] ) ) {
		// Name is set early.
		if ( 'name' === $prop ) {
			return $packages[ $package_id ]::$$prop;
		}

		// Ensure props are set.
		$packages[ $package_id ]::set_props();

		// Try to fetch props.
		switch ( $prop ) {
			case 'theme':
			case 'strings':
				$props = $packages[ $package_id ]::$$prop;
				break;

			default:
				$props = $packages[ $package_id ]::get_props();
				break;
		}

		// See if our prop exists and return.
		switch ( $prop ) {
			case 'theme':
			case 'strings':
				if ( isset( $packages[ $package_id ]::$$prop ) ) {
					return $packages[ $package_id ]::$$prop;
				}
				break;

			default:
				$props = $packages[ $package_id ]::get_props();
				if ( isset( $props[ $prop ] ) ) {
					return $props[ $prop ];
				}

				break;
		}
	}

	return false;
}

/**
 * Get a specific property from a registered DOLLIE_SETUP package's theme.
 *
 * @since 1.1.0
 *
 * @param  string $prop       The property to fetch from the DOLLIE_SETUP package theme.
 * @param  string $package_id The DOLLIE_SETUP package to query. If empty, falls back to current package ID.
 * @return mixed|false        Boolean false on failure, any other type on success.
 */
function dollie_setup_get_theme_prop( $prop = '', $package_id = '' ) {
	if ( empty( $package_id ) ) {
		$package_id = dollie_setup_get_current_package_id();
	}

	if ( empty( $package_id ) ) {
		return false;
	}

	$theme = dollie_setup_get_package_prop( 'theme', $package_id );
	if ( false === $theme ) {
		return false;
	}

	if ( isset( $theme[ $prop ] ) ) {
		return $theme[ $prop ];
	}

	return false;
}

/**
 * Get a specific string from a registered DOLLIE_SETUP package.
 *
 * @since 1.1.0
 *
 * @param  string $prop       The string to fetch from the DOLLIE_SETUP package.
 * @param  string $package_id The DOLLIE_SETUP package to query. If empty, falls back to current package ID.
 * @return string
 */
function dollie_setup_get_string( $string = '', $package_id = '' ) {
	if ( empty( $package_id ) ) {
		$package_id = dollie_setup_get_current_package_id();
	}

	if ( empty( $package_id ) ) {
		return '';
	}

	$strings = dollie_setup_get_package_prop( 'strings', $package_id );
	if ( false === $strings ) {
		return '';
	}

	if ( isset( $strings[ $string ] ) ) {
		return $strings[ $string ];
	}

	return '';
}
