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
		add_action( 'init', array( $this, 'register_post_type' ), 0 );

		// if ( dollie()->auth()->is_connected() ) {

			add_action( 'admin_menu', array( $this, 'add_staging_menu_page' ), 1 );
			add_action( 'admin_menu', array( $this, 'submenus' ), 99 );
			add_action( 'admin_menu', array( $this, 'add_blueprint_submenu' ), 1 );
			add_action( 'admin_menu', array( $this, 'add_extra_menu_links' ), 100 );
			add_action( 'admin_footer', array( $this, 'crisp_support_js' ), 999999 );

		// }

		add_action( 'auth_redirect', array( $this, 'add_site_icon_filter' ) ); // modify esc_attr on auth_redirect
		add_action( 'admin_menu', array( $this, 'remove_site_icon_filter' ) ); // restore on admin_menu (very soon)

		add_filter( 'custom_menu_order', array( $this, 'custom_menu_order' ) );
		add_filter( 'menu_order', array( $this, 'custom_menu_order' ) );
		add_filter( 'manage_users_columns', array( $this, 'new_modify_user_table' ) );
		add_filter( 'manage_users_custom_column', array( $this, 'new_modify_user_table_row' ), 10, 3 );
		add_filter( 'page_row_actions', array( $this, 'alter_container_row_actions' ), 10, 2 );
		add_filter( 'bulk_actions-edit-container', array( $this, 'container_bulk_actions' ) );
		add_filter( 'handle_bulk_actions-edit-container', array( $this, 'handle_container_bulk_actions' ), 10, 3 );
		add_filter( 'current_screen', array( $this, 'container_counter' ) );
		add_filter( 'views_edit-container', array( $this, 'container_filters' ) );
		add_filter( 'parse_query', array( $this, 'filter_containers' ) );
		add_filter( 'pre_get_posts', array( $this, 'remove_default_search' ) );

		add_action( 'add_meta_boxes', array( $this, 'rename_meta_box_title' ) );
		add_action( 'restrict_manage_posts', array( $this, 'filter_by_author' ) );
		add_action( 'admin_footer', array( $this, 'external_menu_scripts' ) );
		add_action( 'wp_before_admin_bar_render', array( $this, 'set_admin_bar_menu' ), 2000 );
		add_action( 'load-edit.php', array( $this, 'add_info_banners' ) );
		add_action( 'admin_init', array( AuthService::instance(), 'process_token' ) );
		add_action( 'admin_init', array( $this, 'disconnect_dollie' ) );
		// Commented out to prevent deletion of containers when trashing a post.
		// add_action( 'trashed_post', [ $this, 'delete_container' ] );
		// add_action( 'untrash_post', [ $this, 'restore_container' ], 10, 2 );

		add_filter( 'manage_container_posts_columns', array( $this, 'set_table_columns' ) );
		add_action( 'manage_container_posts_custom_column', array( $this, 'set_table_custom_columns' ), 10, 2 );

		add_action( 'edit_form_after_title', array( NoticeService::instance(), 'container_manager' ) );
	}

	/**
	 * Register container post type & taxonomy
	 */
	public function register_post_type() {
		$post_type_labels = array(
			'name'                  => _x( 'Sites', 'dollie', 'dollie' ),
			'singular_name'         => _x( 'Site', 'dollie', 'dollie' ),
			'menu_name'             => __( '%%ICON%% Sites', 'dollie' ),
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
		);

		$post_type_args = array(
			'label'               => __( 'Site', 'dollie' ),
			'description'         => __( 'Platform Sites', 'dollie' ),
			'labels'              => $post_type_labels,
			'supports'            => array( 'title', 'content', 'author', 'custom-fields', 'thumbnail', 'page-attributes' ),
			'taxonomies'          => array( 'container_category', 'container_tag' ),
			'hierarchical'        => true,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 2,
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => true,
			'menu_icon'           => 'dashicons-cloud',
			'query_var'           => 'site',
			'rewrite'             => array(
				'slug'       => 'site',
				'with_front' => true,
				'pages'      => true,
				'feeds'      => false,
			),
			'show_in_rest'        => false,
			'capability_type'     => 'wpd_site',
			'map_meta_cap'        => true,
			'capabilities'        => array(
				'create_posts' => 'do_not_allow',
			),
		);

		$post_type_args = apply_filters( 'dollie/cpt/container/args', $post_type_args );

		$taxonomy_labels = array(
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
		);

		$taxonomy_args = array(
			'labels'            => $taxonomy_labels,
			'hierarchical'      => false,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => false,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => true,
		);

		register_post_type( 'container', $post_type_args );
		register_taxonomy( 'container_category', array( 'container' ), $taxonomy_args );
	}

	/**
	 * Disconnect from API
	 */
	public function disconnect_dollie() {

		$user = dollie()->get_user();

		if ( ! $user->can_manage_all_sites() ) {
			return;
		}

		if ( isset( $_GET['disconnect_dollie'] ) ) {
			AuthService::instance()->delete_token();

			wp_redirect( admin_url() );
			exit;
		}
	}

	/**
	 * Delete container
	 *
	 * @param $post_id
	 * @return void
	 */
	public function delete_container( $post_id ) {
		$container = dollie()->get_container( $post_id );

		if ( is_wp_error( $container ) ) {
			return;
		}

		$container->delete();
	}

	public function restore_container( $post_id, $previous_status ) {
		$container = dollie()->get_container( $post_id );

		if ( is_wp_error( $container ) ) {
			return;
		}

		$container->restore();
	}

	/**
	 * Test menu page
	 */
	public function add_staging_menu_page() {
		// add_menu_page(
		// __( 'Dollie <span class="dol-status dol-staging">Staging<span>', 'dollie' ),
		// dollie()->is_live() ?
		// __( 'Settings', 'dollie' ) :
		// __( 'Dollie Hub Setup', 'dollie' ),
		// 'manage_options',
		// self::PANEL_SLUG,
		// '',
		// '',
		// '4.1'
		// );
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
	 * Add blueprints menu item
	 *
	 * @return void
	 */
	public function add_blueprint_submenu() {
		add_menu_page(
			'admin_menu_add_blueprints',
			dollie()->icon()->blueprint() . 'Blueprints',
			'read',
			admin_url( 'edit.php?post_type=container&blueprint=yes' ),
			'',
			'',
			1
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
	 * Custom menu order
	 *
	 * @param [type] $menu_ord
	 * @return array
	 */
	public function custom_menu_order( $menu_ord ) {
		if ( ! $menu_ord ) {
			return true;
		}

		return array(
			'index.php',
			'separator1',
			'dollie_setup',
			'edit.php?post_type=container',
			// 'edit.php?post_type=container&blueprint=yes',
		);
	}

	/**
	 * Admin bar menu
	 */
	public function set_admin_bar_menu() {
		global $wp_admin_bar;

		$user = dollie()->get_user();

		if ( ! $user->can_manage_all_sites() ) {
			return;
		}

		$iconurl = DOLLIE_URL . 'assets/img/active.png';

		if ( 'trial' === dollie()->get_partner_status() ) {
			$menu_title = '(Trial)';
		} elseif ( 'unverified' === dollie()->get_partner_status() ) {
			$menu_title = '(Unverified)';
		} elseif ( 'staging' === dollie()->get_partner_status() ) {
			$menu_title = '(Staging)';
		} else {
			$menu_title = '(Connected)';
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
			array(
				'id'    => $menu_id,
				'title' => $iconspan . $title,
				'href'  => '/',
			)
		);

		if ( ! dollie()->is_live() ) {
			$wp_admin_bar->add_menu(
				array(
					'parent' => $menu_id,
					'title'  => __( 'Connect Hub to Control HQ', 'dollie' ),
					'id'     => 'dwb-go-live',
					'href'   => \Dollie\Core\Services\AuthService::instance()->get_auth_url(),
				)
			);
		}

		$wp_admin_bar->add_menu(
			array(
				'parent' => $menu_id,
				'title'  => esc_html__( 'Hub Dashboard', 'dollie' ),
				'id'     => 'dab-settings',
				'href'   => get_admin_url() . 'admin.php?page=' . self::PANEL_SLUG,
				'meta'   => array( 'target' => '' ),
			)
		);

		$wp_admin_bar->add_menu(
			array(
				'parent' => $menu_id,
				'title'  => esc_html__( 'View Hub in Control HQ', 'dollie' ),
				'id'     => 'dab-hosted-manage',
				'href'   => 'https://control.getdollie.com/hubs',
				'meta'   => array( 'target' => '_blank' ),
			)
		);

		$wp_admin_bar->add_menu(
			array(
				'parent' => $menu_id,
				'title'  => esc_html__( 'Useful Links', 'dollie' ),
				'id'     => 'dab-support',
				'href'   => '',
				'meta'   => array( 'target' => '' ),
			)
		);

		$wp_admin_bar->add_menu(
			array(
				'parent' => $menu_id,
				'title'  => esc_html__( 'View Docs', 'dollie' ),
				'id'     => 'dab-hosted-docs',
				'href'   => 'https://support.getdollie.com',
				'meta'   => array( 'target' => '_blank' ),
			)
		);

		/*
		$wp_admin_bar->add_menu(
			[
				'parent' => $menu_id,
				'title'  => esc_html__( 'Sync Sites', 'dollie' ),
				'id'     => 'dab-sync-sites',
				'href'   => get_admin_url() . '?dollie-sync-containers',
				'meta'   => [ 'target' => '_blank' ],
			]
		);*/

		$wp_admin_bar->add_menu(
			array(
				'parent' => $menu_id,
				'title'  => esc_html__( 'View Usage', 'dollie' ),
				'id'     => 'dab-hosted-usage',
				'href'   => 'https://control.getdollie.com/user/usage',
				'meta'   => array( 'target' => '_blank' ),
			)
		);

		if ( defined( 'S5_APP_TOKEN' ) ) {

			$wp_admin_bar->add_menu(
				array(
					'parent' => 'top-secondary',
					'title'  => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
	<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
	</svg> Hosted in Your Private Cloud',
					'id'     => 'dwb-hosted',
					'href'   => 'https://control.getdollie.com/?redirect=dollie-site-redirect',
				)
			);

			$wp_admin_bar->add_menu(
				array(
					'parent' => 'dwb-hosted',
					'title'  => esc_html__( 'This site is hosted in your own Private Cloud. Visit Control HQ to manage backups,use the developer tools and more.', 'dollie' ),
					'id'     => 'dab-hosted-message',
					'href'   => 'https://control.getdollie.com/?redirect=dollie-site-redirect',
					'meta'   => array( 'target' => '_blank' ),
				)
			);
		}
	}

	/**
	 * Add external links menu
	 */
	public function add_extra_menu_links() {
		global $submenu;

		$submenu['dollie_setup'][] = array(
			esc_html__( 'Import Templates', 'dollie' ),
			'manage_options',
			get_admin_url() . 'admin.php?page=dollie_templates',
		);

		$submenu['dollie_setup'][] = array(
			__( 'Hub Pages', 'dollie' ),
			'manage_options',
			'',
			'',
			'dol-divider-menu',
		);

		$submenu['dollie_setup'][] = array(
			esc_html__( 'Dashboard', 'dollie' ),
			'manage_options',
			dollie()->page()->get_dashboard_url(),
		);

		$submenu['dollie_setup'][] = array(
			esc_html__( 'Customers', 'dollie' ),
			'manage_options',
			dollie()->page()->get_customers_url(),
		);

		$submenu['dollie_setup'][] = array(
			esc_html__( 'Sites', 'dollie' ),
			'manage_options',
			dollie()->page()->get_sites_url(),
		);

		$submenu['dollie_setup'][] = array(
			esc_html__( 'Blueprints', 'dollie' ),
			'manage_options',
			dollie()->page()->get_blueprints_url(),
		);

		$submenu['dollie_setup'][] = array(
			__( 'Support', 'dollie' ),
			'manage_options',
			'',
			'',
			'dol-divider-menu',
		);

		$submenu['dollie_setup'][] = array(
			'<div class="dol-cloud-url">' . esc_html__( 'Visit Control HQ', 'dollie' ) . '</div>',
			'manage_options',
			'https://control.getdollie.com/',
		);

		$submenu['dollie_setup'][] = array(
			'<div id="dol-logs">' . esc_html__( 'Debug Logs', 'dollie' ) . '</div>',
			'manage_options',
			get_admin_url() . 'edit.php?post_type=dollie-logs',
		);
	}

	/**
	 * Blueprints info banner
	 *
	 * @return void
	 */
	public function admin_footer_wrap() {
		dollie_setup_get_template_part( 'wrapper-footer' );
	}

	public function crisp_support_js() {
		$current_screen = get_current_screen();
		$target_screens = array( 'toplevel_page_dollie_setup', 'admin_page_dollie_templates', 'upload' ); // Add screen IDs of the pages you want to target
		if ( $current_screen && in_array( $current_screen->id, $target_screens ) ) {
			?>
			<script type="text/javascript">window.$crisp=[];window.CRISP_WEBSITE_ID="613aba5f-fa3b-4e8a-9689-a92f3b9af84b";(function(){d=document;s=d.createElement("script");s.src="https://client.crisp.chat/l.js";s.async=1;d.getElementsByTagName("head")[0].appendChild(s);})();</script>
			<?php
		}
	}



	/**
	 * Alter container actions
	 *
	 * @param [type] $actions
	 * @param [type] $page_object
	 * @return array
	 */
	public function alter_container_row_actions( $actions, $page_object ) {
		if ( 'container' !== get_post_type() ) {
			return $actions;
		}

		$container = dollie()->get_container( $page_object );

		if ( is_wp_error( $container ) ) {
			return $actions;
		}

		$new_actions = array();

		if ( ! $container->is_blueprint() ) {
			$new_actions['manage_site']     = '<a href="' . $container->get_permalink() . '" target="_blank" class="button-link">' . __( 'View Site Dashboard', 'dollie' ) . '</a>';
			$new_actions['view_in_control'] = '<a href="https://control.getdollie.com/container/' . $container->get_original_url() . '/dashboard" target="_blank" class="button-link">' . __( 'Manage in Control HQ', 'dollie' ) . '</a>';
		} else {
			$new_actions['manage_site'] = '<a href="' . $container->get_permalink() . '" target="_blank" class="button-link">' . __( 'Manage Blueprint', 'dollie' ) . '</a>';
		}

		$new_actions['admin_link'] = '<a href="' . $container->get_customer_login_url() . '" target="_blank" class="button-link">' . __( 'Login to Site', 'dollie' ) . '</a>';

		if ( isset( $actions['trash'] ) ) {
			$new_actions['trash'] = str_replace( 'Trash', '', $actions['trash'] );
		}

		if ( isset( $actions['untrash'] ) ) {
			$new_actions['untrash'] = $actions['untrash'];
		}

		return $new_actions;
	}

	/**
	 * Add custom bulk actions
	 */
	public function container_bulk_actions( $actions ) {
		if ( isset( $_GET['post_status'] ) && 'trash' === sanitize_text_field( $_GET['post_status'] ) ) {
			return $actions;
		}

		return array(
			'stop'    => __( 'Stop', 'dollie' ),
			'start'   => __( 'Start', 'dollie' ),
			'restart' => __( 'Restart', 'dollie' ),
			'trash'   => __( 'Delete', 'dollie' ),
		);
	}

	/**
	 * Handle bulk actions
	 */
	public function handle_container_bulk_actions( $redirect_url, $action, $post_ids ) {
		if ( in_array( $action, array( 'start', 'stop', 'restart' ), true ) ) {
			foreach ( $post_ids as $post_id ) {
				$container = dollie()->get_container( $post_id );

				if ( is_wp_error( $container ) ) {
					continue;
				}

				if ( 'start' === $action && $container->is_stopped() ) {
					$container->perform_action( $action );
					$container->set_details(
						array(
							'status' => 'Running',
						)
					);

					continue;
				}

				if ( 'stop' === $action && $container->is_running() ) {
					$container->perform_action( $action );
					$container->set_details(
						array(
							'status' => 'Stopped',
						)
					);

					continue;
				}

				$container->perform_action( $action );
				$container->set_details(
					array(
						'status' => 'Running',
					)
				);
			}

			$redirect_url = add_query_arg( "{$action}ed", count( $post_ids ), $redirect_url );
		}

		return $redirect_url;
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
			array(
				'domain' => __( 'Domain', 'dollie' ),
				'type'   => __( 'Type', 'dollie' ),
				'status' => __( 'Status', 'dollie' ),
			)
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
			} elseif ( $container->is_staging() ) {
				esc_html_e( 'Staging', 'dollie' );
			}
		}

		if ( 'domain' === $column_name ) {
			echo "<a href=\"{$container->get_url( true )}\" target=\"_blank\">{$container->get_url()}</a>";
		}

		if ( 'status' === $column_name ) {
			echo $container->get_status();
		}
	}

	/**
	 * Display info banners
	 *
	 * @return void
	 */
	public function add_info_banners() {
		$screen = get_current_screen();

		if ( 'edit-container' === $screen->id ) {
			add_action( 'all_admin_notices', array( $this, 'site_info_banner' ) );
			add_action( 'admin_footer', array( $this, 'admin_footer_wrap' ), 999999 );
		}

		if ( 'edit-dollie-access-groups' === $screen->id ) {
			add_action( 'all_admin_notices', array( $this, 'access_group_info_banner' ) );
			add_action( 'admin_footer', array( $this, 'admin_footer_wrap' ), 999999 );
		}

		if ( 'edit-dollie-logs' === $screen->id ) {
			add_action( 'all_admin_notices', array( $this, 'blueprint_info_banner' ) );
			add_action( 'admin_footer', array( $this, 'admin_footer_wrap' ), 999999 );
		}

		if ( 'edit-af_form' === $screen->id ) {
			add_action( 'admin_header', array( $this, 'dollie_footer_wrap' ) );
			add_action( 'admin_footer', array( $this, 'admin_footer_wrap' ), 999999 );
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
						__( 'Below are all the sites that have been launched through your Hub. Below you can see their status, to which customer they are linked and whether they have a domain connected to them. <br><br><strong>Important - If you want to stop or delete sites, please use <a href="https://control.getdollie.com/">Control HQ</a></strong><br><br>', 'dollie' ),
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
						esc_html__( 'Below you will find all the Blueprints you have created. Please visit Control HQ (https://control.getdollie.com) to manage and create new Blueprints.', 'dollie' ),
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
	 * Site info banner
	 *
	 * @return void
	 */
	public function access_group_info_banner() {
		?>
		<?php
			dollie_setup_get_template_part( 'wrapper-header' );
		?>
			<div class="dollie-page-intro">
				<p>
					<?php
					printf(
						esc_html__( 'With Access Groups you can easily control what your customers/clients can do inside your Hub.', 'dollie' ),
					);
					?>
				</p>
			</div>
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
		if ( ! is_admin() || wp_doing_ajax() || isset( $_GET['dollie-sync-containers'] ) ) {
			return $query;
		}

		if ( 'container' !== $query->query['post_type'] ) {
			return $query;
		}

		$screen = get_current_screen();

		if ( 'edit-container' !== $screen->id ) {
			return $query;
		}

		$term       = '';
		$meta_query = array();

		if ( isset( $_GET['s'] ) ) {
			$term = strtolower( sanitize_text_field( $_GET['s'] ) );
		}

		if ( $term ) {
			$meta_query['relation'] = 'AND';
			$meta_query[]           = array(
				'key'     => 'dollie_container_details',
				'value'   => $term,
				'compare' => 'LIKE',
			);
		}

		if ( isset( $_GET['blueprint'] ) ) {
			$meta_query[] = array(
				'key'   => 'dollie_container_type',
				'value' => self::TYPE_BLUEPRINT,
			);
		} else {
			$meta_query[] = array(
				'key'   => 'dollie_container_type',
				'value' => self::TYPE_SITE,
			);
		}

		$query->set( 'meta_query', $meta_query );

		return $query;
	}

	/**
	 * Remove default search
	 *
	 * @param [type] $query
	 */
	public function remove_default_search( $query ) {
		if ( ! is_admin() || wp_doing_ajax() ) {
			return $query;
		}

		if ( 'container' !== $query->query['post_type'] ) {
			return $query;
		}

		unset( $query->query_vars['s'] );

		return $query;
	}

	/**
	 * Add Site Icon Menu hook
	 */
	public function add_site_icon_filter() {
		add_filter( 'attribute_escape', array( $this, 'add_site_icon' ), 20, 2 );
	}

	/**
	 * Remove Site Icon Menu hook
	 */
	public function remove_site_icon_filter() {
		remove_filter( 'attribute_escape', array( $this, 'add_site_icon' ), 20, 2 );
	}

	/**
	 * Add Site Icon to Menu
	 */
	public function add_site_icon( $safe_text = '', $text = '' ) {
		if ( substr_count( $text, '%%ICON%%' ) ) {
			$text = trim( str_replace( '%%ICON%%', '', $text ) );
			// run only once!
			remove_filter( 'attribute_escape', 'add_site_icon', 20, 2 );
			$safe_text = esc_attr( $text );
			$text      = dollie()->icon()->site() . esc_attr( $text );
			return $text;
		}

		return $safe_text;
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
				$meta_query     = array();
				$container_type = self::TYPE_SITE;

				if ( isset( $_GET['blueprint'] ) && ! empty( $_GET['blueprint'] ) ) {
					$container_type = self::TYPE_BLUEPRINT;
				}

				$meta_query[] = array(
					'key'     => 'dollie_container_type',
					'value'   => $container_type,
					'compare' => '=',
				);

				$args = array(
					'posts_per_page' => -1,
					'post_type'      => 'container',
					'post_status'    => 'publish',
					'meta_query'     => $meta_query,
				);

				$blueprints      = new \WP_Query( $args );
				$counts->publish = $blueprints->found_posts;

				wp_reset_postdata();

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
	 * Modify user table
	 *
	 * @param array $column
	 * @return array
	 */
	public function new_modify_user_table( $column ) {
		$column['sites'] = 'Sites';

		return $column;
	}

	/**
	 * Modify user table column
	 *
	 * @param [type] $val
	 * @param [type] $column_name
	 * @param [type] $user_id
	 * @return int
	 */
	public function new_modify_user_table_row( $val, $column_name, $user_id ) {
		$user = dollie()->get_user( $user_id );

		if ( 'sites' === $column_name ) {
			return '<a href="' . admin_url( 'edit.php?post_type=container&author=' . $user_id ) . '">' . $user->count_containers() . '</a>';
		}

		return $val;
	}

	/**
	 * Filter by author
	 *
	 * @return void
	 */
	public function filter_by_author() {
		$params = array(
			'name'            => 'author',
			'show_option_all' => 'All customers',
		);

		if ( isset( $_GET['user'] ) ) {
			$params['selected'] = $_GET['user'];
		}

		wp_dropdown_users( $params );
	}
}
