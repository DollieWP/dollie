<?php
/**
 * Agency: Set defaults for our Agency
 */

/**
 * bbPress routine after activation.
 *
 * - Don't let bbPress redirect to its about page after activating.
 * - Create a forum category to house BuddyPress group forums if necessary.
 *
 * @since 1.1.0 This logic was moved out of the DOLLIE_SETUP plugin-install.php file.
 */
add_action( 'plugins_loaded', function( ) {
	echo 'hi';
	die();

} );
