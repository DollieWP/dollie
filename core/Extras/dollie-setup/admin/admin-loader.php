<?php

/**
 * Set up the admin area
 *
 * @since 0.2
 *
 * @package Dollie_Setup
 * @subpackage Adminstration
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Setup the DOLLIE_SETUP admin area.
 *
 * @since 0.2
 */
class Dollie_Setup_Admin {



	/**
	 * Constructor.
	 */
	public function __construct() {
		 // includes
		$this->includes();

		// setup our hooks
		$this->setup_hooks();
	}

	/**
	 * Includes.
	 */
	private function includes() {
		require DOLLIE_SETUP_PLUGIN_DIR . 'admin/functions.php';

		/**
		 * Hook to declare when the DOLLIE_SETUP admin area is loaded at its earliest.
		 *
		 * @since 1.1.0
		 *
		 * @param Dollie_Setup_Admin $this
		 */
		do_action( 'dollie_setup_admin_loaded', $this );
	}

	/**
	 * Setup hooks.
	 */
	private function setup_hooks() {
		// Do certain things on the main site (also accounts for Network Admin)
		if ( dollie_setup_is_main_site() ) {
			add_action( dollie_setup_admin_prop( 'menu' ), array( $this, 'admin_menu' ) );

			// see if an admin notice should be shown
			add_action( 'admin_init', array( $this, 'setup_notice' ) );

			// add an admin notice if DOLLIE_SETUP isn't setup
			add_action( is_network_admin() ? 'network_admin_notices' : 'admin_notices', array( $this, 'display_notice' ) );



		}
	}


	/** ACTIONS / SCREENS *********************************************/

	/**
	 * Catches form submissions from the DOLLIE_SETUP dashboard and sets
	 * some reference pointers depending on the type of submission.
	 *
	 * @since 0.3
	 */
	public function catch_form_submission() {
		// no package / reset package.
		if ( isset( $_REQUEST['dollie_setup-package'] ) ) {
			// verify nonce
			check_admin_referer( 'dollie_setup_select_package' );

			// We want to select a new package.
			if ( empty( $_REQUEST['dollie_setup-package'] ) ) {
				$current = get_site_option( '_dollie_setup_current_package' );
				if ( ! empty( $current ) ) {
					/**
					 * Hook to do something when a package is about to be deactivated.
					 *
					 * This is a dynamic hook based on the package ID name.
					 *
					 * @since 1.1.0
					 */
					do_action( "dollie_setup_package_{$current}_deactivation" );
				}

				delete_site_option( '_dollie_setup_current_package' );
				delete_site_option( '_dollie_setup_revision_date' );

				// We've selected a package.
			} else {
				update_site_option( '_dollie_setup_current_package', $_REQUEST['dollie_setup-package'] );
			}

			// Redirect to required plugins installation, if necessary.
			if ( 'required-plugins' === dollie_setup_get_setup_step() ) {
				$url = self_admin_url( 'admin.php?page=dollie_setup&dollie_setup-virgin-setup=1&dollie_setup-virgin-nonce=' . wp_create_nonce( 'dollie_setup_virgin_setup' ) );
			} else {
				$url = self_admin_url( 'admin.php?page=dollie_setup' );
			}

			wp_redirect( $url );
			die();

			// Package details.
		} elseif ( ! empty( $_GET['dollie_setup-package-details'] ) ) {
			// verify nonce
			check_admin_referer( 'dollie_setup_package_details' );

			dollie_setup()->setup = 'package-details';

			// virgin setup
		}
		elseif ( ! empty( $_GET['dollie_setup-action'] ) && 'complete' === $_GET['dollie_setup-action'] && ! dollie_setup_get_installed_revision_date() ) {
			dollie_setup_bump_revision_date();

			wp_redirect( self_admin_url( 'admin.php?page=dollie_setup' ) );
			die();
		}

		// Redirect to certain pages if necessary.
		if ( ! dollie_setup_is_setup() && empty( $_GET['dollie_setup-action'] ) ) {
			$redirect = '';
			switch ( dollie_setup_get_setup_step() ) {
				case '':
					dollie_setup_bump_revision_date();
					$redirect = self_admin_url( 'admin.php?page=dollie_setup' );
					break;
			}

			if ( '' !== $redirect ) {
				wp_redirect( $redirect );
				die();
			}
		}

		// Remove admin notice during setup mode.
		$is_setup = isset( dollie_setup()->setup ) ? dollie_setup()->setup : false;
		if ( $is_setup ) {
			remove_action( is_network_admin() ? 'network_admin_notices' : 'admin_notices', array( $this, 'display_notice' ) );
		}
	}

