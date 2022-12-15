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

		add_action( 'init', array( $this, 'add_templates_options_page' ) );

		if ( dollie()->is_live() ) {
			add_action( 'init', array( $this, 'add_options_page' ) );

			if ( is_admin() ) {
				add_action( 'acf/input/admin_head', array( $this, 'add_api_box' ), 1 );
			}
		}

		add_action( 'admin_notices', array( NoticeService::instance(), 'not_connected' ) );
		add_action( 'admin_notices', array( NoticeService::instance(), 'display_custom_deploy_domain_notice' ) );
		add_action( 'admin_notices', array( NoticeService::instance(), 'subscription_no_credits' ) );
		add_action( 'admin_notices', array( NoticeService::instance(), 'subscription_not_verified' ) );
		add_action( 'wp_ajax_dollie_hide_domain_notice', array(
			NoticeService::instance(),
			'remove_custom_deploy_domain_notice'
		) );

		add_action( 'admin_init', [ $this, 'import_template' ] );
		add_action( 'admin_init', [ $this, 'set_old_templates_as_imported' ], 9 );
		add_action( 'admin_notices', [ $this, 'admin_notice' ], 20 );

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
			array(),
			DOLLIE_VERSION
		);

		wp_enqueue_style(
			'dollie-custom-admin',
			DOLLIE_ASSETS_URL . 'css/dollie.css',
			array(),
			DOLLIE_VERSION
		);

		wp_enqueue_script(
			'dollie-global',
			DOLLIE_ASSETS_URL . 'js/dollie-global.js',
			[],
			DOLLIE_VERSION,
			true
		);

	}

	public function add_templates_options_page() {
		$hook = add_submenu_page(
			'admin.php?page=dollie_setup',
			__( 'Dollie Templates', 'dollie' ),
			__( 'Dollie Templates', 'dollie' ),
			'manage_options',
			'dollie_templates',
			array( $this, 'templates_options_page_callback' )
		);
		add_action( 'admin_enqueue_scripts', function ( $handle ) use ( $hook ) {
			if ( $handle === $hook ) {
				wp_enqueue_style(
					'dollie-custom',
					DOLLIE_ASSETS_URL . 'css/dollie.css',
					array(),
					DOLLIE_VERSION
				);
			}
		} );

	}

	public function templates_options_page_callback() {

		$imported_templates = get_option( 'dollie_imported_templates', [] );
		$message            = '';
		if ( isset( $_GET['message'] ) ) {
			$message = sanitize_text_field( $_GET['message'] );
		}
		$templates = [
			[
				'name'        => 'Elementor Template',
				'image'       => DOLLIE_ASSETS_URL . 'img/template-elementor.jpg',
				'url'         => admin_url( 'admin.php?page=dollie_templates&dol-import=elementor' ),
				'is_imported' => isset( $imported_templates['elementor'] ),
			],
			[
				'name'        => 'Guntenberg Template',
				'image'       => DOLLIE_ASSETS_URL . 'img/template-gutenberg.jpg',
				'url'         => admin_url( 'admin.php?page=dollie_templates&dol-import=gutenberg' ),
				'is_imported' => isset( $imported_templates['gutenberg'] ),
			],
		];

		dollie()->load_template( 'admin/templates',
			[
				'templates' => $templates,
				'message'   => $message
			]
			, true );
	}

	/**
	 * Import pre-made templates.
	 *
	 * @return void
	 */
	public function import_template() {

		// only admins.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( ! isset( $_GET['dol-import'] ) ) {
			return;
		}

		// process import request.
		$imported_ids    = [];
		$import_template = sanitize_text_field( $_GET['dol-import'] );

		// check if template is already imported.
		$imported_templates = get_option( 'dollie_imported_templates', [] );
		if ( isset( $imported_templates[ $import_template ] ) && ! empty( $imported_templates[ $import_template ] ) ) {

			// delete the old templates.
			foreach ( $imported_templates[ $import_template ] as $existing_template ) {
				if ( get_post( $existing_template ) ) {
					wp_delete_post( $existing_template->ID, false );
				}
			}
			//wp_redirect( admin_url( 'admin.php?page=dollie_templates&message=Template already imported' ) );
			//exit;
		}

		// Import it.
		if ( $import_template === 'elementor' ) {
			$imported_ids = Upgrades::instance()->import_elementor_template();
		} elseif ( $import_template === 'gutenberg' ) {
			$imported_ids = Upgrades::instance()->import_gutenberg_template();
		}

		// save the imported template to db.
		if ( ! empty( $imported_ids ) ) {
			$imported_templates[ $import_template ] = $imported_ids;
			update_option( 'dollie_imported_templates', $imported_templates );
			update_option( 'dollie_active_template', $import_template );
		}
		wp_redirect( admin_url( 'admin.php?page=dollie_templates&message=Import successful. Check Dollie - Settings - Pages' ) );
		exit;
	}

	public function set_old_templates_as_imported() {
		$imported_templates = get_option( 'dollie_imported_templates', [] );

		if ( isset( $imported_templates['elementor'] ) ) {
			return;
		}

		$imported_ids   = [];
		$template_pages = [
			'wpd_launch_page_id',
			'wpd_launch_blueprint_page_id',
			'wpd_dashboard_page_id',
			'wpd_customers_page_id',
			'wpd_login_page_id',
			'wpd_sites_page_id',
			'wpd_site_template_id',
			'wpd_site_launching_template_id'
		];
		foreach ( $template_pages as $template_page ) {
			$page_id = get_option( 'options_' . $template_page );
			if ( $page_id ) {
				$imported_ids[ $template_page ] = $page_id;
			}
		}

		if ( ! empty( $imported_ids ) ) {
			$imported_templates['elementor'] = $imported_ids;
			update_option( 'dollie_imported_templates', $imported_templates );
		}
	}

	/**
	 * Show admin notice to import templates
	 */
	public function admin_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$imported_templates = get_option( 'dollie_imported_templates', [] );
		if ( ! empty( $imported_templates ) ) {
			return;
		}
		?>

        <div class="notice dollie-notice">

            <div class="dollie-inner-message">

                <div class="dollie-message-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <h3><?php _e( 'Dollie Templates Missing', 'dollie' ); ?></h3>
                    <p>
						<?php
						echo wp_kses_post(
							sprintf(
								__(
									'<strong>Dollie</strong> needs to have a set of pages for its components. Please <a href="%s">import a template</a>',
									'dollie'
								),
								esc_url( get_admin_url() . 'admin.php?page=dollie_templates' )
							)
						);
						?>
                    </p>
                </div>

            </div>
        </div>
		<?php

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

		$args = array(
			'page_title'  => __( 'Settings', 'dollie' ),
			'menu_title'  => __( 'Settings', 'dollie' ),
			'parent_slug' => 'dollie_setup',
			'menu_slug'   => self::PANEL_SLUG,
			'capability'  => 'manage_options',
			'position'    => '4',
			'icon_url'    => 'dashicons-admin-generic',
			'redirect'    => true,
			'autoload'    => true,
		);

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
				array(
					$this,
					'api_box_callback',
				),
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
	public function api_box_callback( $post, $args = array() ) {
		// dollie_setup_get_template_part( 'setup-complete' );
	}

	/**
	 * Add body container class
	 *
	 * @param string $classes
	 *
	 * @return string
	 */
	public function add_dollie_dev_class( $classes ) {
		if ( defined( 'DOLLIE_DEV' ) && DOLLIE_DEV ) {
			$classes .= ' dol-dev-mode';
		}

		return $classes;
	}
}
