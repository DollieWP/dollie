<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;

use Dollie\Core\Modules\Jobs\SyncContainersJob;

use Dollie\Core\Utils\Api;
use Dollie\Core\Utils\Tpl;

/**
 * Class Options
 *
 * @package Dollie\Core\Modules
 */
class Options extends Singleton {


	const PANEL_SLUG = 'wpd_platform_setup',
		API_SLUG     = 'wpd_api';

	/**
	 * Options constructor.
	 */
	public function __construct() {
		 parent::__construct();

		$api_menu_priority = 2;

		if ( $this->is_live() || defined( 'DOLLIE_DEV' ) && DOLLIE_DEV ) {
			$api_menu_priority = 99;
			add_action( 'init', [ $this, 'add_acf_options_page' ] );

			if ( is_admin() ) {
				add_action( 'acf/input/admin_head', [ $this, 'add_api_boxes_before' ], 1 );
			}
		}

		add_action( 'admin_menu', [ $this, 'add_staging_menu_page' ], 1 );
		add_action( 'admin_menu', [ $this, 'dollie_submenus' ], 99 );
		add_action( 'admin_menu', [ $this, 'dollie_submenu_api' ], $api_menu_priority );
		add_action( 'admin_menu', [ $this, 'add_external_menu_links' ], 100 );
		add_action( 'admin_menu', [ $this, 'remove_duplicate_admin_menu' ], 100 );
		add_action( 'admin_menu', [ $this, 'remove_duplicate_forms_menu' ], 100 );

		add_action( 'wp_before_admin_bar_render', [ $this, 'dollie_adminbar_menu' ], 2000 );
		add_filter( 'acf/load_field/name=wpd_api_domain', [ $this, 'dollie_domain_readonly' ] );

		add_action( 'load-edit.php', [ $this, 'add_info_banners' ] );

		add_filter( 'admin_body_class', [ $this, 'add_staging_body_class' ] );

		add_action( 'admin_init', [ Api::instance(), 'process_token' ] );
		add_action( 'admin_init', [ $this, 'disconnect_dollie' ] );
	}

	/**
	 * @param $field
	 *
	 * @return mixed
	 */
	public function dollie_domain_readonly( $field ) {
		$field['readonly'] = 1;

		return $field;
	}

