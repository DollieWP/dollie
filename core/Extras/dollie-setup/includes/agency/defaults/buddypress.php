<?php
/**
 * Agency: Set defaults for the BuddyPress plugin during initial activation.
 *
 * @since 1.1.0
 */

/**
 * Things to do after BuddyPress is activated.
 *
 * - Don't let BuddyPress redirect to its about page after activating.
 * - Install all BuddyPress components.
 *
 * @since 1.1.0 This logic was moved out of the DOLLIE_SETUP plugin-install.php file.
 */
add_action( 'activated_plugin', function( $plugin ) {
	if ( 'buddypress/bp-loader.php' !== $plugin ) {
		return;
	}
} );
