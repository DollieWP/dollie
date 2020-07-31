<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;
use Dollie\Core\Utils\Tpl;

/**
 * Class Options
 * @package Dollie\Core\Modules
 */
class Options extends Singleton {

	/**
	 * Options constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_filter( 'acf/settings/save_json', static function () {
			return get_template_directory() . '/acf-json';
		} );

		add_filter( 'acf/settings/load_json', static function () {
			return [ get_template_directory() . '/acf-json' ];
		} );

		add_action( 'admin_init', [ $this, 'deactivate_admin_menu_editor' ] );

		if ( function_exists( 'acf_add_options_page' ) ) {
			acf_add_options_page(
				[
					'page_title' => 'Dollie Setup',
					'menu_title' => 'Dollie',
					'menu_slug'  => 'wpd_platform_setup',
					'capability' => 'manage_options',
					'redirect'   => true,
					'position'   => 2,
				]
			);
		}

		add_action( 'init', [ $this, 'add_a_test_acf_options_page' ] );
		add_action( 'init', [ $this, 'set_dollie_page_templates' ] );
		add_action( 'admin_menu', [ $this, 'add_a_test_menu_page' ], 9 );
		add_action( 'admin_menu', [ $this, 'remove_duplicate_admin_menu' ], 100 );
		add_action( 'admin_menu', [ $this, 'remove_duplicate_integration_menu' ], 100 );
		add_action( 'admin_menu', [ $this, 'remove_duplicate_forms_menu' ], 100 );
		add_action( 'admin_menu', [ $this, 'add_external_menu_links' ], 100 );
		add_action( 'admin_menu', [ $this, 'add_theme_menu_item' ], 11 );
		add_action( 'wp_before_admin_bar_render', [ $this, 'dollie_adminbar_menu' ], 2000 );

		add_action(
			'load-edit.php',
			static function () {
				$screen = get_current_screen();
				// Only edit post screen:
				if ( 'edit-container' === $screen->id ) {
					add_action(
						'all_admin_notices',
						static function () {
							?>
                            <div class="dollie-notice">
                                <h3>
                                    <svg aria-hidden="true" data-prefix="fab" data-icon="docker" role="img"
                                         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"
                                         class="svg-inline--fa fa-docker fa-w-20 fa-3x">
                                        <path fill="currentColor"
                                              d="M349.9 236.3h-66.1v-59.4h66.1v59.4zm0-204.3h-66.1v60.7h66.1V32zm78.2 144.8H362v59.4h66.1v-59.4zm-156.3-72.1h-66.1v60.1h66.1v-60.1zm78.1 0h-66.1v60.1h66.1v-60.1zm276.8 100c-14.4-9.7-47.6-13.2-73.1-8.4-3.3-24-16.7-44.9-41.1-63.7l-14-9.3-9.3 14c-18.4 27.8-23.4 73.6-3.7 103.8-8.7 4.7-25.8 11.1-48.4 10.7H2.4c-8.7 50.8 5.8 116.8 44 162.1 37.1 43.9 92.7 66.2 165.4 66.2 157.4 0 273.9-72.5 328.4-204.2 21.4.4 67.6.1 91.3-45.2 1.5-2.5 6.6-13.2 8.5-17.1l-13.3-8.9zm-511.1-27.9h-66v59.4h66.1v-59.4zm78.1 0h-66.1v59.4h66.1v-59.4zm78.1 0h-66.1v59.4h66.1v-59.4zm-78.1-72.1h-66.1v60.1h66.1v-60.1z"
                                              class=""></path>
                                    </svg>
                                    The Site Manager
                                </h3>
                                <p>
                                    Below you'll find all of your sites launched by you and your customers. Click on a
                                    site for
                                    a quick overview of the WordPress installation or to access various management tools
                                    and
                                    create
                                    re-usable Blueprints with the click of a button.
                                </p>
                            </div>
							<?php
						}
					);
				}

				// Only edit post screen:
				if ( 'edit-dollie-logs' === $screen->id ) {
					add_action(
						'all_admin_notices',
						static function () {
							?>
                            <div class="dollie-notice">
                                <h3>
                                    <svg aria-hidden="true" data-prefix="fal" data-icon="pennant" role="img"
                                         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"
                                         class="svg-inline--fa fa-pennant fa-w-18 fa-3x">
                                        <path fill="currentColor"
                                              d="M552 191.3c-30 6.2-115.6 12.5-260.7-63.6-87-45.7-158.1-51.8-210-45.2 9-8.8 14.7-21 14.7-34.5C96 21.5 74.5 0 48 0S0 21.5 0 48c0 20.8 13.4 38.4 32 45.1V504c0 4.4 3.6 8 8 8h16c4.4 0 8-3.6 8-8v-79.6c40.2-27.2 102-56.4 179.5-60.2 28.7-1.4 76-5.8 137.9-18.8 4.4-.9 109.4-23.8 190-121.7 11.8-14.3-.7-36.2-19.4-32.4zM48 32c8.8 0 16 7.2 16 16s-7.2 16-16 16-16-7.2-16-16 7.2-16 16-16zm326.9 282.1c-59.9 12.5-105.4 16.8-133 18.2-84.8 4.2-145.3 35.1-177.9 54.2V117.7c47.2-10.6 119.5-10.5 212.4 38.3 118.9 62.4 202.3 72.4 249.5 70.4-69.5 69.7-150.1 87.5-151 87.7z"
                                              class=""></path>
                                    </svg>
                                    The Activity Log
                                </h3>
                                <p>
                                    The activity log keeps track of everything you need to know regarding your sites,
                                    actions taken by your customers and recurring crons/maintenance jobs that run on
                                    your
                                    installation.
                                </p>
                            </div>
							<?php
						}
					);
				}
				// Only edit post screen:
				if ( 'edit-af_form' === $screen->id ) {
					add_action(
						'all_admin_notices',
						static function () {
							?>
                            <div class="dollie-notice">
                                <h3>
                                    <svg aria-hidden="true" data-prefix="fal" data-icon="pennant" role="img"
                                         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"
                                         class="svg-inline--fa fa-pennant fa-w-18 fa-3x">
                                        <path fill="currentColor"
                                              d="M552 191.3c-30 6.2-115.6 12.5-260.7-63.6-87-45.7-158.1-51.8-210-45.2 9-8.8 14.7-21 14.7-34.5C96 21.5 74.5 0 48 0S0 21.5 0 48c0 20.8 13.4 38.4 32 45.1V504c0 4.4 3.6 8 8 8h16c4.4 0 8-3.6 8-8v-79.6c40.2-27.2 102-56.4 179.5-60.2 28.7-1.4 76-5.8 137.9-18.8 4.4-.9 109.4-23.8 190-121.7 11.8-14.3-.7-36.2-19.4-32.4zM48 32c8.8 0 16 7.2 16 16s-7.2 16-16 16-16-7.2-16-16 7.2-16 16-16zm326.9 282.1c-59.9 12.5-105.4 16.8-133 18.2-84.8 4.2-145.3 35.1-177.9 54.2V117.7c47.2-10.6 119.5-10.5 212.4 38.3 118.9 62.4 202.3 72.4 249.5 70.4-69.5 69.7-150.1 87.5-151 87.7z"
                                              class=""></path>
                                    </svg>
                                    Dollie Forms
                                </h3>
                                <p>
                                    These forms can be embedded easily to further customize the experience for your
                                    customers. Please only edit these forms if you're a developer and you have good
                                    knowledge of the Dollie platform and it's API.
                                </p>
                            </div>
							<?php
						}
					);
				}
			}
		);

		add_action( 'admin_menu', [ $this, 'dollie_tools' ], 99 );
		add_action( 'admin_menu', [ $this, 'dollie_forms' ], 99 );
		add_action( 'admin_menu', [ $this, 'dollie_api' ], 99 );
		add_action( 'admin_menu', [ $this, 'dollie_integrations' ], 99 );
		add_filter( 'admin_body_class', [ $this, 'add_staging_body_class' ] );

		if ( is_admin() ) {
			add_action( 'acf/input/admin_head', [ $this, 'add_api_boxes_before' ], 1 );
		}
	}

	/**
	 * Check if site is live
	 *
	 * @return bool
	 */
	public function is_live() {
		return dollie()->is_live();
	}

