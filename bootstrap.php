<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * PSR4 autoloader using Composer + fallback
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

/**
 * Initialize the plugin tracker
 *
 * @return void
 */
function appsero_init_tracker_dollie() {

	if ( ! class_exists( 'Appsero\Client' ) ) {
		require_once __DIR__ . '/vendor/appsero/src/Client.php';
	}

	$client = new Appsero\Client( '7230a815-77e5-4157-8a30-e71e549e7473', 'Dollie', DOLLIE_FILE );

	// Active insights.
	$client->insights()
	->notice( '<h3>Dollie - Help us improve your experience</h3>Please allow us to collect some non-sensitive information about your installation so that we can identify issues quickly and provide you with better support.' )
	->init();

	// Active automatic updater
	$client->updater();

}

appsero_init_tracker_dollie();