	/**
	 * Setup screen.
	 *
	 * @since 0.3
	 */
	private function setup_screen() {
		// do something different for each DOLLIE_SETUP setup condition
		switch ( dollie_setup()->setup ) {
				/*
			 * Required plugins installation.
			 *
			 * 'virgin-setup' is a misnomer when times were simpler :)
			 */
			case 'virgin-setup':
				// get required Dollie plugins.
				$plugins = Dollie_Setup_Plugins::get_plugins( 'required' );

				// sort plugins by plugin state
				$plugins = Dollie_Setup_Admin_Plugins::organize_plugins_by_state( $plugins );

				// Check for recommended plugins.
				$recommended = Dollie_Setup_Admin_Plugins::organize_plugins_by_state( Dollie_Setup_Plugins::get_plugins( 'recommended' ) );
				unset( $recommended['deactivate'] );

				// include the DOLLIE_SETUP Plugin Upgrade and Install API
				if ( ! class_exists( 'Dollie_Setup_Plugin_Upgrader' ) ) {
					require DOLLIE_SETUP_PLUGIN_DIR . 'admin/plugin-install.php';
				}

				// some HTML markup!
				echo '<div class="wrap">';
				echo '<h2>' . esc_html__( 'Installing Required Plugins', 'dollie-setup' ) . '</h2>';

				// Start the installer.
				$options = array();
				if ( ! dollie_setup_get_installed_revision_date() ) {
					if ( ! empty( $recommended ) ) {
						$options = array(
							'redirect_link' => self_admin_url( 'admin.php?page=dollie_setup&dollie_setup-virgin-setup=1&dollie_setup-virgin-nonce=' . wp_create_nonce( 'dollie_setup_virgin_setup' ) ),
							'redirect_text' => __( 'Continue to recommended plugins', 'dollie-setup' ),
						);

						// Add theme step if recommended plugins are already active.
					} elseif ( dollie_setup_get_theme_prop( 'download_url' ) && dollie_setup_get_theme_prop( 'directory_name' ) !== dollie_setup_get_theme()->template ) {
						$options = array(
							'redirect_link' => wp_nonce_url( self_admin_url( 'admin.php?page=dollie_setup&amp;dollie_setup-action=theme-prompt' ), 'dollie_setup_theme_prompt' ),
							'redirect_text' => __( 'Continue to theme installation', 'dollie-setup' ),
						);
					}
				} else {
					$options = array(
						'redirect_link' => self_admin_url( 'admin.php?page=dollie_setup' ),
						'redirect_text' => __( 'Continue to dashboard', 'dollie-setup' ),
					);
				}

				$installer = new Dollie_Setup_Updater( $plugins, $options );

				echo '</div>';

				break;
			case 'package-details':
				$package = sanitize_title( $_GET['dollie_setup-package-details'] );
				// some HTML markup!
				echo '<div class="wrap">';
				echo '<h2>' . sprintf( esc_html__( 'Continue the Setup', 'dollie-setup' ), dollie_setup_get_package_prop( 'name', $package ) ) . '</h2>';

				dollie_setup_get_template_part( 'package-details-intro', $package );
				dollie_setup_get_template_part( 'package-details', $package );
				?>


				<!-- <form method="post" action="<?php echo self_admin_url( 'admin.php?page=dollie_setup' ); ?>" style="margin-top:2em; text-align:right;">
					<?php wp_nonce_field( 'dollie_setup_select_package' ); ?>

					<input type="hidden" name="dollie_setup-package" value="<?php echo $package; ?>" />

					<a class="button button-secondary" href="<?php echo self_admin_url( 'admin.php?page=dollie_setup' ); ?>" style="margin:0 15px 0 0;"><?php esc_html_e( 'Return to dashboard', 'dollie-setup' ); ?></a>

					<input type="submit" value="<?php esc_html_e( 'Continue with ' . $_GET['dollie_setup-package-details'] . ' Setup', 'dollie-setup' ); ?>" class="button-primary" name="package-details" />
				</form> -->


				<?php
				echo '</div>';

				break;

		}
	}

	/** ADMIN PAGE-SPECIFIC *******************************************/

