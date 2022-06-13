<?php

/**
 * Plugin Name: Dollie
 * Description: Start offering white-labeled cloud services and SaaS/WaaS to your customers right away
 * Plugin URI:  https://getdollie.com
 * Version:     5.0.0
 * Author:      GetDollie
 *
 * Text Domain: dollie
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'DOLLIE_VERSION', '5.0.0' );
define( 'DOLLIE_SLUG', 'dollie' );

define( 'DOLLIE_FILE', __FILE__ );
define( 'DOLLIE_PLUGIN_BASE', plugin_basename( DOLLIE_FILE ) );
define( 'DOLLIE_PATH', plugin_dir_path( DOLLIE_FILE ) );
define( 'DOLLIE_URL', plugins_url( '/', DOLLIE_FILE ) );
define( 'DOLLIE_CORE_PATH', DOLLIE_PATH . 'core/' );
define( 'DOLLIE_MODULE_TPL_PATH', DOLLIE_PATH . 'templates/' );
define( 'DOLLIE_ASSETS_URL', DOLLIE_URL . 'assets/' );
define( 'DOLLIE_WIDGETS_PATH', DOLLIE_CORE_PATH . 'Widgets/' );

if ( ! defined( 'DOLLIE_DEV' ) ) {
	define( 'DOLLIE_API_URL', 'https://api.getdollie.com/api/' );
}

if ( ! defined( 'DOLLIE_DEV' ) ) {
	define( 'DOLLIE_PARTNERS_URL', 'https://dashboard.getdollie.com/' );
}

define( 'DOLLIE_BLUEPRINTS_COOKIE', 'dollie_blueprint_id' );
define( 'DOLLIE_BLUEPRINTS_COOKIE_PARAM', 'blueprint_id' );

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
