<?php

namespace Dollie\Core\Modules\Dashboard;

use Dollie\Core\Singleton;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * The Dollie Dashboard Pages class
 *
 * @since 1.0.0
 */
class Pages extends Singleton {

	/**
	 * Holds the collection of page details
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $pages = [];

	/**
	 * Pages constructor
	 *
	 * @uses is_admin()
	 */
	public function __construct() {
		parent::__construct();

		// Only run when in the admin
		if ( ! is_admin() ) {
			return;
		}

		$this->setup_actions();
	}

	/**
	 * Setup class hooks and filters
	 *
	 * @since 1.0.0
	 */
	private function setup_actions() {

		// Register pages
		add_action( 'dollie_dashboard_init', array( $this, 'add_pages' ) );

		// Admin
		add_action( 'admin_menu', array( $this, 'admin_menus' ) );
		add_action( 'network_admin_menu', array( $this, 'admin_menus' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_body_class', array( $this, 'admin_body_class' ) );
		add_action( 'admin_head', array( $this, 'inline_admin_css' ) );
	}

	/** Page Registration *************************************************/

	/**
	 * Register the default dashboard pages
	 *
	 */
	public function add_pages() {

		// Get WeFoster
		$wf = Setup::instance();

		// Dashboard Home
		$this->add_page( array(
			'title'      => __( 'Dollie Integrations', 'dollie' ),
			'slug'       => 'dollie-integrations',
			'parent'     => false,
			'labels'     => array(
				'menu'    => __( 'Integrations', 'dollie' ),
				'loading' => __( 'Loading Dollie Integrations', 'dollie' )
			),
			'capability' => 'manage_options',
			'priority'   => - 99999, // Right after WP's Dashboard
			'icon'       => $wf->plugin_url . 'assets/img/logo-dashboard.png',
			'load_icon'  => $wf->plugin_url . 'assets/img/logo-loading.png',
			'iframe_src' => 'https://getdollie.com/integrations/',
			'_builtin'   => true,
			//'callback'    => 'wpd_platform_setup',
		) );


		/**
		 * Runs after the core dashboard pages are added
		 *
		 * @since 1.0.0
		 */
		do_action( 'dollie_dashboard_add_pages' );
	}

	/**
	 * Register a dashboard page
	 *
	 * @param array $args Page parameters
	 *
	 * @return slug|bool The registered page slug or False when not successful.
	 * @since 1.0.0
	 **
	 */
	public function add_page( $args = array() ) {

		// Merge with defaults
		$args = wp_parse_args( $args, array(
			'title'      => '',
			'slug'       => '',
			'parent'     => 'wpd_platform_setup',
			'capability' => 'manage_options',
			'labels'     => array(),
			'priority'   => 10,
			'icon'       => '',
			'load_icon'  => '',
			'iframe_src' => '',
			'callback'   => '',
			'acf'        => false,
			'_builtin'   => false
		) );

		// Bail when we're missing important arguments
		if ( empty( $args['title'] ) || empty( $args['slug'] ) ) {
			return false;
		}

		// Bail when overwriting pages
		if ( isset( $this->pages[ $args['slug'] ] ) ) {
			return false;
		}

		// Define page labels
		$args['labels'] = $this->setup_page_labels( $args );

		// Set iframe page callback
		if ( ! empty( $args['iframe_src'] ) && empty( $args['callback'] ) ) {
			$args['callback'] = array( $this, 'render_iframe_page' );
		}

		// Bail when the callback is missing
		if ( ! $args['acf'] && ! is_callable( $args['callback'] ) ) {
			return false;
		}

		// TODO: sanitize args
		$page = (object) $args;

		// Register this page
		$this->pages[ $page->slug ] = $page;

		return $page->slug;
	}

	/**
	 * Remove a page from the dashboard
	 *
	 * @param string $slug Unique page slug
	 *
	 * @since 1.0.0
	 *
	 */
	public function remove_page( $slug ) {
		if ( isset( $this->pages[ $slug ] ) ) {
			unset( $this->pages[ $slug ] );
		}
	}

	/**
	 * Update a page variable
	 *
	 * @param string $slug Unique page slug
	 * @param string $arg Page variable name
	 * @param mixed $value New variable value
	 *
	 * @return bool Update success
	 * @since 1.0.0
	 *
	 */
	public function set_page_var( $slug, $arg, $value ) {

		// Bail when the page is not found
		if ( ! isset( $this->pages[ $slug ] ) ) {
			return false;
		}

		// Set page variable
		// TODO: sanitize args
		$this->pages[ $slug ]->{$arg} = $value;

		return true;
	}

	/**
	 * Setup and return the page labels
	 *
	 * @param array $args Page registration parameters
	 *
	 * @return array Page labels
	 * @since 1.0.0
	 *
	 */
	private function setup_page_labels( $args ) {

		// Get the page title
		$title = $args['title'];

		// Setup default labels
		$labels = array(
			'menu'    => $title,
			'loading' => sprintf( __( 'Loading %s', 'dollie' ), $title ),
		);

		// Merge labels
		if ( is_array( $args['labels'] ) ) {
			$labels = wp_parse_args( $args['labels'], $labels );
		}

		return $labels;
	}

	/**
	 * Return (a filtered selection of) the registered pages
	 *
	 * @param array $args Filter arguments. See {@link wp_list_filter()}.
	 * @param array $operator Filter operator. See {@link wp_list_filter()}.
	 *
	 * @return array Pages
	 * @since 1.0.0
	 *
	 */
	public function get_pages( $args = array(), $operator = 'AND' ) {
		return wp_list_filter( $this->pages, $args, $operator );
	}

	/**
	 * Return the page's url
	 *
	 * @param string $slug Page slug
	 *
	 * @return string Page url
	 * @since 1.0.0
	 *
	 * @uses dollie_get_admin_url()
	 *
	 */
	public function get_page_url( $slug ) {

		// Bail when the requested page is not available
		if ( ! isset( $this->pages[ $slug ] ) ) {
			return '';
		}

		return add_query_arg( array( 'page' => $slug ), admin_url( 'admin.php' ) );
	}

	/**
	 * Register admin menus
	 *
	 * @since 1.0.0
	 *
	 * @uses is_multisite()
	 * @uses is_network_admin()
	 * @uses add_menu_page()
	 * @uses acf_add_options_sub_page()
	 * @uses add_submenu_page()
	 */
	public function admin_menus() {

		// Register page menus
		foreach ( $this->get_pages() as $page ) {

			// Parent page
			if ( ! $page->parent ) {
				add_menu_page( $page->title, $page->labels['menu'], $page->capability, $page->slug, $page->callback, $page->icon, $page->priority );

				// Sub page
			} else {

				// ACF options page
				if ( $page->acf ) {

					// Define args
					$page->menu_title = $page->labels['menu'];

					// Register ACF options page
					acf_add_options_sub_page( (array) $page );

					// Default sub page
				} else {
					add_submenu_page( $page->parent, $page->title, $page->labels['menu'], $page->capability, $page->slug, $page->callback, $page->icon, $page->priority );
				}
			}
		}
	}

	/**
	 * Return whether the current admin page is in the dashboard
	 *
	 * @param string $slug Optional. The dashboard page slug to check for
	 *
	 * @return bool This is a dashboard page
	 * @since 1.0.0
	 **
	 */
	public function is_dashboard_page( $slug = '' ) {

		// Bail when we're not in the admin
		if ( ! is_admin() ) {
			return false;
		}

		// Bail when the page is not found
		if ( ! isset( $_GET['page'] ) ) {
			return false;
		}

		// Find the current page
		$page = in_array( $_GET['page'], wp_list_pluck( $this->get_pages(), 'slug' ) );

		// Check for a match
		if ( $page && ! empty( $slug ) ) {
			$page = $_GET['page'] === $slug;
		}

		return $page;
	}

	/**
	 * Allow a developer or agency to hide the Dollie Dashboard.
	 * The functionality and license verification is still required (hence the use of CSS)
	 *
	 * @since 1.0.0
	 *
	 */
	public function inline_admin_css() { ?>

		<style>
			a[href="admin.php?page=wefoster-dashboard"] .wp-menu-name {
				visibility: hidden;
				position: relative;
			}

			a[href="admin.php?page=wefoster-dashboard"] .wp-menu-name:after {
				visibility: visible;
				position: absolute;
				left: 37px;
				content: "WeFoster";
			}
		</style>

		<?php
	}

	/**
	 * Enqueue admin scripts
	 *
	 * @since 1.0.0
	 *
	 * @uses wp_register_style()
	 * @uses wp_enqueue_script()
	 * @uses wp_enqueue_style()
	 */
	public function enqueue_scripts() {

		// Get WeFoster
		$wf = Setup::instance();

		// Register styles
		wp_register_style( 'dollie-dashboard-style', $wf->plugin_url . 'assets/css/style.css' );

		// Register a tiny stylesheet for our menus. Inline CSS is to messy.
		wp_register_style( 'dollie-menu-style', $wf->plugin_url . 'assets/css/menu.css' );
		wp_enqueue_style( 'dollie-menu-style' );

		// Bail when this is not one of our dashboard pages
		if ( ! $this->is_dashboard_page() ) {
			return;
		}

		wp_enqueue_script( 'iframe-resizer', $wf->plugin_url . 'assets/js/iframeResizer.min.js' );
		wp_enqueue_script( 'dollie-dashboard-custom', $wf->plugin_url . 'assets/js/custom.js' );

		wp_enqueue_style( 'dollie-dashboard-style' );
	}

	/**
	 * Filter admin body classes
	 *
	 * @param string $class Space-separated class names
	 *
	 * @return string Class names
	 * @since 1.0.0
	 *
	 */
	public function admin_body_class( $class ) {

		// Add a class for our iframe pages
		if ( isset( $_GET['page'] ) && in_array( $_GET['page'], wp_list_pluck( $this->get_pages( array( 'iframe_src' => '' ), 'NOT' ), 'slug' ) ) ) {
			$class .= ' wefoster-dashboard-iframe';
		}

		return $class;
	}

	/**
	 * Display the dasbhoard iframe page
	 *
	 * @since 1.0.0
	 */
	public function render_iframe_page() {

		// Bail when we don't know the page
		if ( ! isset( $_GET['page'] ) || ! isset( $this->pages[ $_GET['page'] ] ) ) {
			return;
		}

		// Get the page details
		$page = $this->pages[ $_GET['page'] ];

		// Bail when the iframe source is missing
		if ( empty( $page->iframe_src ) ) {
			return;
		}

		// Define local variable(s)
		$iframe_src = add_query_arg( array( 'class' => 'wefoster-dashboard' ), $page->iframe_src );

		// Output the loader and iframe HTML
		?>

		<div class="pre-loader show-loader fade-in one loading-message">
			<div class="pre-loader-message">
				<div class="drawing" id="loading">
					<img src="<?php echo esc_url( $page->load_icon ); ?>">
					<div class="the-loading-message">
						<h3><?php echo esc_html( $page->labels['loading'] ); ?></h3>
					</div>
					<div class="loading-dot"></div>
				</div>
			</div>
		</div><!-- .pre-loader -->

		<div class="iframe-wrapper">
			<iframe id="wefoster-dashboard" iFrameResize() class="pre-loader show-loader"
			        src="<?php echo esc_url( $iframe_src ); ?>" width="100%" height="100%" scrolling="no"
			        onload="iFrameResize()"></iframe>
		</div>

		<script>
            // Resize iframe to fit in window width
            //iFrameResize();
		</script>

		<?php
	}
}
