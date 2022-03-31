<?php

namespace Dollie\Core\Admin;

use Dollie\Core\Log;
use Dollie\Core\Singleton;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class NavMenu extends Singleton {
	const MENU_KEY = 'dollie';

	/**
	 * AjaxAuth constructor.
	 */
	public function __construct() {
		if ( is_admin() ) {
			require_once DOLLIE_CORE_PATH . 'Admin/nav-menu/NavMenuWalker.php';
		}

		add_action( 'load-nav-menus.php', [ $this, 'wp_nav_menu_meta_box' ] );
		add_filter( 'get_user_option_metaboxhidden_nav-menus', [ $this, 'wp_nav_menu_meta_box_always_show' ], 10, 3 );
		add_filter( 'wp_setup_nav_menu_item', [ $this, 'setup_nav_menu_item' ], 10, 1 );

		add_filter( 'walker_nav_menu_start_el', [ $this, 'nav_notifications' ], 10, 4 );

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_ajax_dollie_notifications_mark_read', [ $this, 'ajax_mark_as_read' ] );
		add_action( 'wp_ajax_dollie_get_notifications', [ $this, 'ajax_get_notifications' ] );

	}

	public function enqueue_scripts() {
		wp_register_script( 'dollie-notifications', DOLLIE_ASSETS_URL . 'js/notifications.js', [ 'jquery' ], DOLLIE_VERSION, true );
		wp_localize_script(
			'dollie-notifications',
			'wpdNotifications',
			[
				'ajaxurl'     => admin_url( '/admin-ajax.php' ),
				'refreshTime' => 20000,
			]
		);
	}

	/**
	 * Add nav menu login meta box
	 */
	public function wp_nav_menu_meta_box() {
		add_meta_box(
			'add-' . self::MENU_KEY . '-menu',
			esc_html__( 'Dollie', 'dollie' ),
			[
				$this,
				'do_wp_nav_menu_meta_box',
			],
			'nav-menus',
			'side',
			'default'
		);

		add_action( 'admin_print_footer_scripts', [ $this, 'wp_nav_menu_restrict_items' ] );
	}

	/**
	 *  Always show the Dollie Metabox.
	 */
	public function wp_nav_menu_meta_box_always_show( $result, $option, $user ) {
		if ( in_array( 'add-dollie-menu', $result ) ) {
			$result = array_diff( $result, [ 'add-dollie-menu' ] );
		}

		return $result;
	}

	/**
	 * Build and populate the Dollie accordion in Appearance > Menus.
	 *
	 * @global $nav_menu_selected_id
	 */
	public function do_wp_nav_menu_meta_box() {
		global $nav_menu_selected_id;

		$walker = new Ajax_Walker_Nav_Menu_Checklist( false );
		$args   = [ 'walker' => $walker ];

		$post_type_name = self::MENU_KEY;

		$tabs = [];

		$tabs['loggedin']['label'] = esc_html__( 'Logged-In', 'dollie' );
		$tabs['loggedin']['pages'] = [
			(object) [
				'ID'             => - 1,
				'post_title'     => esc_html__( 'Log Out', 'dollie' ),
				'post_author'    => 0,
				'post_date'      => 0,
				'post_excerpt'   => 'logout',
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'comment_status' => 'closed',
				'guid'           => wp_logout_url(),
			],
			(object) [
				'ID'             => - 1,
				'post_title'     => esc_html__( 'My Account', 'dollie' ),
				'post_author'    => 0,
				'post_date'      => 0,
				'post_excerpt'   => 'avatar',
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'comment_status' => 'closed',
				'guid'           => '#',
			],
			(object) [
				'ID'             => - 1,
				'post_title'     => esc_html__( 'Live Notifications', 'dollie' ),
				'post_author'    => 0,
				'post_date'      => 0,
				'post_excerpt'   => 'notifications',
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'comment_status' => 'closed',
				'guid'           => '#',
			],
		];

		$tabs['loggedout']['label'] = esc_html__( 'Logged-Out', 'dollie' );
		$tabs['loggedout']['pages'] = [];

		$tabs['loggedout']['pages']['login'] = (object) [
			'ID'             => - 1,
			'post_title'     => esc_html__( 'Log In', 'dollie' ),
			'post_author'    => 0,
			'post_date'      => 0,
			'post_excerpt'   => 'login',
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'comment_status' => 'closed',
			'guid'           => wp_login_url(),
		];

		$title = '';
		$link  = '';

		if ( function_exists( 'bp_is_active' ) && bp_is_active() ) {
			$bp_directory_page_ids = bp_core_get_directory_page_ids();
			if ( isset( $bp_directory_page_ids['register'] ) ) {
				$register_page = get_post( $bp_directory_page_ids['register'] );
				$title         = $register_page->post_title;
				$link          = get_permalink( $register_page->ID );
			}
		}

		if ( ! $title && ! $link ) {
			$title = esc_html__( 'Register', 'dollie' );
			$link  = wp_registration_url();
		}

		$tabs['loggedout']['pages']['register'] = (object) [
			'ID'             => - 1,
			'post_title'     => $title,
			'post_author'    => 0,
			'post_date'      => 0,
			'post_excerpt'   => 'register',
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'comment_status' => 'closed',
			'guid'           => $link,
		];
		?>

		<div id="<?php echo self::MENU_KEY; ?>-menu" class="posttypediv">
			<h4><?php esc_html_e( 'Logged-Out', 'dollie' ); ?></h4>
			<p><?php esc_html_e( 'Logged-Out links are not visible to users who are logged in.', 'dollie' ); ?></p>

			<div id="tabs-panel-posttype-<?php echo $post_type_name; ?>-loggedout" class="tabs-panel tabs-panel-active">
				<ul id="<?php echo self::MENU_KEY; ?>-menu-checklist-loggedout" class="categorychecklist form-no-clear">
					<?php echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $tabs['loggedout']['pages'] ), 0, (object) $args ); ?>
				</ul>
			</div>

			<h4><?php esc_html_e( 'Logged-In', 'dollie' ); ?></h4>
			<p><?php esc_html_e( 'Logged-in links are visible only to users who are logged in.', 'dollie' ); ?></p>

			<div id="tabs-panel-posttype-<?php echo $post_type_name; ?>-loggedin" class="tabs-panel tabs-panel-active">
				<ul id="<?php echo self::MENU_KEY; ?>-menu-checklist-loggedin" class="categorychecklist form-no-clear">
					<?php echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $tabs['loggedin']['pages'] ), 0, (object) $args ); ?>
				</ul>
			</div>

			<?php
			$removed_args = [
				'action',
				'customlink-tab',
				'edit-menu-item',
				'menu-item',
				'page-tab',
				'_wpnonce',
			];
			?>

			<p class="button-controls">
			<span class="list-controls">
				<a href="
				<?php
				echo esc_url(
					add_query_arg(
						[
							$post_type_name . '-tab' => 'all',
							'selectall'              => 1,
						],
						remove_query_arg( $removed_args )
					)
				);
				?>
				#<?php echo self::MENU_KEY; ?>-menu"
				   class="select-all"><?php esc_html_e( 'Select All', 'dollie' ); ?></a>
			</span>
				<span class="add-to-menu">
				<input
						type="submit"
						<?php
						if ( function_exists( 'wp_nav_menu_disabled_check' ) ) :
							wp_nav_menu_disabled_check( $nav_menu_selected_id );
						endif;
						?>
					class="button-secondary submit-add-to-menu right"
						value="<?php esc_attr_e( 'Add to Menu', 'dollie' ); ?>" name="add-custom-menu-item"
						id="submit-<?php echo self::MENU_KEY; ?>-menu"/>
				<span class="spinner"></span>
			</span>
			</p>
		</div><!-- /#nav-menu -->

		<?php
	}

	/**
	 * Restrict various items from view if editing a Dollie menu.
	 *
	 * If a person is editing a Dollie menu item, that person should not be able to
	 * see or edit the following fields:
	 *
	 * - CSS Classes - We use the 'dollie-login-menu' CSS class to determine if the
	 *   menu item belongs to Dollie, so we cannot allow manipulation of this field to
	 *   occur.
	 * - URL - This field is automatically generated by BP on output, so this
	 *   field is useless and can cause confusion.
	 *
	 * Note: These restrictions are only enforced if JavaScript is enabled.
	 *
	 * @since 1.0
	 */
	public function wp_nav_menu_restrict_items() {
		?>
		<script type="text/javascript">
			jQuery('#menu-to-edit').on('click', 'a.item-edit', function () {
				var settings = jQuery(this).closest('.menu-item-bar').next('.menu-item-settings');
				var css_class = settings.find('.edit-menu-item-classes');

				if (css_class.val().indexOf('<?php echo self::MENU_KEY; ?>-menu') === 0) {
					css_class.attr('readonly', 'readonly');
					settings.find('.field-url').css('display', 'none');
				}
			});
		</script>
		<?php
	}

	/**
	 * Add Specific items to the wp_nav_menu.
	 *
	 * @param \WP_Post $menu_item The menu item.
	 *
	 * @return \WP_Post The modified WP_Post object.
	 * @since 1.9.0
	 */
	public function setup_nav_menu_item( $menu_item ) {
		if ( is_admin() ) {
			return $menu_item;
		}

		// Prevent a notice error when using the customizer.
		$menu_classes = $menu_item->classes;

		if ( is_array( $menu_classes ) ) {
			$menu_classes = implode( ' ', $menu_item->classes );
		}

		// We use information stored in the CSS class to determine what kind of
		// menu item this is, and how it should be treated.
		preg_match( '/\s' . self::MENU_KEY . '-(.*)-nav/', $menu_classes, $matches );

		// If this isn't a Dollie menu item, we can stop here.
		if ( empty( $matches[1] ) ) {
			return $menu_item;
		}

		switch ( $matches[1] ) {
			case 'login':
				if ( is_user_logged_in() ) {
					$menu_item->_invalid = true;
				} else {
					$menu_item->url = dollie()->get_login_page_id() > 0 ? get_permalink( dollie()->get_login_page_id() ) : wp_login_url( $this->get_requested_url() );
				}

				break;

			// Don't show the Register link to logged-in users.
			case 'register':
				if ( is_user_logged_in() ) {
					$menu_item->_invalid = true;
				}

				break;

			case 'logout':
				if ( ! is_user_logged_in() ) {
					$menu_item->_invalid = true;
				} else {
					$menu_item->url = wp_logout_url( $this->get_requested_url() );
				}

				break;

			case 'notifications':
				if ( ! is_user_logged_in() ) {
					$menu_item->_invalid = true;
				}

				break;

			case 'avatar':
				if ( ! is_user_logged_in() ) {
					$menu_item->url       = wp_login_url( $this->get_requested_url() );
					$menu_item->classes[] = 'dollie-login-nav';
				} else {
					$menu_item->url = dollie()->get_dashboard_page_url();

					$avatar            = get_avatar_url(
						get_current_user_id(),
						[
							'width'  => 30,
							'height' => 30,
						]
					);
					$menu_item->title  = '<img class="dollie-menu-avatar-img" src="' . esc_attr( $avatar ) . '"' . ' alt="">';
					$menu_item->title .= ( $menu_item->attr_title !== '' ? '<span class="dollie-menu-avatar-text">' . strip_tags( $menu_item->attr_title ) . '</span>' : '' );
				}

				break;
		}

		// If component is deactivated, make sure menu item doesn't render.
		if ( empty( $menu_item->url ) ) {
			$menu_item->_invalid = true;

			// Highlight the current page.
		} else {
			$current = $this->get_requested_url();
			if ( strpos( $current, $menu_item->url ) !== false ) {
				if ( is_array( $menu_item->classes ) ) {
					$menu_item->classes[] = 'current_page_item';
					$menu_item->classes[] = 'current-menu-item';
				} else {
					$menu_item->classes = [ 'current_page_item', 'current-menu-item' ];
				}
			}
		}

		return $menu_item;
	}

	/**
	 * Mark notifications as read by ajax request
	 */
	public function ajax_mark_as_read() {

		check_ajax_referer( 'dollie-notifications', 'check' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error(
				[
					'message' => esc_html__( 'You need to be logged in', 'dollie' ),
				]
			);
		}

		$args = [
			'posts_per_page'   => - 1,
			'post_type'        => 'dollie-logs',
			'suppress_filters' => true,
			'meta_query'       => [
				[
					'key'   => '_wpd_read',
					'value' => 0,
				],
			],
		];

		if ( ! current_user_can( 'manage_options' ) ) {
			$args['author'] = get_current_user_id();
		}

		$posts_array = get_posts( $args );

		foreach ( $posts_array as $post_array ) {
			update_post_meta( $post_array->ID, '_wpd_read', 1 );
		}

		wp_send_json_success();
		exit;
	}

	/**
	 * Mark notifications as read by ajax request
	 */
	public function ajax_get_notifications() {

		check_ajax_referer( 'dollie-notifications', 'check' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error(
				[
					'message' => esc_html__( 'You need to be logged in', 'dollie' ),
				]
			);
		}

		$data = $this->get_notifications_content();

		/*
		$current = sanitize_text_field( $_GET['current_notifications'] );
		if ( $data['count'] === (int) $current ) {
			wp_send_json_success();
			exit;
		}
		*/

		wp_send_json_success( $data );
		exit;
	}

	/**
	 * Notifications content
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	private function get_notifications_content( $args = [] ) {

		$defaults = [
			'posts_per_page' => - 1,
			'post_type'      => 'dollie-logs',
			'meta_query'     => [
				'relation' => 'AND',
				[
					'key'     => '_wpd_read',
					'compare' => 'EXISTS',
				],
				[
					'key'     => '_wpd_bulk_log',
					'compare' => 'NOT EXISTS',
				],
			],
		];

		$args = wp_parse_args( $args, $defaults );

		if ( ! current_user_can( 'manage_options' ) ) {
			$args['author'] = get_current_user_id();
		}

		$item_output   = '';
		$count         = 0;
		$notifications = get_posts( $args );

		if ( $notifications ) {

			foreach ( $notifications as $notification ) {

				$read_status = get_post_meta( $notification->ID, '_wpd_read', true ) === '1';
				$read_class  = $read_status ? 'notif-read' : 'notif-unread';
				$log_data    = get_post_meta( $notification->ID, '_wpd_log_data', true );
				$site_id     = get_post_meta( $notification->ID, '_wpd_site_id', true );

				$log = Log::get_content( $log_data['action'], $log_data['values'], $notification->ID );

				if ( ! $log_data || ! isset( $log['title'] ) ) {
					continue;
				}

				$item_output .= '<div class="notif-item ' . $read_class . ' dol-p-5 dol-border-0 dol-border-b dol-border-gray-200 dol-border-solid">';
				$item_output .= '<div class="notif-title dol-flex dol-justify-items-stretch dol-space-x-2">' .
								'<div class="dol-flex-grow">' .
								esc_html( $log['title'] ) .
								'</div>' .
								'<div class="notif-date dol-flex-shrink dol-text-right dol-text-gray-500">' .
								human_time_diff( strtotime( $notification->post_date ) ) . ' ago' .
								'</div>' .
								'</div>';

				$link_output = '';
				if ( $site_id && isset( $log['link'] ) && $log['link'] ) {
					$link      = get_permalink( $site_id );
					$link_text = esc_html__( 'Go to site', 'dollie' );

					if ( dollie()->is_blueprint( $site_id ) ) {
						$link      = trailingslashit( $link ) . 'blueprints';
						$link_text = esc_html__( 'Go to blueprint', 'dollie' );
					}

					$link_output = '<br><a href="' . $link . '">' . $link_text . '</a>';
				}

				$item_output .= '<div class="notification-body dol-text-gray-600 dol-mt-1">' .
								wp_kses_post( $log['content'] ) .
								$link_output .
								'</div>';
				$item_output .= '</div>';

				if ( ! $read_status ) {
					$count ++;
				}
			}
		}

		if ( $count === 0 ) {
			$count = '';
		}

		return [
			'count'   => $count,
			'content' => $item_output,
		];
	}

	/**
	 * Notifications menu item
	 *
	 * @param $item_output
	 * @param $item
	 * @param $depth
	 * @param $args
	 *
	 * @return mixed|string
	 */
	public function nav_notifications( $item_output, $item, $depth, $args ) {
		if ( in_array( 'dollie-notifications-nav', $item->classes, true ) ) {

			wp_enqueue_script( 'dollie-notifications' );

			$footer_output = '<div class="transform dollie-notifications-list dol-z-99999 dol-h-full dol-fixed dol-top-0 dol-right-0 dol-leading-normal dol-text-left ol-leading-4 dol-text-gray-700 dol-bg-white dol-hidden dol-rounded dol-shadow-md dol-ease-in-out dol-transition-all dol-duration-300 dol-translate-x-0"' .
							 ' data-nonce="' . wp_create_nonce( 'dollie-notifications' ) . '"' . '>';

			$footer_output .= '<div class="notif-header">' .
							  '<div class="notif-header-title dol-flex dol-justify-items-stretch dol-space-x-4 dol-p-5 dol-border-0 dol-border-b dol-border-gray-200 dol-border-solid">' .
							  '<div class="dol-text-lg dol-flex-grow">' .
							  esc_html__( 'Notifications', 'dollie' ) .
							  ' <span class="dollie-notifications-count">%notif_count%</span>' .
							  '</div>' .
							  '<div class="dol-flex-shrink">' .
							  '<a class="notif-mark-read" href="#">' . esc_html__( 'Mark all as read', 'dollie' ) . '</a>' .
							  '</div>' .
							  '<div class="dol-flex-shrink">' .
							  '<a class="notif-close" title="' . esc_attr__( 'Close', 'dollie' ) . '" href="#">' .
								dollie()->icon()->close() .
							  '</a>' .
							  '</div>' .
							  '</div>' .
							  '</div>';
			$footer_output .= '<div class="notif-inner dol-overflow-y-auto">';

			$notifications = $this->get_notifications_content();

			$footer_output .= $notifications['content'];
			$footer_output .= '</div></div>';
			$footer_output  = str_replace( '%notif_count%', $notifications['count'], $footer_output );

			add_action(
				'wp_footer',
				static function () use ( $footer_output ) {
					echo $footer_output;
				},
				20
			);

			$menu_output = '<a href="#">' .
						   '<span class="notif-icon-wrap dol-relative">' .
						   dollie()->icon()->notifications() .
						   '<span class="dollie-notifications-count notif-navigation-count">%notif_count%</span>' .
						   '</span>' .
						   '</a>';
			$menu_output = str_replace( '%notif_count%', $notifications['count'], $menu_output );

			return $menu_output;
		}

		return $item_output;
	}

	/**
	 * Return the URL as requested on the current page load by the user agent.
	 *
	 * @return string Requested URL string.
	 * @since 1.0
	 */
	public function get_requested_url() {
		$url  = is_ssl() ? 'https://' : 'http://';
		$url .= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		return apply_filters( 'dollie/get_requested_url', esc_url( $url ) );
	}

}
