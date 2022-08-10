<?php


namespace Dollie\Core\Admin;

use Dollie\Core\Singleton;
use Dollie\Core\Utils\ConstInterface;
use Dollie\Core\Services\AuthService;
use Dollie\Core\Services\WorkspaceService;
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

		if ( dollie()->auth()->is_connected() ) {
			add_action( 'init', [ $this, 'register_post_type' ], 0 );

			add_action( 'admin_menu', [ $this, 'add_staging_menu_page' ], 1 );
			add_action( 'admin_menu', [ $this, 'submenus' ], 99 );
			add_action( 'admin_menu', [ $this, 'add_extra_menu_links' ], 100 );
		}

		add_filter( 'page_row_actions', [ $this, 'admin_alter_container_actions' ], 10, 2 );

		add_action( 'bulk_actions-edit-container', [ $this, 'admin_remove_container_actions' ] );
		add_action( 'admin_footer', [ $this, 'external_menu_scripts' ] );
		add_action( 'wp_before_admin_bar_render', [ $this, 'set_admin_bar_menu' ], 2000 );

		add_action( 'admin_init', [ WorkspaceService::instance(), 'check_deployment_domain' ] );
		add_action( 'admin_init', [ AuthService::instance(), 'process_token' ] );
		add_action( 'admin_init', [ $this, 'disconnect_dollie' ] );

		add_filter( 'manage_container_posts_columns', [ $this, 'set_table_columns' ] );
		add_action( 'manage_container_posts_custom_column', [ $this, 'set_table_custom_columns' ], 10, 2 );

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
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'menu_position'       => 2,
			'show_in_admin_bar'   => false,
			'show_in_nav_menus'   => false,
			'can_export'          => false,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => true,
			'menu_icon'           => 'dashicons-cloud',
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
			'capabilities'        => array(
				'create_posts' => 'do_not_allow',
			),
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
	public function submenus() {
		if ( ! defined( 'DOLLIE_DEV' ) || ! DOLLIE_DEV ) {
			return;
		}

		add_submenu_page(
			self::PANEL_SLUG,
			'Forms',
			'Forms',
			'manage_options',
			'edit.php?post_type=af_form'
		);
	}

	/**
	 * Force external menu to open in new tab
	 *
	 * @return void
	 */
	public function external_menu_scripts() {       ?>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				$('.dol-cloud-url').each(function(i, item) {
					$(item).parent().attr('target', '_blank');
				});
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
				'title'  => esc_html__( 'Overview', 'dollie' ),
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

		if ( $sites_url = dollie()->page()->get_sites_url() ) {
			$wp_admin_bar->add_menu(
				[
					'parent' => $menu_id,
					'title'  => esc_html__( 'View Sites', 'dollie' ),
					'id'     => 'dab-site',
					'href'   => $sites_url,
					'meta'   => [ 'target' => '' ],
				]
			);
		}

		if ( $launch_site_url = dollie()->page()->get_launch_site_url() ) {
			$wp_admin_bar->add_menu(
				[
					'parent' => $menu_id,
					'title'  => sprintf( esc_html__( 'Launch New %s', 'dollie' ), dollie()->string_variants()->get_site_type_string() ),
					'id'     => 'dwb-launch',
					'href'   => $launch_site_url,
				]
			);
		}

		if ( defined( 'S5_APP_TOKEN' ) ) {
			$wp_admin_bar->add_menu(
				[
					'parent' => 'top-secondary',
					'title'  => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
</svg> Dollie Cloud - Hosted for Free',
					'id'     => 'dwb-hosted',
					'href'   => 'https://cloud.getdollie.com/?redirect=dollie-site-redirect',
				]
			);

			$wp_admin_bar->add_menu(
				[
					'parent' => 'dwb-hosted',
					'title'  => esc_html__( 'Manage Your Site', 'dollie' ),
					'id'     => 'dab-hosted-manage',
					'href'   => 'https://cloud.getdollie.com/?redirect=dollie-site-redirect',
					'meta'   => [ 'target' => '_blank' ],
				]
			);

			$wp_admin_bar->add_menu(
				[
					'parent' => 'dwb-hosted',
					'title'  => esc_html__( 'View Documentation', 'dollie' ),
					'id'     => 'dab-hosted-docs',
					'href'   => 'https://cloud.getdollie.com/?redirect=knowledge-base',
					'meta'   => [ 'target' => '_blank' ],
				]
			);

			$wp_admin_bar->add_menu(
				[
					'parent' => 'dwb-hosted',
					'title'  => esc_html__( 'This site is hosted for free on the Dollie Cloud. Visit the Partner Dashboard to manage this site and access our developer tools.', 'dollie' ),
					'id'     => 'dab-hosted-message',
					'href'   => 'https://cloud.getdollie.com/?redirect=dollie-site-redirect',
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

		if ( $blueprints_url = dollie()->page()->get_sites_url( '', [ 'blueprint' => 'yes' ] ) ) {
			$wp_admin_bar->add_menu(
				[
					'parent' => $menu_id,
					'title'  => esc_html__( 'View Blueprints', 'dollie' ),
					'id'     => 'dab-view-blueprints',
					'href'   => $blueprints_url,
					'meta'   => [ 'target' => '' ],
				]
			);
		}

		if ( $launch_blueprint_url = dollie()->page()->get_launch_blueprint_url() ) {
			$wp_admin_bar->add_menu(
				[
					'parent' => $menu_id,
					'title'  => esc_html__( 'Launch New Blueprint', 'dollie' ),
					'id'     => 'dab-launch-blueprint',
					'href'   => $launch_blueprint_url,
					'meta'   => [ 'target' => '' ],
				]
			);
		}

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
				'title'  => esc_html__( 'Dollie Cloud', 'dollie' ),
				'id'     => 'dwb-partner',
				'href'   => 'https://cloud.getdollie.com',
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
	}

	/**
	 * Add external links menu
	 */
	public function add_extra_menu_links() {
		global $submenu;

		$submenu['dollie_setup'][] = [
			__( 'Your Hub', 'dollie' ),
			'manage_options',
			'',
			'',
			'dol-divider-menu',
		];

		$submenu['dollie_setup'][] = [
			esc_html__( 'Dashboard', 'dollie' ),
			'manage_options',
			dollie()->page()->get_dashboard_url(),
		];

		$submenu['dollie_setup'][] = [
			esc_html__( 'Customers', 'dollie' ),
			'manage_options',
			dollie()->page()->get_customers_url(),
		];

		$submenu['dollie_setup'][] = [
			esc_html__( 'Sites', 'dollie' ),
			'manage_options',
			dollie()->page()->get_sites_url(),
		];

		$submenu['dollie_setup'][] = [
			esc_html__( 'Blueprints', 'dollie' ),
			'manage_options',
			dollie()->page()->get_blueprints_url(),
		];

		$submenu['dollie_setup'][] = [
			__( 'Support', 'dollie' ),
			'manage_options',
			'',
			'',
			'dol-divider-menu',
		];

		$submenu['dollie_setup'][] = [
			'<div class="dol-cloud-url">' . esc_html__( 'Dollie Cloud', 'dollie' ) . '</div>',
			'manage_options',
			'https://cloud.getdollie.com/',
		];

		$submenu['dollie_setup'][] = [
			'<div class="dol-cloud-url">' . esc_html__( 'Contact Support', 'dollie' ) . '</div>',
			'manage_options',
			'https://cloud.getdollie.com/?redirect=support',
		];

		$submenu['dollie_setup'][] = [
			'<div class="dol-cloud-url">' . esc_html__( 'Documentation', 'dollie' ) . '</div>',
			'manage_options',
			'https://cloud.getdollie.com/knowledge-base',
		];

		$submenu['dollie_setup'][] = [
			'<div id="dol-logs">' . esc_html__( 'Logs', 'dollie' ) . '</div>',
			'manage_options',
			get_admin_url() . 'edit.php?post_type=dollie-logs',
		];
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
	 * Alter container actions
	 *
	 * @param [type] $actions
	 * @param [type] $page_object
	 * @return array
	 */
	public function admin_alter_container_actions( $actions, $page_object ) {
		if ( 'container' !== get_post_type() ) {
			return $actions;
		}

		$container = dollie()->get_container( $page_object );

		if ( is_wp_error( $container ) ) {
			return $actions;
		}

		$actions = [];

		if ( ! $container->is_blueprint() ) {
			$actions['manage_site'] = '<a href="' . $container->get_permalink() . '" target="_blank" class="button-link">' . __( 'Manage Site', 'dollie' ) . '</a>';
		} else {
			$actions['manage_site'] = '<a href="' . $container->get_permalink() . '" target="_blank" class="button-link">' . __( 'Manage Blueprint', 'dollie' ) . '</a>';
		}

		return $actions;
	}

	/**
	 * Remove container actions
	 *
	 * @param [type] $actions
	 * @return array
	 */
	public function admin_remove_container_actions( $actions ) {
		return [];
	}

	/**
	 * Set table columns
	 *
	 * @param array $columns
	 * @return array
	 */
	public function set_table_columns( array $columns ) {
		return array_merge(
			$columns,
			[
				'type'   => __( 'Type', 'dollie' ),
				'domain' => __( 'Domain', 'dollie' ),
				'status' => __( 'Status', 'dollie' ),
			]
		);
	}

	/**
	 * Set table custom columns values
	 *
	 * @param string  $column_name
	 * @param integer $post_id
	 * @return void
	 */
	public function set_table_custom_columns( string $column_name, int $post_id ) {
		if ( 'container' !== get_post_type() ) {
			return;
		}

		$container = dollie()->get_container( $post_id );

		if ( is_wp_error( $container ) ) {
			return;
		}

		if ( 'type' === $column_name ) {
			if ( $container->is_site() ) {
				esc_html_e( 'Site', 'dollie' );
			} elseif ( $container->is_blueprint() ) {
				esc_html_e( 'Blueprint', 'dollie' );
			}
		}

		if ( 'domain' === $column_name ) {
			echo "<a href=\"{$container->get_url( true )}\" target=\"_blank\">{$container->get_url()}</a>";
		}

		if ( 'status' === $column_name ) {
			echo $container->get_status();
		}
	}
}
