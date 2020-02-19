<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Helpers;
use Dollie\Core\Log;

/**
 * Class Scripts
 * @package Dollie\Core\Modules
 */
class Scripts extends Singleton {

	/**
	 * @var mixed
	 */
	private $helpers;

	/**
	 * Scripts constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->helpers = Helpers::instance();

		if ( function_exists( 'wff_child_theme_setup' ) ) {
			add_action( 'wp_enqueue_scripts', 'my_dequeue_styles', 10000 );
			add_action( 'wp_enqueue_scripts', 'wff_child_scripts', 10000000 );
			add_action( 'wp_enqueue_scripts', 'dequeue_buddypress' );
		}
	}

	public function my_dequeue_styles() {
		wp_dequeue_style( 'wff_main' );
		wp_dequeue_style( 'wff_buddypress' );
	}

	public function wff_child_scripts() {
		//Don't load BuddyPress
		if ( ! is_page_template( 'divi-builder-page' ) ) {
			wp_enqueue_style( 'wff_bp', get_template_directory_uri() . '/assets/css/buddypress.min.css', false, '1' );
		}
		if ( is_page( 'analytics' ) ) {
			wp_enqueue_script( 'chart_js', 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.bundle.js', false, '1' );
		}
		$script = get_template_directory_uri() . '/assets/js/scripts.min.js';
		wp_enqueue_style( 'wff_child', get_template_directory_uri() . '/assets/css/main.min.css', false, '1' );
		wp_register_script( 'wff_child_script', $script, array(), '1', true );
		//wp_register_script('wff_iframe_resizer', get_template_directory_uri() . '/assets/js/plugins/iframe-resizer.js', array(), '1', false);
		wp_enqueue_script( 'wff_child_script' );
		//wp_enqueue_script('wff_iframe_resizer');
	}

	public function dequeue_buddypress() {
		if ( ! is_buddypress() ) {
			wp_dequeue_style( 'bp-legacy-css' );
			wp_deregister_script( 'bp-jquery-query' );
			wp_deregister_script( 'bp-confirm' );
		}
	}

}
