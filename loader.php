<?php

/**
 * Plugin Name: Dollie
 * Description: A turn-key solution for WordPress product vendors, agencies and developers to start offering white-labeled cloud services and SaaS/WaaS to their customers.
 * Plugin URI:  https://getdollie.com
 * Version:     1.0.0
 * Author:      Dollie
 * Author URI:  https://www.dollie.co/
 *
 * Text Domain: dollie
 * Domain Path: /languages/.
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'DOLLIE_VERSION', '1.3.0' );
define( 'DOLLIE_SLUG', 'dollie' );

define( 'DOLLIE_FILE', __FILE__ );
define( 'DOLLIE_PLUGIN_BASE', plugin_basename( DOLLIE_FILE ) );
define( 'DOLLIE_PATH', plugin_dir_path( DOLLIE_FILE ) );
define( 'DOLLIE_URL', plugins_url( '/', DOLLIE_FILE ) );
define( 'DOLLIE_CORE_PATH', DOLLIE_PATH . 'core/' );
define( 'DOLLIE_ASSETS_URL', DOLLIE_URL . 'assets/' );

// Init plugin
require_once DOLLIE_CORE_PATH . 'Plugin.php';
