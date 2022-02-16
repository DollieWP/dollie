<?php
/**
 * Package: Agency Core class
 *
 * @package    Dollie_Setup
 * @subpackage Package
 * @since      1.1.0
 */

/**
 * The "agency" DOLLIE_SETUP package.
 *
 * For plugin manifest, see {@link CBox_Plugins_Agency}.
 * For admin settings page, see {@link CBox_Settings_Agency}.
 *
 * @todo Name subject to change.
 *
 * @since 1.1.0
 */
class CBox_Package_Agency extends CBox_Package {
	/**
	 * @var string Display name for our package.
	 */
	public static $name = 'Agency';

	/**
	 * @var array Configuration holder.
	 */
	protected static $config = array();

	/**
	 * Package configuration.
	 *
	 * @since 1.1.0
	 */
	protected static function config() {
		return array(
			'icon_url'          => dollie_setup()->plugin_url( 'admin/images/logo-dollie_setup_icon-2x.png' ),
			'settings_key'      => '_dollie_setup_admin_settings',
			'documentation_url' => 'http://commonsinabox.org/dollie_setup-agency-overview/?modal=1'
		);
	}

	/**
	 * Register theme.
	 *
	 * @since 1.1.0
	 */
	protected static function theme() {
		return array(
			'name'           => 'Hello Dollie Theme',
			'version'        => '1.0.0',
			'directory_name' => 'hello-dollie',
			'download_url'   => DOLLIE_SETUP_PLUGIN_DIR . 'includes/zip/hello-dollie-1.0.0.zip',
			'admin_settings' => 'themes.php',
			'screenshot_url' => dollie_setup()->plugin_url( 'admin/images/screenshot_dollie_setup_theme.png' ),
		);
	}

	/**
	 * Custom hooks used during package initialization.
	 *
	 * @since 1.1.0
	 */
	protected function custom_init() {
		/**
	         * Trigger Infinity's activation hook
	         *
		 * Infinity, and therefore dollie_setup-theme, runs certain setup routines at
	         * 'infinity_dashboard_activated'. We need to run this hook just after DOLLIE_SETUP
	         * activates a theme, so we do that here.
	         */
		add_action( 'dollie_setup_agency_theme_activated', function() {
			if ( ! dollie_setup_get_installed_revision_date() ) {
				remove_action( 'infinity_dashboard_activated', 'infinity_dashboard_activated_redirect', 99 );
			}

			do_action( 'infinity_dashboard_activated' );
		} );
	}
}
