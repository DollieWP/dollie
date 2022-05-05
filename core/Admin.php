<?php


namespace Dollie\Core;

use Dollie\Core\Admin\Container;
use Dollie\Core\Admin\NavMenu;
use Dollie\Core\Admin\Upgrades;
use Dollie\Core\Services\NoticeService;
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

		add_action( 'plugins_loaded', array( $this, 'initialize' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_resources' ) );
		add_filter( 'admin_body_class', array( $this, 'add_dollie_dev_class' ) );

		if ( dollie()->is_live() ) {
			add_action( 'init', array( $this, 'add_options_page' ) );

			if ( is_admin() ) {
				add_action( 'acf/input/admin_head', array( $this, 'add_api_box' ), 1 );
			}
		}

		add_action( 'admin_notices', array( NoticeService::instance(), 'not_connected' ) );
		add_action( 'admin_notices', array( NoticeService::instance(), 'custom_deploy_domain' ) );
		add_action( 'admin_notices', array( NoticeService::instance(), 'subscription_no_credits' ) );
		add_action( 'wp_ajax_dollie_hide_domain_notice', array( NoticeService::instance(), 'remove_custom_deploy_domain' ) );
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

	/**
	 * Remove deployment domain
	 *
	 * @return void
	 */
	public function remove_custom_deploy_domain(): void {
		if ( ! check_ajax_referer( 'dollie_notice', '_dollie_nonce' ) ) {
			wp_send_json_error();
		}

		update_option( 'deployment_domain_notice', true );
		wp_send_json_success();
	}
}