	/**
	 * Setup admin menu and any dependent page hooks.
	 */
	public function admin_menu() {
		$name = __( 'Dollie Hub', 'dollie-setup' );
		$page = add_menu_page(
			$name,
			$name,
			'install_plugins', // todo - map cap?
			'dollie_setup',
			array( $this, 'admin_page' ),
			'none',
			2
		);

		$dashboard = dollie_setup_get_package_prop( 'name' ) ? __( 'Settings', 'dollie-setup' ) : __( 'Dollie Setup', 'dollie-setup' );

		$subpage = add_submenu_page(
			'dollie_setup',
			$dashboard,
			$dashboard,
			'install_plugins', // todo - map cap?
			'dollie_setup',
			array( $this, 'admin_page' )
		);

		/**
		 * Hook to do so something during DOLLIE_SETUP admin menu registration.
		 *
		 * @since 1.0-beta1
		 */
		do_action( 'dollie_setup_admin_menu' );

		$package_id = dollie_setup_get_current_package_id();
		if ( ! empty( $package_id ) ) {
			/**
			 * Admin menu hook for the current active package.
			 *
			 * @since 1.1.0
			 */
			do_action( "dollie_setup_{$package_id}_admin_menu" );
		}

		// catch form submission
		add_action( "load-{$subpage}", array( $this, 'catch_form_submission' ) );
	}

	/**
	 * The main dashboard page.
	 */
	public function admin_page() {
		$is_setup = isset( dollie_setup()->setup ) ? dollie_setup()->setup : false;

		// what's new page
		if ( $this->is_changelog() ) {
			dollie_setup_get_template_part( 'changelog' );

			// setup screen
		} elseif ( $is_setup ) {
			$this->setup_screen();

			// regular screen should go here
		} else {
			?>
			<div class="wrap">
				<h2><?php _e( 'Dollie Setup Dashboard', 'dollie-setup' ); ?></h2>

				<?php $this->steps(); ?>

				<?php $this->metaboxes(); ?>
				<?php $this->about(); ?>
			</div>
			<?php
		}
	}

