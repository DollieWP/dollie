<?php

/**
 * Plugin Name: Dollie
 * Description: A turn-key solution for WordPress product vendors, agencies and developers to start offering white-labeled cloud services and SaaS/WaaS to their customers.
 * Plugin URI:  https://getdollie.com
 * Version:     3.2.6
 * Author:      Dollie
 * Author URI:  https://getdollie.com
 *
 * Text Domain: dollie
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'DOLLIE_VERSION', '3.2.6' );
define( 'DOLLIE_SLUG', 'dollie' );

define( 'DOLLIE_FILE', __FILE__ );
define( 'DOLLIE_PLUGIN_BASE', plugin_basename( DOLLIE_FILE ) );
define( 'DOLLIE_PATH', plugin_dir_path( DOLLIE_FILE ) );
define( 'DOLLIE_URL', plugins_url( '/', DOLLIE_FILE ) );
define( 'DOLLIE_CORE_PATH', DOLLIE_PATH . 'core/' );
define( 'DOLLIE_MODULE_TPL_PATH', DOLLIE_PATH . 'templates/' );
define( 'DOLLIE_ASSETS_URL', DOLLIE_URL . 'assets/' );

if ( ! defined( 'DOLLIE_MEMORY' ) ) {
	define( 'DOLLIE_MEMORY', 1024 );
}
define( 'DOLLIE_S5_USER', get_option( 'options_wpd_api_email' ) );
define( 'DOLLIE_S5_PASSWORD', get_option( 'options_wpd_api_password' ) );

$domain     = get_option( 'options_wpd_api_domain' );
$sub_domain = preg_replace( '#^https?://#', '', rtrim( $domain, '/' ) );
$enterprise = get_option( 'options_wpd_api_dashboard_url' );

if ( substr_count( $sub_domain, '.' ) === 2 ) {
	define( 'DOLLIE_DOMAIN', '-' . $sub_domain );
} else {
	define( 'DOLLIE_DOMAIN', '.' . $sub_domain );
}

define( 'DOLLIE_INSTALL', $enterprise === '' ? $domain : $enterprise );

/*
 * Localization
 */
function dollie_load_plugin_textdomain() {
	load_plugin_textdomain( 'dollie', false, basename( __DIR__ ) . '/languages/' );
}

add_action( 'plugins_loaded', 'dollie_load_plugin_textdomain' );

// Autoload
require_once 'bootstrap.php';

\Dollie\Core\Plugin::instance();

/**
 * Returns the helpers instance of Dollie.
 *
 * @return Dollie\Core\Utils\Helpers
 * @since  2.0
 */
function dollie() {
	return \Dollie\Core\Utils\Helpers::instance();
}

// Init Dollie
dollie();
