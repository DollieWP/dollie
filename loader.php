<?php

/**
 * Plugin Name: Dollie
 * Description: A turn-key solution for WordPress product vendors, agencies and developers to start offering white-labeled cloud services and SaaS/WaaS to their customers.
 * Plugin URI:  https://getdollie.com
 * Version:     1.0.0
 * Author:      Dollie
 * Author URI:  https://getdollie.com
 *
 * Text Domain: dollie
 * Domain Path: /languages/.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'DOLLIE_VERSION', '1.0.0' );
define( 'DOLLIE_SLUG', 'dollie' );

define( 'DOLLIE_FILE', __FILE__ );
define( 'DOLLIE_PLUGIN_BASE', plugin_basename( DOLLIE_FILE ) );
define( 'DOLLIE_PATH', plugin_dir_path( DOLLIE_FILE ) );
define( 'DOLLIE_URL', plugins_url( '/', DOLLIE_FILE ) );
define( 'DOLLIE_CORE_PATH', DOLLIE_PATH . 'core/' );
define( 'DOLLIE_ASSETS_URL', DOLLIE_URL . 'assets/' );

// Sensitive data
if ( ! defined( 'DOLLIE_RUNDECK_TOKEN' ) ) {
	define( 'DOLLIE_RUNDECK_TOKEN', 'lRKqXgIpMYu9gFNvWDICFOPXHxULWmG8' );
}
if ( ! defined( 'DOLLIE_RUNDECK_URL' ) ) {
	define( 'DOLLIE_RUNDECK_URL', 'https://worker.getdollie.com' );
}
if ( ! defined( 'DOLLIE_PACKAGE' ) ) {
	define( 'DOLLIE_PACKAGE', '2c9fa77e6f320129016f8a85a3870250' );
}
if ( ! defined( 'DOLLIE_MEMORY' ) ) {
	define( 'DOLLIE_MEMORY', 1024 );
}
define( 'DOLLIE_S5_USER', get_option( 'options_wpd_api_email' ) );
define( 'DOLLIE_S5_PASSWORD', get_option( 'options_wpd_api_password' ) );

// Autoload
require_once 'bootstrap.php';

// Init
\Dollie\Core\Plugin::instance();