	/**
	 * Disconnect from API
	 */
	public function disconnect_dollie() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( isset( $_GET['disconnect_dollie'] ) ) {
			Api::delete_auth_token();

			wp_redirect( admin_url( 'admin.php?page=' . self::PANEL_SLUG ) );
			exit;
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
	 * Test acf page option
	 */
	public function add_acf_options_page() {
		if ( ! function_exists( 'acf_add_options_page' ) ) {
			return;
		}

		$args = [
			'page_title'  => __( 'Settings', 'dollie' ),
			'menu_title'  => __( 'Dollie (Live)', 'dollie' ),
			'menu_slug'   => self::PANEL_SLUG,
			'capability'  => 'manage_options',
			'position'    => '4',
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
	public function add_staging_menu_page() {
		$page_title = __( 'Dollie (Staging)', 'dollie' );

		if ( $this->is_live() ) {
			$title    = __( 'Settings', 'dollie' );
			$callback = '';
		} else {
			$title    = $page_title;
			$callback = [ $this, 'dollie_api_content' ];
		}

		add_menu_page(
			$page_title,
			$title,
			'manage_options',
			self::PANEL_SLUG,
			$callback,
			'',
			'4.1'
		);
	}

	/**
	 * Submenus
	 */
	public function dollie_submenus() {
		if ( defined( 'DOLLIE_DEV' ) && DOLLIE_DEV ) {
			add_submenu_page(
				self::PANEL_SLUG,
				'Forms',
				'Forms',
				'manage_options',
				'edit.php?post_type=af_form'
			);
		}

		if ( defined( 'DOLLIE_DEV' ) && DOLLIE_DEV && $this->is_live() ) {
			add_submenu_page(
				self::PANEL_SLUG,
				'Tools',
				'Tools',
				'manage_options',
				'wpd_tools',
				[ $this, 'dollie_tools_content' ]
			);
		}
	}

	/**
	 * Api Submenu
	 */
	public function dollie_submenu_api() {
		$url = self::API_SLUG;

		if ( ! $this->is_live() ) {
			$url = self::PANEL_SLUG;
		}

		add_submenu_page(
			self::PANEL_SLUG,
			'Api',
			'API',
			'manage_options',
			$url,
			[ $this, 'dollie_api_content' ]
		);
	}

	/**
	 * Remove duplicated admin menu
	 */
	public function remove_duplicate_admin_menu() {
		 global $menu;
		$entries = [];
		foreach ( $menu as $key => $values ) {
			if ( self::PANEL_SLUG === $values[2] ) {
				$entries[] = $key;
			}
		}

		if ( count( $entries ) > 1 ) {
			unset( $menu[ $entries[0] ] );
		}
	}

	/**
	 * Remove duplicated forms menu
	 */
	public function remove_duplicate_forms_menu() {
		 global $menu;
		foreach ( $menu as $key => $values ) {
			if ( 'edit.php?post_type=af_form' === $values[2] ) {
				unset( $menu[ $key ] );
				break;
			}
		}
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
			$menu_title = __( 'Dollie (Live)', 'dollie' );
		} else {
			$menu_title = __( 'Dollie (Staging)', 'dollie' );
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
					'title'  => __( 'Connect with Dollie', 'dollie' ),
					'id'     => 'dwb-go-live',
					'href'   => \Dollie\Core\Plugin::instance()->get_api_access_url(),
				]
			);
		}

		$wp_admin_bar->add_menu(
			[
				'parent' => $menu_id,
				'title'  => esc_html__( 'Settings', 'dollie' ),
				'id'     => 'dab-settings',
				'href'   => get_admin_url() . 'admin.php?page=' . self::PANEL_SLUG,
				'meta'   => [ 'target' => '' ],
			]
		);

		$wp_admin_bar->add_menu(
			[
				'parent' => $menu_id,
				'title'  => esc_html__( 'View Sites', 'dollie' ),
				'id'     => 'dab-site',
				'href'   => get_admin_url() . 'edit.php?post_type=container',
				'meta'   => [ 'target' => '' ],
			]
		);

		$wp_admin_bar->add_menu(
			[
				'parent' => $menu_id,
				'title'  => esc_html__( 'View Blueprints', 'dollie' ),
				'id'     => 'dab-blueprints',
				'href'   => get_admin_url() . 'edit.php?post_type=container&blueprint=yes',
				'meta'   => [ 'target' => '' ],
			]
		);

		if ( $launch_site ) {
			$wp_admin_bar->add_menu(
				[
					'parent' => $menu_id,
					'title'  => esc_html__( 'Launch New Site', 'dollie' ),
					'id'     => 'dwb-launch',
					'href'   => get_permalink( $launch_site ),
				]
			);
		}

		$wp_admin_bar->add_menu(
			[
				'parent' => $menu_id,
				'title'  => esc_html__( 'Logs', 'dollie' ),
				'id'     => 'dab-logs',
				'href'   => get_admin_url() . 'edit.php?post_type=dollie-logs',
				'meta'   => [ 'target' => '' ],
			]
		);

		if ( defined( 'DOLLIE_DEV' ) && DOLLIE_DEV ) {
			$wp_admin_bar->add_menu(
				[
					'parent' => $menu_id,
					'title'  => esc_html__( 'Forms' ),
					'id'     => 'dab-forms',
					'href'   => get_admin_url() . 'edit.php?post_type=af_form',
					'meta'   => [ 'target' => '' ],
				]
			);
		}

		$wp_admin_bar->add_menu(
			[
				'parent' => $menu_id,
				'title'  => esc_html__( 'Visit Partner Dashboard', 'dollie' ),
				'id'     => 'dwb-partner',
				'href'   => 'https://partners.getdollie.com',
			]
		);
	}

