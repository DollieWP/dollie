<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;

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
		add_action( 'admin_menu', [ $this, 'add_a_test_menu_page' ], 9 );
		add_action( 'admin_menu', [ $this, 'remove_duplicate_admin_menu' ], 100 );
		add_action( 'admin_menu', [ $this, 'remove_duplicate_integration_menu' ], 100 );
		add_action( 'admin_menu', [ $this, 'add_theme_menu_item' ], 11 );
		add_action( 'wp_before_admin_bar_render', [ $this, 'dollie_adminbar_menu' ], 2000 );

		add_action(
			'load-edit.php', static function () {
			$screen = get_current_screen();
			// Only edit post screen:
			if ( 'edit-container' === $screen->id ) {
				add_action(
					'all_admin_notices', static function () {
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
                            The Container Manager
                        </h3>
                        <p>
                            Below you'll find all of your site containers. Click on a container for a quick overview of
                            the WordPress installation running inside of it, access various management tools and create
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
					'all_admin_notices', static function () {
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
                            The activity log keeps track of everything you need to know regarding your containers,
                            actions taken by your customers and recurring crons/maintenance jobs that run on your
                            installation.
                        </p>
                    </div>
					<?php
				}
				);
			}
		}
		);

		add_action( 'admin_menu', [ $this, 'dollie_tools' ], 99 );
		add_action( 'admin_menu', [ $this, 'dollie_integrations' ], 99 );
		add_filter( 'admin_body_class', [ $this, 'add_staging_body_class' ] );
	}

	public function is_live() {
		return dollie()->is_live();
	}

	public function deactivate_admin_menu_editor() {
		$url = get_option( 'siteurl' );
		if ( $this->is_live() && strpos( $url, 'dollie.io' ) ) {
			deactivate_plugins( 'admin-menu-editor-pro/menu-editor.php' );
		}
	}

	public function add_a_test_acf_options_page() {
		if ( ! function_exists( 'acf_add_options_page' ) ) {
			return;
		}

		if ( $this->is_live() ) {
			$title = 'Dollie (Live)';
		} else {
			$title = 'Dollie (Staging)';
		}

		$args = [
			'page_title'  => 'Settings',
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

	public function add_a_test_menu_page() {

		if ( $this->is_live() ) {
			$title = 'Dollie (Live)';
		} else {
			$title = 'Dollie (Staging)';
		}

		$menu_title = 'Settings';
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

	public function remove_duplicate_admin_menu() {
		global $menu;
		foreach ( $menu as $key => $values ) {
			if ( $values[2] === 'wpd_platform_setup' ) {
				unset( $menu[ $key ] );
				break;
			}
		}
	}

	public function remove_duplicate_integration_menu() {
		global $menu;
		foreach ( $menu as $key => $values ) {
			if ( $values[2] === 'dollie-integrations' ) {
				unset( $menu[ $key ] );
				break;
			}
		}
	}

	public function add_theme_menu_item() {
		add_submenu_page(
			'wpd_platform_setup',
			'New Container',
			'New Container',
			'manage_options',
			'wpd_launch_site',
			'wpd_launch_site'
		);
	}

	public function dollie_adminbar_menu() {
		global $wp_admin_bar;
		$launch_site = dollie()->get_launch_page_id();

		$iconurl = DOLLIE_URL . 'assets/img/active.png';

		if ( $this->is_live() ) {
			$menu_title = 'Dollie (Live)';
		} else {
			$menu_title = 'Dollie (Staging)';
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
        background-image:url("' . $iconurl . '");"></span>';

		$title = $menu_title;

		$menu_id = 'dab';

		$wp_admin_bar->add_menu(
			[
				'id'    => $menu_id,
				'title' => $iconspan . $title,
				'href'  => '/',
			]
		);

		if ( ! $this->is_live() ) {
			$wp_admin_bar->add_menu(
				[
					'parent' => $menu_id,
					'title'  => __( 'Apply to go Live' ),
					'id'     => 'dwb-go-live',
					'href'   => 'https://partners.getdollie.com/go-live',
				]
			);
		}

		$wp_admin_bar->add_menu(
			[
				'parent' => $menu_id,
				'title'  => __( 'View Containers' ),
				'id'     => 'dab-site',
				'href'   => get_admin_url() . 'edit.php?post_type=container',
				'meta'   => [ 'target' => '' ],
			]
		);

		$wp_admin_bar->add_menu(
			[
				'parent' => $menu_id,
				'title'  => __( 'Integrations' ),
				'id'     => 'dab-integrations',
				'href'   => get_admin_url() . 'admin.php?page=dollie-integrations',
				'meta'   => [ 'target' => '' ],
			]
		);

		$wp_admin_bar->add_menu(
			[
				'parent' => $menu_id,
				'title'  => __( 'Visit Partner Dashboard' ),
				'id'     => 'dwb-partner',
				'href'   => 'https://partners.getdollie.com',
			]
		);

		$wp_admin_bar->add_menu(
			[
				'parent' => $menu_id,
				'title'  => __( 'Join us on Slack' ),
				'id'     => 'dwb-slack',
				'href'   => 'https://dollie-hub.herokuapp.com/',
			]
		);

		$wp_admin_bar->add_menu(
			[
				'parent' => $menu_id,
				'title'  => __( 'Settings' ),
				'id'     => 'dab-settings',
				'href'   => get_admin_url() . 'admin.php?page=wpd_platform_setup',
				'meta'   => [ 'target' => '' ],
			]
		);

		$wp_admin_bar->add_menu(
			[
				'parent' => $menu_id,
				'title'  => __( 'View Logs' ),
				'id'     => 'dab-logs',
				'href'   => get_admin_url() . 'edit.php?post_type=dollie-logs',
				'meta'   => [ 'target' => '' ],
			]
		);

		$wp_admin_bar->add_menu(
			[
				'parent' => $menu_id,
				'title'  => __( 'Launch Site' ),
				'id'     => 'dwb-launch',
				'href'   => '?page_id' . $launch_site,
			]
		);
	}

	public function dollie_tools() {
		add_submenu_page(
			'wpd_platform_setup',
			'Tools',
			'Tools',
			'manage_options',
			'wpd_tools',
			[ $this, 'dollie_tools_content' ]
		);
	}

	public function dollie_integrations() {
		add_submenu_page(
			'wpd_platform_setup',
			'<span class="breaking-news-toggle">Integrations</span>',
			'<span class="dashicons dashicons-awards"></span> Integrations',
			'manage_options',
			'dollie-integrations',
			'dollie-integrations'
		);
	}

	public function dollie_tools_content() {
		// Markup for synchronize button.
		echo '<div class="dollie-notice">
				<h1><span class="dashicons dashicons-admin-tools"></span>Dollie Tools</h1>
			</div>
			<div>
				<br><br>
				<label><strong>Synchronize Your Deployed Containers</strong></label><br><br>
				<form method="post"><input type="submit" name="synchronize" class="button" value="Start Sync!" /></form>
				<p>By clicking the button below you can synchronize all containers that have been deployed through this installation. This is especially useful if you have accidentally lost data or simply wanted to re-import your deployed containers in a fresh Dollie installation.</p>
			</div>';

		// If synchronize button is clicked then call the function to perform task.
		if ( array_key_exists( 'synchronize', $_POST ) ) {
			$containers = ContainerManagement::instance()->sync_containers();

			if ( ! empty( $containers ) ) {

				// Display Synchronized container's details.
				echo 'Synchronized ' . count( $containers ) . ' containers<br><br><br>';

				$counter = 0;
				foreach ( $containers as $container ) {
					$full_url        = parse_url( $container['uri'] );
					$stripped_domain = explode( '.', $full_url['host'] );
					$name            = $stripped_domain[0];
					$counter ++;

					if ( $counter % 2 ) {
						$grid = 'even';
					} else {
						$grid = 'odd';
					}

					echo '<div class="synch-result ' . $grid . '">
						<div class="row"><div class="left">Name</div><div class="right">' . $name . '</div></div>
						<div class="row"><div class="left">URL</div><div class="right">' . $container['uri'] . '</div></div>
						<div class="row"><div class="left">Status</div><div class="right">' . $container['status'] . '</div></div>
					</div>';
				}
			} else {
				echo '<p>No containers found to be synchronized.</p>';
			}

		}
	}

	public function add_staging_body_class( $classes ) {
		if ( ! $this->is_live() ) {
			$classes .= ' dollie_is_staging';
		}

		return $classes;
	}

}
