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

			// after installing a theme, do something
			add_action( 'admin_init', array( $this, 'theme_activation_hook' ) );

			// Upgrader page.
			add_action( 'dollie_setup_admin_menu', array( $this, 'upgrader' ), 0 );
		}

		// add a special header on the admin plugins page
		add_action( 'pre_current_active_plugins', array( $this, 'plugins_page_header' ) );
	}

	/**
	 * Set up upgrader page only if there are items to upgrade.
	 *
	 * @since 1.2.0
	 */
	public function upgrader() {
		// Ensure we're on a DOLLIE_SETUP page.
		if ( empty( $_GET['page'] ) || false === strpos( $_GET['page'], 'dollie_setup' ) ) {
			return;
		}

		require DOLLIE_SETUP_PLUGIN_DIR . 'admin/upgrades/pages.php';
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
		} elseif ( ! empty( $_REQUEST['dollie_setup-virgin-setup'] ) ) {
			// verify nonce
			check_admin_referer( 'dollie_setup_virgin_setup', 'dollie_setup-virgin-nonce' );

			// set reference pointer for later use
			dollie_setup()->setup = 'virgin-setup';

			$url  = '';
			$step = dollie_setup_get_setup_step();

			// Redirect to a specific installation step, if necessary.
			if ( '' === dollie_setup_get_setup_step() ) {
				if ( dollie_setup_get_theme_prop( 'download_url' ) && dollie_setup_get_theme_prop( 'directory_name' ) !== dollie_setup_get_theme()->template ) {
					$url = self_admin_url( 'admin.php?page=dollie_setup&dollie_setup-action=theme-prompt&_wpnonce=' . wp_create_nonce( 'dollie_setup_theme_prompt' ) );
				}
			}

			// Required plugins are already installed.
			if ( '' === $url && 'required-plugins' !== $step ) {
				$url = self_admin_url( 'admin.php?page=dollie_setup' );
			}

			if ( $url ) {
				wp_redirect( $url );
				die();
			}

			// BP installed, but no DOLLIE_SETUP
		} elseif ( ! empty( $_REQUEST['dollie_setup-recommended-nonce'] ) ) {
			// verify nonce
			check_admin_referer( 'dollie_setup_bp_installed', 'dollie_setup-recommended-nonce' );

			// set reference pointer for later use
			dollie_setup()->setup = 'install';

			// If no plugins to install, redirect back to DOLLIE_SETUP dashboard
			if ( empty( $_REQUEST['dollie_setup_plugins'] ) ) {
				// DOLLIE_SETUP and DOLLIE_SETUP theme hasn't been installed ever, so prompt for install.
				if ( ! dollie_setup_get_installed_revision_date() && dollie_setup_get_theme_prop( 'directory_name' ) !== dollie_setup_get_theme()->template ) {
					dollie_setup()->setup = 'theme-prompt';

					// Bump the revision date in the DB after updating
				} else {
					add_action(
						'dollie_setup_after_updater',
						function () {
							dollie_setup_bump_revision_date();
						}
					);
					do_action( 'dollie_setup_after_updater' );

					wp_redirect( self_admin_url( 'admin.php?page=dollie_setup' ) );
					exit;
				}
			}

			// plugin upgrades available
		} elseif ( ! empty( $_REQUEST['dollie_setup-action'] ) && $_REQUEST['dollie_setup-action'] == 'upgrade' ) {
			// verify nonce
			check_admin_referer( 'dollie_setup_upgrade' );

			// set reference pointer for later use
			dollie_setup()->setup = 'upgrade';

			if ( ! empty( $_REQUEST['dollie_setup-themes'] ) ) {
				dollie_setup()->theme_upgrades = $_REQUEST['dollie_setup-themes'];
			}

			// bump the revision date in the DB after updating
			add_action(
				'dollie_setup_after_updater',
				function () {
					dollie_setup_bump_revision_date();
				}
			);

			// theme prompt
		} elseif ( ! empty( $_REQUEST['dollie_setup-action'] ) && $_REQUEST['dollie_setup-action'] == 'theme-prompt' ) {
			check_admin_referer( 'dollie_setup_theme_prompt' );

			// DOLLIE_SETUP theme doesn't exist, so set reference pointer for later use
			dollie_setup()->setup = 'theme-prompt';

			// bump the revision date in the DB after updating
			add_action(
				'dollie_setup_after_updater',
				function () {
					dollie_setup_bump_revision_date();
				}
			);

			// install DOLLIE_SETUP theme
		} elseif ( ! empty( $_REQUEST['dollie_setup-action'] ) && $_REQUEST['dollie_setup-action'] == 'install-theme' ) {
			// verify nonce
			check_admin_referer( 'dollie_setup_install_theme' );

			// get dollie_setup theme
			$theme = dollie_setup_get_theme( dollie_setup_get_theme_prop( 'directory_name' ) );

			// DOLLIE_SETUP theme exists! so let's activate it and redirect to the
			// Dollie Recommended Theme options page!
			if ( $theme->exists() ) {
				// if BP_ROOT_BLOG is defined and we're not on the root blog, switch to it
				if ( ! dollie_setup_is_main_site() ) {
					switch_to_blog( dollie_setup_get_main_site_id() );
					$switched = true;
				}

				// switch the theme
				switch_theme( dollie_setup_get_theme_prop( 'directory_name' ), dollie_setup_get_theme_prop( 'directory_name' ) );

				// restore blog after switching
				if ( ! empty( $switched ) ) {
					restore_current_blog();
					unset( $switched );
				}

				// Mark the theme as having just been activated
				// so that we can run the setup on next pageload
				update_site_option( '_dollie_setup_theme_activated', '1' );

				wp_redirect( self_admin_url( 'admin.php?page=dollie_setup' ) );
				return;
			}

			// DOLLIE_SETUP theme doesn't exist, so set reference pointer for later use
			dollie_setup()->setup = 'install-theme';

			// theme upgrades available
		} elseif ( ! empty( $_REQUEST['dollie_setup-action'] ) && $_REQUEST['dollie_setup-action'] == 'upgrade-theme' ) {
			// verify nonce
			check_admin_referer( 'dollie_setup_upgrade_theme' );

			// set reference pointers for later use
			dollie_setup()->setup          = 'upgrade-theme';
			dollie_setup()->theme_upgrades = $_REQUEST['dollie_setup-themes'];
		}

		// Complete step.
		if ( ! empty( $_GET['dollie_setup-action'] ) && 'complete' === $_GET['dollie_setup-action'] && ! dollie_setup_get_installed_revision_date() ) {
			dollie_setup_bump_revision_date();

			wp_redirect( self_admin_url( 'admin.php?page=dollie_setup' ) );
			die();
		}

		// Redirect to certain pages if necessary.
		if ( ! dollie_setup_is_setup() && empty( $_GET['dollie_setup-action'] )  ) {
			$redirect = '';
			switch ( dollie_setup_get_setup_step() ) {
				case 'required-plugins':
					// Set setup flag for required plugins page.
					dollie_setup()->setup = 'virgin-setup';
					break;

				case 'plugin-update':
					$redirect = add_query_arg( '_wpnonce', wp_create_nonce( 'dollie_setup_upgrade' ), dollie_setup_admin_prop( 'url', 'admin.php?page=dollie_setup&dollie_setup-action=upgrade' ) );
					break;

				case 'theme-prompt':
					$redirect = add_query_arg( '_wpnonce', wp_create_nonce( 'dollie_setup_theme_prompt' ), dollie_setup_admin_prop( 'url', 'admin.php?page=dollie_setup&dollie_setup-action=theme-prompt' ) );
					break;

				case 'theme-update':
					$redirect = dollie_setup_admin_prop( 'url', 'admin.php?page=dollie_setup&dollie_setup-action=upgrade-theme&dollie_setup-themes=' . esc_attr( dollie_setup_get_theme_prop( 'directory_name' ) ) );
					$redirect = add_query_arg( '_wpnonce', wp_create_nonce( 'dollie_setup_upgrade_theme' ), $redirect );
					break;

				case 'upgrades-available':
					$redirect = dollie_setup_admin_prop( 'url', 'admin.php?page=dollie_setup-upgrades' );
					break;

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

				// Installed, but haven't run through setup.
			case 'install':
				$plugins = $_REQUEST['dollie_setup_plugins'];

				// include the DOLLIE_SETUP Plugin Upgrade and Install API
				if ( ! class_exists( 'Dollie_Setup_Plugin_Upgrader' ) ) {
					require DOLLIE_SETUP_PLUGIN_DIR . 'admin/plugin-install.php';
				}

				// some HTML markup!
				echo '<div class="wrap">';
				echo '<h2>' . esc_html__( 'Installing Selected Plugins', 'dollie-setup' ) . '</h2>';

				// Prompt for theme install afterwards, if available.
				if ( dollie_setup_get_theme_prop( 'download_url' ) && dollie_setup_get_theme_prop( 'directory_name' ) !== dollie_setup_get_theme()->template ) {
					$url  = wp_nonce_url( self_admin_url( 'admin.php?page=dollie_setup&amp;dollie_setup-action=theme-prompt' ), 'dollie_setup_theme_prompt' );
					$text = __( 'Continue to theme installation', 'dollie-setup' );
				} else {
					$url  = self_admin_url( 'admin.php?page=dollie_setup' );
					$text = __( 'Continue to Your Dashboard', 'dollie-setup' );
				}

				// start the install!
				$installer = new Dollie_Setup_Updater(
					$plugins,
					array(
						'redirect_link' => $url,
						'redirect_text' => $text,
					)
				);

				echo '</div>';

				break;

				// upgrading installed plugins
			case 'upgrade':
				// setup our upgrade plugins array
				$plugins['upgrade'] = Dollie_Setup_Admin_Plugins::get_upgrades( 'active' );

				// if theme upgrades are available, let's add an extra button to the end of
				// the plugin upgrader, so we can proceed with upgrading the theme
				if ( dollie_setup_get_theme_to_update() ) {
					$title = esc_html__( 'Upgrading Dollie Plugin Suite and Themes', 'dollie-setup' );

					$redirect_link = wp_nonce_url( self_admin_url( 'admin.php?page=dollie_setup&dollie_setup-action=upgrade-theme&dollie_setup-themes=' . dollie_setup_get_theme_prop( 'directory_name' ) ), 'dollie_setup_upgrade_theme' );
					$redirect_text = sprintf( __( "Now, let's upgrade the %s theme &rarr;", 'dollie-setup' ), esc_attr( dollie_setup_get_theme_prop( 'name' ) ) );
				} else {
					$title = esc_html__( 'Upgrading Dollie Plugin Suite', 'dollie-setup' );

					$redirect_link = self_admin_url( 'admin.php?page=dollie_setup' );
					$redirect_text = __( 'Continue to Your Dashboard', 'dollie-setup' );
				}

				// include the DOLLIE_SETUP Plugin Upgrade and Install API
				if ( ! class_exists( 'Dollie_Setup_Plugin_Upgrader' ) ) {
					require DOLLIE_SETUP_PLUGIN_DIR . 'admin/plugin-install.php';
				}

				// some HTML markup!
				echo '<div class="wrap">';
				echo '<h2>' . $title . '</h2>';

				// start the upgrade!
				$installer = new Dollie_Setup_Updater(
					$plugins,
					array(
						'redirect_link' => $redirect_link,
						'redirect_text' => $redirect_text,
					)
				);

				echo '</div>';

				break;

				// prompt for theme install
			case 'theme-prompt':

				dollie_setup_get_template_part( 'wrapper-header' );
				$directory_name = dollie_setup_get_theme_prop( 'directory_name' );
				$current_theme  = wp_get_theme();

				if ( $current_theme->get('Name') == 'Hello Dollie' && ! get_option( '_dollie_setup_theme_activated' ) ) {
					update_site_option( '_dollie_setup_theme_activated', '1' );
					wp_redirect( self_admin_url( 'admin.php?page=dollie_setup&dollie_setup-action=complete') );
					exit;
				}

				// Button text.
				if ( ! empty( $directory_name ) && dollie_setup_get_theme( $directory_name )->exists() ) {
					$btn_text = esc_html__( 'Activate Theme', 'dollie-setup' );
				} else {
					$btn_text = esc_html__( 'Install & Activate Hello Dollie Theme', 'dollie-setup' );
				}

				// Theme needs to be force-installed.
				if ( dollie_setup_get_theme_prop( 'force_install' ) ) {
					$bail_text = esc_html__( 'Return to package selection', 'dollie-setup' );
					$bail_link = esc_url( wp_nonce_url( self_admin_url( 'admin.php?page=dollie_setup&amp;dollie_setup-package=0' ), 'dollie_setup_select_package' ) );
					$warning   = sprintf( __( 'Please note: This theme is <strong>required</strong> for use with Dollie Setup %s.', 'dollie-setup' ), dollie_setup_get_package_prop( 'name' ) );
					$warning   = sprintf( '<p>%s</p>', $warning );

					// Theme installation is optional.
				} else {
					$bail_text = esc_html__( 'Keep Using ', 'dollie-setup' ) . ' ' . esc_html( $current_theme->get( 'Name' ) . ' Theme' );
					$bail_link = self_admin_url( 'admin.php?page=dollie_setup&amp;dollie_setup-action=complete' );
				}

				// some HTML markup!
				echo '<div class="wrap">';

				echo '<h2>' . esc_html__( 'Theme Installation', 'dollie-setup' ) . '</h2>';

				dollie_setup_get_template_part( 'theme-prompt' );

				echo '<div style="margin-top:2em;">';

				printf( '<a href="%1$s" class="button button-primary">%2$s</a>', wp_nonce_url( self_admin_url( 'admin.php?page=dollie_setup&amp;dollie_setup-action=install-theme' ), 'dollie_setup_install_theme' ), $btn_text );

				printf( '<a class="dol-btn dol-bg-base-200 dol-ml-3 dol-text-gray-400" href="%1$s">%2$s</a>', $bail_link, $bail_text );

				echo '</div>';

				echo '</div>';

				dollie_setup_get_template_part( 'wrapper-footer' );

				break;

				// install the dollie_setup theme
			case 'install-theme':
				// dollie_setup_get_template_part('wrapper-header');
				// include the Dollie Recommended Theme Installer
				if ( ! class_exists( 'Dollie_Setup_Theme_Installer' ) ) {
					require DOLLIE_SETUP_PLUGIN_DIR . 'admin/theme-install.php';
				}

				$title = sprintf( _x( 'Installing %s theme', 'references the theme that is currently being installed', 'dollie-setup' ), dollie_setup_get_theme_prop( 'name' ) );

				$dollie_setup_theme = new Dollie_Setup_Theme_Installer( new Theme_Installer_Skin( compact( 'title' ) ) );
				$dollie_setup_theme->install();

				// dollie_setup_get_template_part('wrapper-footer');

				break;

				// upgrade DOLLIE_SETUP themes
			case 'upgrade-theme':
				dollie_setup_get_template_part( 'wrapper-header' );
				// include the Dollie Recommended Theme Installer
				if ( ! class_exists( 'Dollie_Setup_Theme_Installer' ) ) {
					require DOLLIE_SETUP_PLUGIN_DIR . 'admin/theme-install.php';
				}

				// some HTML markup!
				echo '<div class="wrap">';
				echo '<h2>' . esc_html__( 'Upgrading Theme', 'dollie-setup' ) . '</h2>';

				// get dollie_setup theme specs
				$upgrader = new Dollie_Setup_Theme_Installer( new Bulk_Theme_Upgrader_Skin() );

				// Modifies the theme action links that get displayed after theme installation
				// is complete.
				add_filter( 'update_bulk_theme_complete_actions', array( $upgrader, 'remove_theme_actions' ) );

				$upgrader->bulk_upgrade( dollie_setup()->theme_upgrades );

				echo '</div>';
				dollie_setup_get_template_part( 'wrapper-footer' );

				break;
		}
	}

	/**
	 * Do something just after a theme is activated on the next page load.
	 *
	 * @since 1.0-beta1
	 */
	public function theme_activation_hook() {
		if ( get_site_option( '_dollie_setup_theme_activated' ) ) {
			delete_site_option( '_dollie_setup_theme_activated' );

			/**
			 * Do something just after a theme is activated on the next page load.
			 *
			 * This is a dynamic hook, based off of the current package ID.
			 *
			 * @since 1.1.0
			 */
			do_action( 'dollie_setup_' . dollie_setup_get_current_package_id() . '_theme_activated' );

			// DOLLIE_SETUP finished updating, but DB version not saved; do it now.
			if ( ! dollie_setup_get_installed_revision_date() ) {
				dollie_setup_bump_revision_date();
			}
		}
	}

	/** ADMIN PAGE-SPECIFIC *******************************************/

	/**
	 * Setup admin menu and any dependent page hooks.
	 */
	public function admin_menu() {
		$name = __( 'Your Hub', 'dollie-setup' );
		$page = add_menu_page(
			'Dashboard',
			'Your Hub',
			'install_plugins', // todo - map cap?
			'dollie_setup',
			array( $this, 'admin_page' ),
			'none',
			2
		);

		// $dashboard = dollie_setup_get_package_prop( 'name' ) ? sprintf( __( '%s Dashboard', 'dollie-setup' ), dollie_setup_get_package_prop( 'name' ) ) : __( 'Dollie Setup', 'dollie-setup' );

		// $subpage = add_page(
		// 	'dollie_setup',
		// 	$dashboard,
		// 	$dashboard,
		// 	'install_plugins', // todo - map cap?
		// 	'dollie_setup',
		// 	array( $this, 'admin_page' )
		// );

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
				<?php $this->upgrades(); ?>
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

				<h2><?php _e( 'Required Plugins', 'dollie-setup' ); ?></h2>

				<form method="post" action="<?php echo self_admin_url( 'admin.php?page=dollie_setup' ); ?>">
					<p class="submitted-on"><?php printf( __( "Before you can use Dollie Setup %s, we'll need to install some required plugins. Click 'Continue' to get set up.", 'dollie-setup' ), dollie_setup_get_package_prop( 'name' ) ); ?></p>

					<?php wp_nonce_field( 'dollie_setup_virgin_setup', 'dollie_setup-virgin-nonce' ); ?>

					<p><input type="submit" value="<?php _e( 'Continue &rarr;', 'dollie-setup' ); ?>" class="button-primary" name="dollie_setup-virgin-setup" /></p>
				</form>

				<?php
				break;

				// (2) next, recommended plugins are offered if available.
			case 'recommended-plugins':
				?>

				<h2><?php _e( 'Recommended Plugins', 'dollie-setup' ); ?></h2>

				<form id="dollie_setup-recommended" method="post" action="<?php echo self_admin_url( 'admin.php?page=dollie_setup' ); ?>">
					<p class="submitted-on"><?php _e( "You're almost finished with the installation process.", 'dollie-setup' ); ?></p>

					<p class="submitted-on"><?php printf( __( 'Did you know Dollie Setup %s comes prebundled with a few recommended plugins?  These plugins help to add functionality to your existing WordPress site.', 'dollie-setup' ), dollie_setup_get_package_prop( 'name' ) ); ?>

					<p class="submitted-on"><?php _e( "We have automatically selected the following plugins to install for you. However, feel free to uncheck some of these plugins based on your site's needs.", 'dollie-setup' ); ?></p>

					<?php wp_nonce_field( 'dollie_setup_bp_installed', 'dollie_setup-recommended-nonce' ); ?>

					<?php
					Dollie_Setup_Admin_Plugins::render_plugin_table(
						array(
							'type'            => 'recommended',
							'omit_activated'  => true,
							'check_all'       => true,
							'submit_btn_text' => __( 'Continue', 'dollie-setup' ),
						)
					);
					?>
				</form>

				<script>
					jQuery(function() {
						dollie_setupRecommendedChecked();
						jQuery("#dollie_setup-recommended input[type='checkbox']").change(function() {
							dollie_setupRecommendedChecked();
						})
					})

					function dollie_setupRecommendedChecked() {
						if (jQuery("#dollie_setup-recommended input:checked").length > 0) {
							jQuery("#dollie_setup-update-recommended").val("<?php echo esc_html( 'Install Selected Plugins', 'dollie-setup' ); ?>");
						} else {
							jQuery("#dollie_setup-update-recommended").val("<?php echo esc_html( 'Continue without Installing...', 'dollie-setup' ); ?>");
						}
					}
				</script>

				<?php
				break;
		} // end switch()
		dollie_setup_get_template_part( 'wrapper-footer' );
	}

	/**
	 * Upgrade notice.
	 *
	 * Displays a notice if WordPress needs to be updated to the DOLLIE_SETUP
	 * recommended version.
	 *
	 * @since 0.3
	 * @since 1.2.0 Now only shows if WordPress should be updated or not.
	 */
	private function upgrades() {
		// get plugin dependency requirements
		$requirements = Plugin_Dependencies::get_requirements();

		// check DOLLIE_SETUP plugin header's 'Core' header for version requirements
		// if exists, WordPress needs to be upgraded
		if ( ! empty( $requirements['Dollie Setup']['core'] ) ) {
			$version = $requirements['Dollie Setup']['core'];
			?>

			<div id="dollie_setup-upgrades" class="secondary-panel">
				<h2><?php _e( 'Upgrade Available', 'dollie-setup' ); ?></h2>

				<div class="login postbox">
					<div class="message">
						<p><?php printf( __( 'Dollie Setup %1$s requires WordPress %2$s', 'dollie-setup' ), dollie_setup_get_version(), $version ); ?>
							<br />
							<a class="button-secondary" href="<?php echo network_admin_url( 'update-core.php' ); ?>"><?php _e( 'Upgrade now!', 'dollie-setup' ); ?></a>
						</p>
					</div>
				</div>
			</div>

			<?php
			return;
		}
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
		if ( dollie_setup_is_setup() ) {
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
		if ( ! $show_notice || 'dollie_setup' === get_current_screen()->parent_base || dollie()->is_api_connected() ) {
			return;
		}


		// setup some variables depending on the setup step
		switch ( dollie_setup_get_setup_step() ) {
			case 'no-package':
			case 'required-plugins':
				$notice_header= __( '<span></span>Dollie Hub - Let\'s Get Started', 'dollie-setup');

				if (defined('S5_APP_TOKEN')) {
						$notice_text= __( 'Your brand new WordPress site is now set up hosted on the Dollie Cloud. But before you can start building your platform we need to install some required plugins. ', 'dollie-setup');
				} else {
					$notice_text= __( 'Your Hub is almost ready! But before you can start building your platform we need to install some required plugins. ', 'dollie-setup');
				}

				$button_link = dollie_setup_admin_prop( 'url', 'admin.php?page=dollie_setup' );
				$button_text = __( 'Start the Setup', 'dollie-setup' );
				break;

			case 'theme-update':
				$notice_header= __( '<span>Theme</span> Update Available', 'dollie-setup');
				$notice_text = sprintf( __( 'The %1$s theme needs an update.', 'dollie-setup' ), esc_attr( dollie_setup_get_theme_prop( 'name' ) ) );
				$button_text = __( 'Update the theme &rarr;', 'dollie-setup' );

				$button_link = dollie_setup_admin_prop( 'url', 'admin.php?page=dollie_setup&dollie_setup-action=upgrade-theme&dollie_setup-themes=' . esc_attr( dollie_setup_get_theme_prop( 'directory_name' ) ) );
				$button_link = add_query_arg( '_wpnonce', wp_create_nonce( 'dollie_setup_upgrade_theme' ), $button_link );
				break;

			case 'recommended-plugins':
				$notice_header= __( '<span>Plugins</span> Recommended Plugins Setup', 'dollie-setup');
				$notice_text = __( 'You only have one last thing to do. We promise!', 'dollie-setup' );
				$button_link = dollie_setup_admin_prop( 'url', 'admin.php?page=dollie_setup' );
				$button_text = __( 'Click here to finish up!', 'dollie-setup' );
				break;

			case 'plugin-update':
			case 'upgrades-available':
				$notice_header= __( '<span>Plugins</span> Updates Available', 'dollie-setup');
				$notice_text = esc_html__( 'There are some upgrades available for the required Dollie Hub plugins', 'dollie-setup' );
				$button_text = esc_html__( 'Click here to update', 'dollie-setup' );

				$button_link = add_query_arg( '_wpnonce', wp_create_nonce( 'dollie_setup_upgrade' ), dollie_setup_admin_prop( 'url', 'admin.php?page=dollie_setup&dollie_setup-action=upgrade' ) );
				if ( 'upgrades-available' === dollie_setup_get_setup_step() ) {
					$button_link = dollie_setup_admin_prop( 'url', 'admin.php?page=dollie_setup-upgrades' );
				}
				break;

			default:
				return;
				break;
		}

		// change variables if we're still in setup phase
		$is_setup = isset( dollie_setup()->setup ) ? dollie_setup()->setup : false;
		if ( $is_setup ) {
			if ( 'upgrade-theme' == dollie_setup()->setup ) {
				$notice_text = __( 'Upgrading theme...', 'dollie-setup' );
			} else {
				$notice_text = __( 'Installing plugins...', 'dollie-setup' );
			}
		}
		?>


	<div id="dollie_setup-steps" class="notice dollie-notice dollie-setup">
			<svg xmlns="http://www.w3.org/2000/svg" fill="#33D399" viewBox="0 0 24 24" stroke="currentColor">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
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

	/**
	 * Add a special header before the admin plugins table is rendered
	 * to remind admins that Dollie plugins are on their own, special page.
	 *
	 * This only shows up when DOLLIE_SETUP is fully setup.
	 *
	 * @since 0.3
	 *
	 * @uses dollie_setup_is_setup() To tell if DOLLIE_SETUP is fully setup.
	 * @uses current_user_can() Check if the current user has the permission to do something.
	 * @uses is_network_admin() Check to see if we're in the network admin area.
	 * @uses is_multisite() Check to see if WP is in network mode.
	 */
	public function plugins_page_header() {
		 // Multisite: Don't show if user doesn't have network admin access.
		if ( is_multisite() && ! current_user_can( 'manage_network_plugins' ) ) {
			return;
		}

		if ( dollie_setup_is_setup() ) :
			$single_site = ( current_user_can( 'manage_network_plugins' ) && ! is_network_admin() ) || ( ! is_multisite() && current_user_can( 'install_plugins' ) );

			if ( $single_site ) {
				echo '<h3>' . __( 'Dollie Plugin Suite', 'dollie-setup' ) . '</h3>';
			} else {
				echo '<h3>' . __( 'DOLLIE_SETUP Network Plugins', 'dollie-setup' ) . '</h3>';
			}

			if ( $single_site ) {
				echo '<p>' . __( "Don't forget that Dollie plugins can be managed from the Dollie plugins page!", 'dollie-setup' ) . '</p>';
			}

			echo '<p style="margin-bottom:2.1em;">' . sprintf( __( 'You can <a href="%s">manage your Dollie plugins here</a>.', 'dollie-setup' ), dollie_setup_admin_prop( 'url', 'admin.php?page=dollie_setup-plugins' ) ) . '</p>';

			if ( $single_site ) {
				echo '<h3>' . sprintf( __( 'Plugins on %s', 'dollie-setup' ), get_bloginfo( 'name' ) ) . '</h3>';
			} else {
				echo '<h3>' . __( 'Other Network Plugins', 'dollie-setup' ) . '</h3>';
			}

		endif;
	}
}