	/**
	 * Disable admin menu editor
	 */
	public function deactivate_admin_menu_editor() {
		$url = get_option( 'siteurl' );
		if ( $this->is_live() && strpos( $url, 'dollie.io' ) ) {
			deactivate_plugins( 'admin-menu-editor-pro/menu-editor.php' );
		}
	}

	/**
	 * Test acf page option
	 */
	public function add_a_test_acf_options_page() {
		if ( ! function_exists( 'acf_add_options_page' ) ) {
			return;
		}

		if ( $this->is_live() ) {
			$title = esc_html__( 'Dollie (Live)', 'dollie' );
		} else {
			$title = esc_html__( 'Dollie (Staging)', 'dollie' );
		}

		$args = [
			'page_title'  => esc_html__( 'Settings', 'dollie' ),
			'menu_title'  => $title,
			'menu_slug'   => 'wpd_platform_setup',
			'capability'  => 'manage_options',
			'position'    => '-4.374981',
			'parent_slug' => '',
			'icon_url'    => false,
			'redirect'    => true,
			'autoload'    => true,
		];

		acf_add_options_page( $args );
	}

	/**
	 * Test menu page
	 */
	public function add_a_test_menu_page() {
		if ( $this->is_live() ) {
			$title = esc_html__( 'Dollie (Live)', 'dollie' );
		} else {
			$title = esc_html__( 'Dollie (Staging)', 'dollie' );
		}

		$menu_title = esc_html__( 'Settings', 'dollie' );
		$capability = 'manage_options';
		$position   = '75.374981';
		$menu_slug  = 'wpd_platform_setup';
		$callback   = '';
		$icon       = '';

		add_menu_page(
			$title,
			$menu_title,
			$capability,
			$menu_slug,
			$callback,
			$icon,
			$position
		);
	}

