<?php
/*
Plugin Name: ACF Options Page
Description: Add options page to ACF.
Version: 2.1.0
Author: codezz
Text Domain: options-page-for-acf
Domain Path: /language
*/

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define( 'ACF_OP', '4.0.7' );
define( 'ACF_OP_SLUG', 'acf-options-page' );

define( 'ACF_OP_FILE', __FILE__ );
define( 'ACF_OP_PLUGIN_BASE', plugin_basename( ACF_OP_FILE ) );
define( 'ACF_OP_PATH', plugin_dir_path( ACF_OP_FILE ) );
define( 'ACF_OP_URL', plugins_url( '/', ACF_OP_FILE ) );

require_once ACF_OP_PATH . 'inc/acf_options_page.php';
require_once ACF_OP_PATH . 'inc/acf_admin_options_page.php';
require_once ACF_OP_PATH . 'inc/ACF_Location_Options_Page.php';
