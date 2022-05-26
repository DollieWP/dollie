<?php


namespace Dollie\Core\Admin;

use Dollie\Core\Singleton;
use Dollie\Core\Utils\ConstInterface;
use Dollie\Core\Services\AuthService;
use Dollie\Core\Services\NoticeService;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class Container extends Singleton implements ConstInterface {
	/**
	 * Container constructor
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'init', [ $this, 'register_post_type' ], 0 );

		if ( dollie()->is_api_connected() ) {
			add_action( 'admin_menu', [ $this, 'add_staging_menu_page' ], 1 );
			add_action( 'admin_menu', [ $this, 'dollie_submenus' ], 99 );
			add_action( 'admin_menu', [ $this, 'dollie_setup_submenus' ], 1 );
			add_action( 'admin_menu', [ $this, 'add_external_menu_links' ], 100 );
		}

		add_action( 'admin_menu', [ $this, 'remove_duplicate_admin_menu' ], 100 );
		add_action( 'admin_footer', [ $this, 'external_menu_scripts' ] );
		add_action( 'wp_before_admin_bar_render', [ $this, 'set_admin_bar_menu' ], 2000 );
		add_action( 'load-edit.php', [ $this, 'add_info_banners' ] );

		add_filter( 'current_screen', [ $this, 'container_counter' ] );
		add_filter( 'views_edit-container', [ $this, 'container_filters' ] );

		add_action( 'admin_init', [ AuthService::instance(), 'process_token' ] );
		add_action( 'init', [ $this, 'check_deployment_domain_status' ] );
		add_action( 'admin_init', [ $this, 'disconnect_dollie' ] );
		add_filter( 'admin_body_class', [ $this, 'add_container_type_class' ] );

		add_filter( 'manage_container_posts_columns', [ $this, 'set_table_columns' ] );
		add_action( 'manage_container_posts_custom_column', [ $this, 'set_table_custom_columns' ], 10, 2 );

		add_action( 'add_meta_boxes', [ $this, 'rename_meta_box_title' ] );
		add_filter( 'parse_query', [ $this, 'filter_containers' ] );
		add_filter( 'page_row_actions', [ $this, 'add_actions' ], 10, 2 );

		add_action( 'before_delete_post', [ $this, 'undeploy_container' ] );
		add_action( 'untrashed_post', [ $this, 'restore_container' ] );
		add_action( 'wp_trash_post', [ $this, 'stop_container' ] );

		add_filter( 'gettext', [ $this, 'override_empty_trash' ], 50, 3 );

		add_action( 'edit_form_after_title', [ NoticeService::instance(), 'container_manager' ] );
	}

	/**
	 * Register container post type & taxonomy
	 */
	public function register_post_type() {
		$post_type_labels = [
			'name'                  => _x( 'Sites', 'dollie', 'dollie' ),
			'singular_name'         => _x( 'Site', 'dollie', 'dollie' ),
			'menu_name'             => __( 'Sites', 'dollie' ),
			'name_admin_bar'        => __( 'Post Type', 'dollie' ),
			'archives'              => __( 'Site Archives', 'dollie' ),
			'attributes'            => __( 'Site Attributes', 'dollie' ),
			'parent_item_colon'     => __( 'Parent Site:', 'dollie' ),
			'all_items'             => __( 'View Sites', 'dollie' ),
			'add_new_item'          => __( 'Add New Site', 'dollie' ),
			'add_new'               => __( 'Add New', 'dollie' ),
			'new_item'              => __( 'New Site', 'dollie' ),
			'edit_item'             => __( 'Edit Site', 'dollie' ),
			'update_item'           => __( 'Update Site', 'dollie' ),
			'view_item'             => __( 'View Site', 'dollie' ),
			'view_items'            => __( 'View Sites', 'dollie' ),
			'search_items'          => __( 'Search Sites', 'dollie' ),
			'not_found'             => __( 'Not found', 'dollie' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'dollie' ),
			'featured_image'        => __( 'Featured Image', 'dollie' ),
			'set_featured_image'    => __( 'Set featured image', 'dollie' ),
			'remove_featured_image' => __( 'Remove featured image', 'dollie' ),
			'use_featured_image'    => __( 'Use as featured image', 'dollie' ),
			'insert_into_item'      => __( 'Insert into item', 'dollie' ),
			'uploaded_to_this_item' => __( 'Uploaded to this item', 'dollie' ),
			'items_list'            => __( 'Sites list', 'dollie' ),
			'items_list_navigation' => __( 'Sites list navigation', 'dollie' ),
			'filter_items_list'     => __( 'Filter items list', 'dollie' ),
		];

		$post_type_args = [
			'label'               => __( 'Site', 'dollie' ),
			'description'         => __( 'Platform Sites', 'dollie' ),
			'labels'              => $post_type_labels,
			'supports'            => [ 'title', 'content', 'author', 'custom-fields', 'thumbnail', 'page-attributes' ],
			'taxonomies'          => [ 'container_category', 'container_tag' ],
			'hierarchical'        => true,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'menu_position'       => -10,
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => true,
			'menu_icon'           => 'dashicons-image-filter',
			'query_var'           => 'site',
			'rewrite'             => [
				'slug'       => 'site',
				'with_front' => true,
				'pages'      => true,
				'feeds'      => false,
			],
			'show_in_rest'        => false,
			'capability_type'     => 'wpd_site',
			'map_meta_cap'        => true,
		];

		$post_type_args = apply_filters( 'dollie/cpt/container/args', $post_type_args );

		$taxonomy_labels = [
			'name'                       => _x( 'Blueprint Categories', 'Taxonomy General Name', 'dollie' ),
			'singular_name'              => _x( 'Blueprint Category', 'Taxonomy Singular Name', 'dollie' ),
			'menu_name'                  => __( 'Taxonomy', 'dollie' ),
			'all_items'                  => __( 'All Items', 'dollie' ),
			'parent_item'                => __( 'Parent Item', 'dollie' ),
			'parent_item_colon'          => __( 'Parent Item:', 'dollie' ),
			'new_item_name'              => __( 'New Item Name', 'dollie' ),
			'add_new_item'               => __( 'Add New Item', 'dollie' ),
			'edit_item'                  => __( 'Edit Item', 'dollie' ),
			'update_item'                => __( 'Update Item', 'dollie' ),
			'view_item'                  => __( 'View Item', 'dollie' ),
			'separate_items_with_commas' => __( 'Separate items with commas', 'dollie' ),
			'add_or_remove_items'        => __( 'Add or remove items', 'dollie' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'dollie' ),
			'popular_items'              => __( 'Popular Items', 'dollie' ),
			'search_items'               => __( 'Search Items', 'dollie' ),
			'not_found'                  => __( 'Not Found', 'dollie' ),
			'no_terms'                   => __( 'No items', 'dollie' ),
			'items_list'                 => __( 'Items list', 'dollie' ),
			'items_list_navigation'      => __( 'Items list navigation', 'dollie' ),
		];

		$taxonomy_args = [
			'labels'            => $taxonomy_labels,
			'hierarchical'      => false,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => false,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => true,
		];

		register_post_type( 'container', $post_type_args );
		register_taxonomy( 'container_category', [ 'container' ], $taxonomy_args );
	}

	/**
	 * Check domain availability
	 */
	public function check_deployment_domain_status() {
		if ( ! dollie()->is_api_connected() || ! is_admin() ) {
			return;
		}

		$domain        = get_option( 'wpd_deployment_domain' );
		$domain_status = get_option( 'wpd_deployment_domain_status' );
		$delay_status  = get_option( 'wpd_deployment_delay_status' );

		if ( $domain && ! $domain_status ) {
			if ( $delay_status && ! get_transient( 'wpd_deployment_domain_delay' ) ) {
				update_option( 'wpd_deployment_domain_status', true );
				delete_option( 'wpd_deployment_delay_status' );
			} elseif ( ! $delay_status ) {
				// $response = Api::post( Api::ROUTE_DOMAIN_CHECK_DEPLOYMENT_ZONE );

				// if ( $response['domain'] === $domain && $response['status'] ) {
				// set_transient( 'wpd_deployment_domain_delay', true, 5 * MINUTE_IN_SECONDS );
				// update_option( 'wpd_deployment_delay_status', true );
				// }
			}
		}
	}

	/**
	 * Disconnect from API
	 */
	public function disconnect_dollie() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( isset( $_GET['disconnect_dollie'] ) ) {
			AuthService::instance()->delete_token();

			wp_redirect( admin_url() );
			exit;
		}
	}

	/**
	 * Test menu page
	 */
	public function add_staging_menu_page() {
		add_menu_page(
			__( 'Dollie <span class="dol-status dol-staging">Staging<span>', 'dollie' ),
			dollie()->is_live() ?
			__( 'Settings', 'dollie' ) :
			__( 'Dollie Hub Setup', 'dollie' ),
			'manage_options',
			self::PANEL_SLUG,
			'',
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
	}

	/**
	 * Setup submenus
	 */
	public function dollie_setup_submenus() {
		if ( dollie()->is_api_connected() ) {
			add_submenu_page(
				self::PANEL_SLUG,
				'Dashboard',
				'Dashboard',
				'manage_options',
				'admin.php?page=dollie_setup'
			);
		}
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

        if (!defined('DOLLIE_DEV') || !DOLLIE_DEV) {
            foreach ($menu as $key => $values) {
                if ('edit.php?post_type=af_form' === $values[2]) {
                    unset($menu[ $key ]);
                    break;
                }
            }
        }
	}

	/**
	 * Force external menu to open in new tab
	 *
	 * @return void
	 */
	public function external_menu_scripts() {       ?>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				$('#dol-url-partner-dashboard').parent().attr('target', '_blank');
				$('#dol-url-support').parent().attr('target', '_blank');
			});
		</script>
		<?php
	}

	/**
	 * Admin bar menu
	 */
	public function set_admin_bar_menu() {
		global $wp_admin_bar;

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$launch_site = dollie()->page()->get_launch_id();

		$iconurl = DOLLIE_URL . 'assets/img/active.png';


		if ( dollie()->get_partner_status() == 'trial' ) {
			$menu_title = '(Trial)';
		} elseif ( dollie()->get_partner_status() == 'staging' ) {
		   $menu_title = '(Staging)';
		} else {
			$menu_title = '(Live)';
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

		$title = 'Dollie ' . $menu_title;

		$menu_id = 'dab-' . dollie()->get_partner_status();

		$wp_admin_bar->add_menu(
			[
				'id'    => $menu_id,
				'title' => $iconspan . $title,
				'href'  => '/',
			]
		);

		if ( ! dollie()->is_live() ) {
			$wp_admin_bar->add_menu(
				array(
					'parent' => $menu_id,
					'title'  => __( 'Connect with Dollie Cloud', 'dollie' ),
					'id'     => 'dwb-go-live',
					'href'   => \Dollie\Core\Services\AuthService::instance()->get_auth_url(),
				)
			);
		}

		$wp_admin_bar->add_menu(
			[
				'parent' => $menu_id,
				'title'  => esc_html__( 'Dashboard', 'dollie' ),
				'id'     => 'dab-dashboard',
				'href'   => get_admin_url() . 'admin.php?page=dollie_setup',
				'meta'   => [ 'target' => '' ],
			]
		);

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
				'title'  => esc_html__( 'Sites', 'dollie' ),
				'id'     => 'dab-sites',
				'href'   => '',
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

		if ( $launch_site ) {
			$wp_admin_bar->add_menu(
				[
					'parent' => $menu_id,
					'title'  => esc_html__( 'Launch New ' . dollie()->string_variants()->get_site_type_string(), 'dollie' ),
					'id'     => 'dwb-launch',
					'href'   => get_permalink( $launch_site ),
				]
			);
		}

		if (defined('S5_APP_TOKEN')) {
			$wp_admin_bar->add_menu(
				[
					'parent' => 'top-secondary',
					'title'  => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
</svg> Dollie Cloud - Hosted for Free',
					'id'     => 'dwb-hosted',
					'href'   => 'https://partners.getdollie.com/?redirect=dollie-site-redirect',
				]
			);


			$wp_admin_bar->add_menu(
						[
							'parent' => 'dwb-hosted',
							'title'  => esc_html__( 'Manage Your Site', 'dollie' ),
							'id'     => 'dab-hosted-manage',
							'href'   => 'https://partners.getdollie.com/?redirect=dollie-site-redirect',
							'meta'   => [ 'target' => '_blank' ],
						]
					);

			$wp_admin_bar->add_menu(
						[
							'parent' => 'dwb-hosted',
							'title'  => esc_html__( 'View Documentation', 'dollie' ),
							'id'     => 'dab-hosted-docs',
							'href'   => 'https://partners.getdollie.com/?redirect=knowledge-base',
							'meta'   => [ 'target' => '_blank' ],
						]
					);

			$wp_admin_bar->add_menu(
				[
					'parent' => 'dwb-hosted',
					'title'  => esc_html__( 'This site is hosted for free on the Dollie Cloud. Visit the Partner Dashboard to manage this site and access our developer tools.', 'dollie' ),
					'id'     => 'dab-hosted-message',
					'href'   => 'https://partners.getdollie.com/?redirect=dollie-site-redirect',
					'meta'   => [ 'target' => '_blank' ],
				]
			);


		}


		$wp_admin_bar->add_menu(
			[
				'parent' => $menu_id,
				'title'  => esc_html__( 'Blueprints', 'dollie' ),
				'id'     => 'dab-blueprints',
				'href'   => '',
				'meta'   => [ 'target' => '' ],
			]
		);

		$wp_admin_bar->add_menu(
			[
				'parent' => $menu_id,
				'title'  => esc_html__( 'View Blueprints', 'dollie' ),
				'id'     => 'dab-view-blueprints',
				'href'   => get_admin_url() . 'edit.php?post_type=container&blueprint=yes',
				'meta'   => [ 'target' => '' ],
			]
		);

		$launch_blueprint_site = dollie()->page()->get_launch_blueprint_id();
		$wp_admin_bar->add_menu(
			[
				'parent' => $menu_id,
				'title'  => esc_html__( 'Launch Blueprint', 'dollie' ),
				'id'     => 'dab-launch-blueprint',
				'href'   => get_permalink( $launch_blueprint_site ),
				'meta'   => [ 'target' => '' ],
			]
		);




		$wp_admin_bar->add_menu(
			[
				'parent' => $menu_id,
				'title'  => esc_html__( 'Support', 'dollie' ),
				'id'     => 'dab-support',
				'href'   => '',
				'meta'   => [ 'target' => '' ],
			]
		);

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

		$submenu['dollie_setup'][] = [
			__( 'Sites', 'dollie' ),
			'manage_options',
			'',
			'',
			'dol-divider-menu',
		];

		$submenu['dollie_setup'][] = [
			esc_html__( 'View Sites', 'dollie' ),
			'manage_options',
			get_admin_url() . 'edit.php?post_type=container',
		];

		if ( $launch_site = dollie()->page()->get_launch_id() ) {
			$submenu['dollie_setup'][] = [
				sprintf( esc_html__( 'Launch New %s', 'dollie-setup' ), dollie()->string_variants()->get_site_type_string() ),
				'manage_options',
				get_permalink( $launch_site ),
			];
		}

		$submenu['dollie_setup'][] = [
			__( 'Blueprints', 'dollie' ),
			'manage_options',
			'',
			'',
			'dol-divider-menu',
		];

		$submenu['dollie_setup'][] = [
			esc_html__( 'View Blueprints', 'dollie' ),
			'manage_options',
			get_admin_url() . 'edit.php?post_type=container&blueprint=yes',
		];

		if ( $launch_blueprint_site = dollie()->page()->get_launch_blueprint_id() ) {
			$submenu['dollie_setup'][] = [
				esc_html__( 'Launch Blueprint', 'dollie-setup' ),
				'manage_options',
				get_permalink( $launch_blueprint_site ),
			];
		}


		$submenu['dollie_setup'][] = [
			__( 'Support', 'dollie' ),
			'manage_options',
			'',
			'',
			'dol-divider-menu',
		];

		$submenu['dollie_setup'][] = [
			esc_html__( 'Support', 'dollie' ),
			'manage_options',
			'https://partners.getdollie.com/?redirect=support',
		];

		$submenu['dollie_setup'][] = [
			'<div id="dol-url-partner-dashboard">' . esc_html__( 'Documentation', 'dollie' ) . '</div>',
			'manage_options',
			'https://partners.getdollie.com/knowledge-base',
		];

		$submenu['dollie_setup'][] = [
			'<div id="dol-logs">' . esc_html__( 'Logs', 'dollie' ) . '</div>',
			'manage_options',
			get_admin_url() . 'edit.php?post_type=dollie-logs',
		];
	}

	/**
	 * Display info banners
	 *
	 * @return void
	 */
	public function add_info_banners() {
		$screen = get_current_screen();

		if ( 'edit-container' === $screen->id ) {
			add_action( 'all_admin_notices', [ $this, 'site_info_banner' ] );
			add_action( 'admin_footer', [ $this, 'admin_footer_wrap' ], 999999 );
		}

		if ( 'edit-dollie-logs' === $screen->id ) {
			add_action( 'all_admin_notices', [ $this, 'blueprint_info_banner' ] );
			add_action( 'admin_footer', [ $this, 'admin_footer_wrap' ], 999999 );
		}

		if ( 'edit-af_form' === $screen->id ) {
			add_action( 'admin_header', [ $this, 'dollie_footer_wrap' ] );
			add_action( 'admin_footer', [ $this, 'admin_footer_wrap' ], 999999 );
		}
	}

	/**
	 * Site info banner
	 *
	 * @return void
	 */
	public function site_info_banner() {
		?>
		<?php
		if ( empty( $_GET['blueprint'] ) ) :
			dollie_setup_get_template_part( 'wrapper-header' );
			?>
			<div class="dollie-page-intro">
				<h3>
					<?php esc_html_e( 'Sites Overview - All Sites in your Hub', 'dollie' ); ?>
				</h3>
				<p>
					<?php
					printf(
						'%s <a href="%s">%s</a>.',
						esc_html__( 'Below are all the sites that have been launched through your Hub. Each site is hosted under your own brand and domain inside your Dollie Cloud. Below uou can see their status, to which customer they are linked and whether they have a domain connected to them.', 'dollie' ),
						esc_url( dollie()->page()->get_sites_url() ),
						esc_html__( 'View Sites on the front-end of my Hub', 'dollie' )
					);
					?>
				</p>
			</div>
			<?php
		else :
			dollie_setup_get_template_part( 'wrapper-header' );
			?>
			<div class="dollie-page-intro">
				<h3>
					<?php esc_html_e( 'Your Site Blueprints', 'dollie' ); ?>
				</h3>
				<p>
					<?php
					printf(
						'%s <a href="%s">%s</a>.',
						esc_html__( 'Below you will find all the Blueprints you have created in your Hub. Click on the Blueprint to manage or update them.', 'dollie' ),
						esc_url( dollie()->page()->get_launch_blueprint_url() ),
						esc_html__( 'Launch a New Blueprint', 'dollie' )
					);
					?>
				</p>
			</div>
		<?php endif; ?>
		<?php
	}

	/**
	 * Blueprints info banner
	 *
	 * @return void
	 */
	public function blueprint_info_banner() {
		dollie_setup_get_template_part( 'wrapper-header' );
		?>
		<div class="dollie-page-intro">
			<h3>
				<?php esc_html_e( 'The Hub Log', 'dollie' ); ?>
			</h3>
			<p>
				<?php esc_html_e( 'The Hub log keeps track of activity inside your Hub, For example action taken by your customers and recurring crons/maintenance jobs that run behind the scenes.', 'dollie' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Blueprints info banner
	 *
	 * @return void
	 */
	public function admin_footer_wrap() {
		dollie_setup_get_template_part( 'wrapper-footer' );
	}

	/**
	 * Forms info banner
	 *
	 * @return void
	 */
	public function form_info_banner() {
		dollie_setup_get_template_part( 'wrapper-header' );
		?>
		<div class="dollie-page-intro">
			<h3>
				<?php esc_html_e( 'Dollie Forms', 'dollie' ); ?>
			</h3>
			<p>
				<?php esc_html_e( 'These forms can be embedded easily to further customize the experience for your customers. Please only edit these forms if you\'re a developer and you have good knowledge of the Hub and it\'s API.', 'dollie' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Update posts counter for blueprints
	 *
	 * @param obj $screen
	 * @return void
	 */
	public function container_counter( $screen ) {
		if ( 'edit-container' !== $screen->id ) {
			return;
		}

		add_filter(
			'wp_count_posts',
			static function ( $counts ) {
				$container_type = self::TYPE_SITE;

				if ( isset( $_GET['blueprint'] ) && ! empty( $_GET['blueprint'] ) ) {
					$container_type = self::TYPE_BLUEPRINT;
				}

				$args = [
					'posts_per_page' => -1,
					'post_type'      => 'container',
					'post_status'    => 'publish',
					'meta_query'     => [
						'relation' => 'OR',
						[
							'key'     => 'dollie_container_type',
							'value'   => $container_type,
							'compare' => '=',
						],
					],
				];

				$blueprints      = new \WP_Query( $args );
				$counts->publish = $blueprints->found_posts;

				return $counts;
			}
		);
	}

	/**
	 * Update filters for blueprints
	 *
	 * @param array $views
	 * @return array
	 */
	public function container_filters( $views ) {
		unset( $views['mine'] );

		return $views;
	}

	/**
	 * Add body container class
	 *
	 * @param string $classes
	 *
	 * @return string
	 */
	public function add_container_type_class( $classes ) {
		$container = dollie()->get_container();

		if ( is_wp_error( $container ) ) {
			return $classes;
		}

		if ( $container->is_site() ) {
			$classes .= ' dol-container-site';
		} elseif ( $container->is_blueprint() ) {
			$classes .= ' dol-container-blueprint';
		} elseif ( $container->is_staging() ) {
			$classes .= ' dol-container-staging';
		}

		return $classes;
	}

	/**
	 * Set table columns
	 *
	 * @param $columns
	 *
	 * @return array
	 */
	public function set_table_columns( array $columns ) {
		$custom_columns = [
			'cb' => $columns['cb'],
		];

		unset( $columns['cb'] );
		unset( $columns['title'] );

		if ( isset( $_GET['blueprint'] ) && ! empty( $_GET['blueprint'] ) ) {
			$custom_columns['container-title'] = __( 'Blueprint', 'dollie' );

			$columns = array_merge(
				$columns,
				[
					'blueprint-updated' => __( 'Updated', 'dollie' ),
					'size'              => __( 'Size', 'dollie' ),
					'status'            => __( 'Status', 'dollie' ),
				]
			);
		} else {
			$custom_columns['container-title'] = __( 'Site', 'dollie' );

			$columns = array_merge(
				$columns,
				[
					'domain' => __( 'Domain', 'dollie' ),
					'size'   => __( 'Size', 'dollie' ),
					'status' => __( 'Status', 'dollie' ),
				]
			);
		}

		return $custom_columns + $columns;
	}

	/**
	 * Set table custom columns
	 *
	 * @param string $column_name
	 * @param int    $post_id
	 */
	public function set_table_custom_columns( string $column_name, int $post_id ) {
		$container = dollie()->get_container( $post_id );

		if ( is_wp_error( $container ) ) {
			return;
		}

		?>
			<?php if ( 'container-title' === $column_name ) : ?>
				<a href="<?php echo get_edit_post_link( $container->get_id() ); ?>">
					<?php echo $container->get_title(); ?>
				</a>
				<div>
					<a target="_blank" href="<?php echo $container->get_url(); ?>">
						<span class="url-box"><?php echo $container->get_url(); ?></span>
					</a>
				</div>
			<?php endif; ?>

			<?php if ( 'blueprint-updated' === $column_name && $container->is_blueprint() ) : ?>
				<?php echo $container->get_changes_update_time(); ?>
			<?php endif; ?>

			<?php if ( 'size' === $column_name ) : ?>
				<?php echo $container->get_storage_size(); ?>
			<?php endif; ?>

			<?php if ( 'status' === $column_name ) : ?>
				<?php echo $container->get_status(); ?>
			<?php endif; ?>

			<?php if ( 'domain' === $column_name && $container->is_site() ) : ?>
				<?php echo $container->get_url(); ?>
			<?php endif; ?>
		<?php
	}

	/**
	 * Container author box
	 */
	public function rename_meta_box_title() {
		remove_meta_box( 'authordiv', 'container', 'core' );
		add_meta_box(
			'authordiv',
			__( 'Assigned Customer', 'dollie' ),
			'post_author_meta_box',
			'container',
			'normal',
			'high'
		);
	}

	/**
	 * Seperate Blueprints from regular sites.
	 *
	 * @param $query
	 */
	public function filter_containers( $query ) {
		if ( ! is_admin() || wp_doing_ajax() ) {
			return $query;
		}

		if ( ! isset( $_GET['post_type'] ) || 'container' !== $_GET['post_type'] ) {
			return $query;
		}

		$query->query_vars['meta_query'] = [];

		if ( isset( $_GET['blueprint'] ) ) {
			$query->query_vars['meta_query'][] = [
				'relation' => 'OR',
				[
					'key'   => 'dollie_container_type',
					'value' => '1',
				],
			];
		} else {
			$query->query_vars['meta_query'][] = [
				'relation' => 'OR',
				[
					'key'   => 'dollie_container_type',
					'value' => '0',
				],
			];
		}

		return $query;
	}

	/**
	 * Add custom actions for the site listing, and remove default ones.
	 *
	 * @param $actions
	 * @param $page_object
	 */
	public function add_actions( $actions, $page_object ) {
		$container = dollie()->get_container();

		// if ( is_wp_error( $container ) ) {
		// 	return $actions;
		// }

		unset( $actions['trash'] );
		unset( $actions['view'] );
		unset( $actions['inline hide-if-no-js'] );
		unset( $actions['edit'] );

		if ( empty( $_GET['blueprint'] ) ) {
			$actions['manage_site'] = '<a href="' . $container->get_permalink() . '" class="manage_site"><span class="dashicons dashicons-admin-tools"></span>' . __( 'Manage Site' ) . '</a>';
		} else {
			$actions['manage_site'] = '<a href="' . $container->get_permalink( 'blueprints' ) . '" class="manage_site"><span class="dashicons dashicons-admin-tools"></span>' . __( 'Manage Blueprint' ) . '</a>';
		}

		$actions['admin_link'] = '<a href="' . $container->get_login_url() . '" class="login_admin"><span class="dashicons dashicons-privacy"></span>' . __( 'Login to Installation' ) . '</a>';

		return $actions;
	}

	/**
	 * Undeploy container when deleting post
	 *
	 * @param int $post_id
	 */
	public function undeploy_container( int $post_id ) {
		$container = dollie()->get_container( $post_id );

		if ( is_wp_error( $container ) ) {
			return;
		}

		$container->undeploy();
	}

	/**
	 * Start container after post restore
	 *
	 * @param int $post_id
	 */
	public function restore_container( int $post_id ) {
		$container = dollie()->get_container( $post_id );

		if ( is_wp_error( $container ) ) {
			return;
		}

		$container->perform_action( self::ACTION_START );
	}

	/**
	 * Stop container after moving to trash
	 *
	 * @param int $post_id
	 */
	public function stop_container( int $post_id ) {
		$container = dollie()->get_container( $post_id );

		if ( is_wp_error( $container ) ) {
			return;
		}

		$container->perform_action( self::ACTION_STOP );
	}


	/**
	 * Override the "Empty Trash" string on admin pages for a custom post type
	 *
	 * @param string $translated_text translation of $text from previous filter calls
	 * @param string $text original text string
	 * @param string $domain translation domain
	 *
	 * @return string|void
	 */
	public function override_empty_trash( $translated_text, $text, $domain ) {
		if ( ! is_admin() ) {
			return $translated_text;
		}

		if ( ! function_exists( 'get_current_screen' ) ) {
			require_once ABSPATH . '/wp-admin/includes/screen.php';
		}

		$current_screen = get_current_screen();

		if ( isset( $current_screen ) && 'container' === $current_screen->post_type ) {
			if ( 'Empty Trash' === $text ) {
				$translated_text = __( 'Permanently delete sites', 'dollie' );
			} elseif ( 'Move to Trash' === $text ) {
				$translated_text = __( 'Stop', 'dollie' );
			}
		}

		return $translated_text;
	}
}