	/**
	 * Remove duplicated admin menu
	 */
	public function remove_duplicate_admin_menu() {
		global $menu;
		foreach ( $menu as $key => $values ) {
			if ( $values[2] === 'wpd_platform_setup' ) {
				unset( $menu[ $key ] );
				break;
			}
		}
	}

	/**
	 * Remove duplicated integration menu
	 */
	public function remove_duplicate_integration_menu() {
		global $menu;
		foreach ( $menu as $key => $values ) {
			if ( $values[2] === 'dollie-integrations' ) {
				unset( $menu[ $key ] );
				break;
			}
		}
	}

	/**
	 * Remove duplicated forms menu
	 */
	public function remove_duplicate_forms_menu() {
		global $menu;
		foreach ( $menu as $key => $values ) {
			if ( $values[2] === 'edit.php?post_type=af_form' ) {
				unset( $menu[ $key ] );
				break;
			}
		}
	}

	/**
	 * Add theme menu
	 */
	public function add_theme_menu_item() {
		add_submenu_page(
			'wpd_platform_setup',
			'Launch New Site',
			'Launch New Site',
			'manage_options',
			'wpd_launch_site',
			'wpd_launch_site'
		);
	}

	/**
	 * Admin bar menu
	 */
	public function dollie_adminbar_menu() {
		global $wp_admin_bar;

		// Admin only
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$launch_site = dollie()->get_launch_page_id();

		$iconurl = DOLLIE_URL . 'assets/img/active.png';

		if ( $this->is_live() ) {
			$menu_title = esc_html__( 'Dollie (Live)', 'dollie' );
		} else {
			$menu_title = esc_html__( 'Dollie (Staging)', 'dollie' );
		}

		$iconspan = '<span class="custom-icon" style="
        float:left;
        width: 22px !important;
        height: 22px !important;
        margin-left: 2px !important;
        margin-top: 5px !important;
        background-size: cover;
        margin-right: 4px;
        position: relative;
        top: 0px;
        background-image:url(\'' . $iconurl . '\');"></span>';

		$title = $menu_title;

		$menu_id = 'dab';

		$wp_admin_bar->add_menu( [
			'id'    => $menu_id,
			'title' => $iconspan . $title,
			'href'  => '/',
		] );

		if ( ! $this->is_live() ) {
			$wp_admin_bar->add_menu( [
				'parent' => $menu_id,
				'title'  => __( 'Apply to go Live' ),
				'id'     => 'dwb-go-live',
				'href'   => 'https://partners.getdollie.com/go-live',
			] );
		}

		$wp_admin_bar->add_menu( [
			'parent' => $menu_id,
			'title'  => __( 'Settings' ),
			'id'     => 'dab-settings',
			'href'   => get_admin_url() . 'admin.php?page=wpd_platform_setup',
			'meta'   => [ 'target' => '' ],
		] );

		$wp_admin_bar->add_menu( [
			'parent' => $menu_id,
			'title'  => __( 'View Sites' ),
			'id'     => 'dab-site',
			'href'   => get_admin_url() . 'edit.php?post_type=container',
			'meta'   => [ 'target' => '' ],
		] );


		$wp_admin_bar->add_menu( [
			'parent' => $menu_id,
			'title'  => __( 'Logs' ),
			'id'     => 'dab-logs',
			'href'   => get_admin_url() . 'edit.php?post_type=dollie-logs',
			'meta'   => [ 'target' => '' ],
		] );

		if ( $launch_site ) {
			$wp_admin_bar->add_menu( [
				'parent' => $menu_id,
				'title'  => __( 'Launch New Site' ),
				'id'     => 'dwb-launch',
				'href'   => get_permalink( $launch_site ),
			] );
		}

		$wp_admin_bar->add_menu( [
			'parent' => $menu_id,
			'title'  => __( 'Forms' ),
			'id'     => 'dab-forms',
			'href'   => get_admin_url() . 'edit.php?post_type=af_form',
			'meta'   => [ 'target' => '' ],
		] );

		$wp_admin_bar->add_menu( [
			'parent' => $menu_id,
			'title'  => __( '<span id="integrations-menu">Integrations</span>' ),
			'id'     => 'dab-integrations',
			'href'   => get_admin_url() . 'admin.php?page=dollie-integrations',
			'meta'   => [ 'target' => '' ],
		] );

		$wp_admin_bar->add_menu( [
			'parent' => $menu_id,
			'title'  => __( 'Site Manager Dashboard' ),
			'id'     => 'dab-control-panel',
			'href'   => get_option( 'options_wpd_api_dashboard_url' ),
			'meta'   => [ 'target' => '' ],
		] );

		$wp_admin_bar->add_menu( [
			'parent' => $menu_id,
			'title'  => __( 'Visit Partner Dashboard' ),
			'id'     => 'dwb-partner',
			'href'   => 'https://partners.getdollie.com',
		] );

		$wp_admin_bar->add_menu( [
			'parent' => $menu_id,
			'title'  => __( 'Slack' ),
			'id'     => 'dwb-slack',
			'href'   => 'https://dollie-hub.herokuapp.com/',
		] );

	}

	/**
	 * Add tools if the site is live
	 */
	public function dollie_tools() {
		if ( $this->is_live() ) {
			add_submenu_page(
				'wpd_platform_setup',
				'Tools',
				'Tools',
				'manage_options',
				'wpd_tools',
				[ $this, 'dollie_tools_content' ]
			);
		}
	}

	/**
	 * Add forms menu
	 */
	public function dollie_forms() {
		add_submenu_page(
			'wpd_platform_setup',
			'Forms',
			'Forms',
			'manage_options',
			'edit.php?post_type=af_form'
		);
	}

	/**
	 * Add api menu
	 */
	public function dollie_api() {
		add_submenu_page(
			'wpd_platform_setup',
			'Api',
			'API',
			'manage_options',
			'wpd_api',
			[ $this, 'dollie_api_content' ]
		);
	}

	/**
	 * Add integrations menu
	 */
	public function dollie_integrations() {
		add_submenu_page(
			'wpd_platform_setup',
			'<span class="breaking-news-toggle">' . esc_html__( 'Integrations', 'dollie' ) . '</span>',
			'<span id="dollie-integrations-menu"><span class="dashicons dashicons-awards"></span> ' . esc_html__( 'Integrations', 'dollie' ) . '</span>',
			'manage_options',
			'dollie-integrations',
			static function () {
				return false;
			}
		);
	}

	/**
	 * Add external links menu
	 */
	public function add_external_menu_links() {
		global $submenu;
		// add your submenu of external links
		// add the links to the "slug" for your top-level menu (which is now your external url)
		$submenu['wpd_platform_setup'][] = array(
			'Site Manager',
			'manage_options',
			get_option( 'options_wpd_api_dashboard_url' )
		);
		$submenu['wpd_platform_setup'][] = array(
			'Partner Dashboard',
			'manage_options',
			'https://partners.getdollie.com'
		);
		$submenu['wpd_platform_setup'][] = array(
			'Slack Community',
			'manage_options',
			'https://dollie-hub.herokuapp.com/'
		);
	}

	/**
	 * Tools page content
	 */
	public function dollie_tools_content() {
		$containers = [];

		if ( array_key_exists( 'synchronize', $_POST ) ) {
			$containers = Jobs::instance()->run_sync_containers_task();
		}

		Tpl::load( 'admin/tools-page', [ 'containers' => $containers ], true );
	}

	/**
	 * Api page content
	 */
	public function dollie_api_content() {
		if ( isset( $_GET['data'] ) && $_GET['data'] ) {
			$data = @base64_decode( $_GET['data'] );
			$data = @json_decode( $data, true );

			if ( is_array( $data ) ) {
				Api::update_auth_data( $data );
			}

			wp_redirect( admin_url( 'admin.php?page=wpd_api' ) );
		}

		Tpl::load( 'admin/api-page', [], true );
	}

	/**
	 * Staging extra body class
	 *
	 * @param $classes
	 *
	 * @return string
	 */
	public function add_staging_body_class( $classes ) {
		if ( ! $this->is_live() ) {
			$classes .= ' dollie_is_staging';
		}

		return $classes;
	}

	/**
	 * Api status box
	 */
	public function add_api_boxes_before() {
		$screen = get_current_screen();
		if ( $screen && $screen->id === 'toplevel_page_wpd_platform_setup' ) {
			add_meta_box( 'custom-mb-before-acf', 'CUSTOM MB BEFORE ACF', [
				$this,
				'add_api_boxes_callback'
			], 'acf_options_page', 'normal', 'high' );
		}
	}

	/**
	 * Set page template
	 */
	public function set_dollie_page_templates() {
		$theme = wp_get_theme();
		if ( 'Dollie Framework' === $theme->name || 'Dollie Framework' === $theme->parent_theme ) {
			$login     = dollie()->get_login_page_id();
			$dashboard = dollie()->get_dashboard_page_id();
			$launch    = dollie()->get_launch_page_id();

			if ( ! empty( $login ) ) {
				update_post_meta( $login, '_wp_page_template', 'page-templates/dollie-login.php' );
			}
			if ( ! empty( $dashboard ) ) {
				update_post_meta( $dashboard, '_wp_page_template', 'page-templates/dollie-dashboard.php' );
			}
			if ( ! empty( $launch ) ) {
				update_post_meta( $launch, '_wp_page_template', 'page-templates/dollie-launch-site.php' );
			}
		}
	}

	/**
	 * Api status callback
	 *
	 * @param $post
	 * @param array $args
	 */
	public function add_api_boxes_callback( $post, $args = array() ) {
		$token_active = (bool) get_option( 'dollie_auth_token_status', 0 );
		$status       = __( 'Inactive', DOLLIE_SLUG );
		$class        = 'api-inactive';

		if ( $token_active ) {
			$status = __( 'Active', DOLLIE_SLUG );
			$class  = 'api-active';
		}

		?>
        <div class="notice dollie-notice <?php echo esc_attr( $class ); ?>">
            <div class="dollie-inner-message">
                <h2><span class="dashicons dashicons-rest-api dollie-api-status"></span>
                    API Status: <span class="<?php echo esc_attr( $class ); ?>-span"><?php echo $status; ?></span>
                </h2>

                <div>
					<?php if ( isset( $_GET['err'] ) ) : ?>
						<?php _e( 'Something went wrong. Please try again later or contact our support.', DOLLIE_SLUG ); ?>
                        <br>
                        <br>
					<?php endif; ?>

					<?php if ( isset( $_GET['status'] ) && $_GET['status'] === 'not_connected' ) : ?>
						<?php _e( 'You are not connected with the Dollie API. Please follow the instructions at the top of the page to continue with the API authentication.', DOLLIE_SLUG ); ?>
                        <br>
                        <br>
					<?php endif; ?>

					<?php if ( isset( $_GET['status'] ) && $_GET['status'] === 'refresh' ) : ?>
						<?php _e( 'Your Dollie API token has expired. Please refresh it to prevent services interruptions for you and your clients.', DOLLIE_SLUG ); ?>
                        <br>
                        <br>
					<?php endif; ?>
                    <div>
                    </div>
                    <br>
					<?php if ( \Dollie\Core\Utils\Api::get_auth_data( 'refresh_token' ) && ! \Dollie\Core\Utils\Api::auth_token_is_active() ) : ?>
						<?php echo \Dollie\Core\Plugin::instance()->get_api_refresh_link( true ); ?>
					<?php endif; ?>

					<?php if ( ! \Dollie\Core\Utils\Api::get_auth_data( 'access_token' ) && ! \Dollie\Core\Utils\Api::auth_token_is_active() ) : ?>
						<?php echo \Dollie\Core\Plugin::instance()->get_api_access_link( true ); ?>
					<?php endif; ?>

                </div>
            </div>
        </div>

		<?php
	}

}