	/**
	 * Should we show the changelog screen?
	 *
	 * @return bool
	 */
	private function is_changelog() {
		if ( ! empty( $_GET['whatsnew'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * DOLLIE_SETUP setup steps.
	 *
	 * This shows up when DOLLIE_SETUP hasn't completed setup yet.
	 *
	 * @since 0.3
	 *
	 * @uses dollie_setup_is_setup() To tell if DOLLIE_SETUP is fully setup.
	 * @uses dollie_setup_is_upgraded() To check if DOLLIE_SETUP just upgraded.
	 * @uses dollie_setup_get_setup_step() Which setup step is DOLLIE_SETUP at?
	 */
	private function steps() {
		// if DOLLIE_SETUP is already setup, stop now!
		if ( dollie_setup_is_setup() ) {
			return;
		}

		// stop if DOLLIE_SETUP just upgraded
		if ( dollie_setup_is_upgraded() ) {
			return;
		}

		// do something different depending on the setup step
		dollie_setup_get_template_part( 'wrapper-header' );
		switch ( dollie_setup_get_setup_step() ) {
				// (0) No package.
			case 'no-package':
				?>

				<?php
				// Load our Package Template
				dollie_setup_get_template_part( 'wizard' );
				dollie_setup_get_template_part( 'packages' );
				?>

				<?php
				break;

				// (1) required plugins need to be installed/upgraded first if necessary.
			case 'required-plugins':
				?>

				<h2><?php _e( 'Redirect to Plugin Installer', 'dollie-setup' ); ?></h2>


				<?php
				break;

				// (2) next, recommended plugins are offered if available.
			case 'recommended-plugins':
				?>

				<h2><?php _e( 'Redirect to Demo Setup', 'dollie-setup' ); ?></h2>


				<?php
				break;
		} // end switch()
		dollie_setup_get_template_part( 'wrapper-footer' );
	}


	/**
	 * Metaboxes.
	 *
	 * These are quick action links for the admin to do stuff.
	 * Note: These metaboxes only show up when DOLLIE_SETUP has finished setting up.
	 *
	 * @since 0.3
	 *
	 * @uses dollie_setup_is_setup() To tell if DOLLIE_SETUP is fully setup.
	 */
	private function metaboxes() {
		if ( ! dollie_setup_is_setup() ) {
			return;
		}

		dollie_setup_get_template_part( 'dashboard' );
	}

	/**
	 * About section.
	 *
	 * This only shows up when DOLLIE_SETUP is fully setup.
	 *
	 * @since 0.3
	 *
	 * @uses dollie_setup_is_setup() To tell if DOLLIE_SETUP is fully setup.
	 */
	private function about() {
		if ( ! dollie_setup_is_setup() ) {
			return;
		}

		dollie_setup_get_template_part( 'footer' );
	}

	/** HEADER INJECTIONS *********************************************/

	/**
	 * Setup internal variable if the admin notice should be shown.
	 *
	 * @since 0.3
	 *
	 * @uses dollie_setup_is_setup() To tell if DOLLIE_SETUP is fully setup.
	 * @uses current_user_can() Check if the current user has the permission to do something.
	 * @uses is_multisite() Check to see if WP is in network mode.
	 */
	public function setup_notice() {
		// if DOLLIE_SETUP is setup, stop now!
		if ( ! dollie()->auth()->is_connected() || dollie_setup_is_setup() ) {
			return;
		}

		// only show notice if we're either a super admin on a network or an admin on a single site
		$show_notice = current_user_can( 'manage_network_plugins' ) || ( ! is_multisite() && current_user_can( 'install_plugins' ) );

		if ( ! $show_notice ) {
			return;
		}

		dollie_setup()->show_notice = true;
	}

	/**
	 * Show an admin notice if DOLLIE_SETUP hasn't finished setting up.
	 *
	 * @since 0.3
	 *
	 * @uses dollie_setup_get_setup_step() Which setup step is DOLLIE_SETUP at?
	 */
	public function display_notice() {
		// If our notice marker isn't set or if we're on the DOLLIE_SETUP page, stop now!
		$show_notice = isset( dollie_setup()->show_notice ) ? dollie_setup()->show_notice : false;
		if ( ! $show_notice || 'dollie_setup' === get_current_screen()->parent_base || ( method_exists( dollie(), 'auth' ) && dollie()->auth()->is_connected() ) ) {
			return;
		}

		// setup some variables depending on the setup step
		switch ( dollie_setup_get_setup_step() ) {
			case 'no-package':
			case 'required-plugins':
				$notice_header = __( '<span></span>Dollie Hub - Let\'s Get Started', 'dollie-setup' );

				if ( defined( 'S5_APP_TOKEN' ) ) {
					$notice_text = __( 'Your brand new WordPress site is now set up hosted on the Dollie Private Cloud. But before you can start building your platform we need to install some required plugins. ', 'dollie-setup' );
				} else {
					$notice_text = __( 'Your Hub is almost ready! But before you can start building your platform we need to install some required plugins. ', 'dollie-setup' );
				}

				$button_link = dollie_setup_admin_prop( 'url', 'admin.php?page=dollie_setup' );
				$button_text = __( 'Start the Setup', 'dollie-setup' );
				break;


			case 'recommended-plugins':
				$notice_header = __( '<span>Plugins</span> Recommended Plugins Setup', 'dollie-setup' );
				$notice_text   = __( 'You only have one last thing to do. We promise!', 'dollie-setup' );
				$button_link   = dollie_setup_admin_prop( 'url', 'admin.php?page=dollie_setup' );
				$button_text   = __( 'Click here to finish up!', 'dollie-setup' );
				break;


			default:
				return;
				break;
		}

		// change variables if we're still in setup phase
		$is_setup = isset( dollie_setup()->setup ) ? dollie_setup()->setup : false;
		if ( $is_setup ) {
			if ( 'upgrade-theme' === dollie_setup()->setup ) {
				$notice_text = __( 'Upgrading theme...', 'dollie-setup' );
			} else {
				$notice_text = __( 'Installing plugins...', 'dollie-setup' );
			}
		}
		?>

		<div id="dollie_setup-steps" class="notice dollie-notice dollie-setup">
			<svg xmlns="http://www.w3.org/2000/svg" fill="#33D399" viewBox="0 0 24 24" stroke="currentColor">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
					  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
					  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
			</svg>
			<h3><?php echo $notice_header; ?></h3>

			<p><?php echo $notice_text; ?></p>


			<?php if ( empty( $_REQUEST['page'] ) || ( ! empty( $_REQUEST['page'] ) && 'dollie_setup' !== $_REQUEST['page'] ) ) : ?>
				<div class="dollie-msg-button-right">
					<a class="callout" href="<?php echo $button_link; ?>"><?php echo $button_text; ?></a>
				</div>
			<?php endif; ?>
		</div>

		<?php
	}


}
