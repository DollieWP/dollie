<?php


namespace Dollie\Core\Admin;

use Dollie\Core\Singleton;
use Dollie\Core\Utils\ConstInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class Admin extends Singleton implements ConstInterface {
	/**
	 * Admin constructor
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'plugins_loaded', [ $this, 'initialize' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'load_resources' ] );

		if ( dollie()->is_live() ) {
			add_action( 'init', [ $this, 'add_options_page' ] );

			if ( is_admin() ) {
				add_action( 'acf/input/admin_head', [ $this, 'add_api_box' ], 1 );
			}
		}
	}

	/**
	 * Initialize
	 *
	 * @return void
	 */
	public function initialize() {
		NavMenu::instance();
		Container::instance();

		if ( class_exists( '\Elementor\Plugin' ) ) {
			Upgrades::instance();
		}
	}

	/**
	 * Load CSS and JS resources
	 *
	 * @param $hook
	 */
	public function load_resources() {
		wp_enqueue_style(
			'dollie-custom-css',
			DOLLIE_ASSETS_URL . 'css/admin.css',
			[],
			DOLLIE_VERSION
		);

		wp_enqueue_style(
			'dollie-custom-admin',
			DOLLIE_ASSETS_URL . 'css/dollie.css',
			[],
			DOLLIE_VERSION
		);

		wp_enqueue_script(
			'dollie-custom-js',
			DOLLIE_ASSETS_URL . 'js/admin.js',
			[],
			DOLLIE_VERSION
		);
	}

	/**
	 * Add options page
	 *
	 * @return void
	 */
	public function add_options_page() {
		if ( ! function_exists( 'acf_add_options_page' ) ) {
			return;
		}

		$args = [
			'page_title'  => __( 'Settings', 'dollie' ),
			'menu_title'  => __( 'Settings <span class="dol-status dol-live">Live<span>', 'dollie' ),
			'menu_slug'   => self::PANEL_SLUG,
			'capability'  => 'manage_options',
			'position'    => '99',
			'parent_slug' => 'dollie_setup',
			'icon_url'    => false,
			'redirect'    => true,
			'autoload'    => true,
		];

		acf_add_options_page( $args );
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function add_api_box() {
		$screen = get_current_screen();

		if ( $screen && 'toplevel_page_' . self::PANEL_SLUG === $screen->id ) {
			add_meta_box(
				'custom-mb-before-acf',
				'CUSTOM MB BEFORE ACF',
				[
					$this,
					'api_box_callback',
				],
				'acf_options_page',
				'normal',
				'high'
			);
		}
	}

	/**
	 * Api status callback
	 *
	 * @param $post
	 * @param array $args
	 */
	public function api_box_callback( $post, $args = [] ) {
		dollie_setup_get_template_part( 'setup-complete' );
	}
}
