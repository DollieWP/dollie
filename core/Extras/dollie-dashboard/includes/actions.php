<?php

/**
 * Dollie Dashboard Actions & Filters
 *
 * @package WeFoster Dasboard
 * @subpackage Hooks
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Libraries *************************************************************/

add_action( 'Dollie_DBloaded', 'Dollie_DBacf', 5 );

/** Plugin ****************************************************************/

add_action( 'Dollie_DBloaded', 'Dollie_DBpages'   );
add_action( 'init',            'Dollie_DBinit'    );

/** Sub-actions ***********************************************************/

/**
 * Register plugin init hook
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'Dollie_DBinit'
 */
function Dollie_DBinit() {
	do_action( 'Dollie_DBinit' );
}
