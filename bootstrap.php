<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * PSR4 autoloader using Composer
 *
 * @since 1.0.0
 */
if ( file_exists( DOLLIE_PATH . 'vendor/autoload.php' ) ) {
	require DOLLIE_PATH . 'vendor/autoload.php';
} else {
	wp_die(
		__( 'Dollie requires Composer\'s autoload to run. Please run `composer install` from Dollie\'s plugin root directory.', DOLLIE_SLUG ),
		__( 'Dollie - Autoload Required', DOLLIE_SLUG )
	);
}
