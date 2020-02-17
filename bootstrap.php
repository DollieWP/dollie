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

	/**
	 * Custom autoloader function for dollie plugin.
	 *
	 * @access private
	 *
	 * @param string $class_name Class name to load.
	 *
	 * @return bool True if the class was loaded, false otherwise.
	 */
	function _dollie_autoload( $class_name ) {
		$namespace = 'Dollie\Core';

		if ( strpos( $class_name, $namespace . '\\' ) !== 0 ) {
			return false;
		}

		$parts = explode( '\\', substr( $class_name, strlen( $namespace . '\\' ) ) );

		$path = DOLLIE_PATH . '/core';
		foreach ( $parts as $part ) {
			$path .= '/' . $part;
		}
		$path .= '.php';

		if ( ! file_exists( $path ) ) {
			return false;
		}

		require_once $path;

		return true;
	}

	spl_autoload_register( '_dollie_autoload' );
}