	/**
	 * Add external links menu
	 */
	public function add_external_menu_links() {
		 global $submenu;

		$submenu[ self::PANEL_SLUG ][] = [
			esc_html__( 'Partner Dashboard', 'dollie' ),
			'manage_options',
			'https://partners.getdollie.com',
		];

		$submenu[ self::PANEL_SLUG ][] = [
			esc_html__( 'Support', 'dollie' ),
			'manage_options',
			'https://partners.getdollie.com/?redirect=support',
		];

		$launch_site = dollie()->get_launch_page_id();
		if ( $launch_site ) {

			array_splice(
				$submenu[ self::PANEL_SLUG ],
				2,
				0,
				[
					[
						esc_html__( 'Launch New Site', 'dollie' ),
						'manage_options',
						get_permalink( $launch_site ),
					],
				]
			);
		}
		array_splice(
			$submenu[ self::PANEL_SLUG ],
			2,
			0,
			[
				[
					esc_html__( 'View Blueprints', 'dollie' ),
					'manage_options',
					get_admin_url() . 'edit.php?post_type=container&blueprint=yes',
				],
			]
		);
	}

	/**
	 * Tools page content
	 */
	public function dollie_tools_content() {
		$containers = [];

		if ( array_key_exists( 'synchronize', $_POST ) ) {
			$containers = SyncContainersJob::instance()->run();
		}

		Tpl::load( 'admin/tools-page', [ 'containers' => $containers ], true );
	}

	/**
	 * Api page content
	 */
	public function dollie_api_content() {
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
		if ( $screen && 'toplevel_page_' . self::PANEL_SLUG === $screen->id ) {
			add_meta_box(
				'custom-mb-before-acf',
				'CUSTOM MB BEFORE ACF',
				[
					$this,
					'add_api_boxes_callback',
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
	public function add_api_boxes_callback( $post, $args = [] ) {
		$token  = Api::get_auth_token();
		$status = __( 'Inactive', 'dollie' );
		$class  = 'api-inactive';

		if ( $token ) {
			$status = __( 'Active', 'dollie' );
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
						<?php _e( 'Something went wrong. Please try again later or contact our support.', 'dollie' ); ?>
						<br>
						<br>
					<?php endif; ?>

					<?php if ( isset( $_GET['status'] ) && $_GET['status'] === 'not_connected' ) : ?>
						<?php _e( 'You are not connected with the Dollie API. Please follow the instructions at the top of the page to continue with the API authentication.', 'dollie' ); ?>
						<br>
						<br>
					<?php endif; ?>

					<br>

					<?php if ( ! \Dollie\Core\Utils\Api::get_auth_token() ) : ?>
						<?php echo \Dollie\Core\Plugin::instance()->get_api_access_link( true ); ?>
					<?php endif; ?>

				</div>
			</div>
		</div>

		<?php
	}

	public function add_info_banners() {
		$screen = get_current_screen();
		// Only edit post screen:
		if ( 'edit-container' === $screen->id ) {
			add_action(
				'all_admin_notices',
				static function () {
					if ( empty( $_GET['blueprint'] ) ) {
						?>
					<div class="dollie-notice">
						<h3>
							The Site Manager
						</h3>
						<p>
							<?php
							printf(
								'Below you will find all of your sites launched by you and your customers. Remember: You can also view all the sites launched by your customers in the <a href="%s">Sites Directory</a>.',
								esc_url( dollie()->get_sites_page_url() )
							);
							?>
						</p>
					</div>
				<?php } else { ?>
				<div class="dollie-notice">
					<h3>
						Your Site Blueprints
					</h3>
					<p>
						<?php
						printf(
							'Below you will find all the Blueprints you have created. Want to add a new Blueprint? <a href="%s">Launch a Blueprint</a>.',
							esc_url( dollie()->get_launch_blueprint_page_url() )
						);
						?>
					</p>
				</div>
						<?php
				}
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
}
