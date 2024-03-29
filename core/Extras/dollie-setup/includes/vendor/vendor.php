<?php
/**
 * Package: Vendor Core class
 *
 * @package    Dollie_Setup
 * @subpackage Package
 * @since      1.1.0
 */

/**
 * The "agency" DOLLIE_SETUP package.
 *
 * For plugin manifest, see {@link Dollie_Setup_Plugins_Agency}.
 * For admin settings page, see {@link Dollie_Setup_Settings_Agency}.
 *
 * @todo Name subject to change.
 *
 * @since 1.1.0
 */
class Dollie_Setup_Package_Vendor extends Dollie_Setup_Package {
	/**
	 * @var string Display name for our package.
	 */
	public static $name = 'Vendor';

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
			'icon_url'          => DOLLIE_ASSETS_URL . 'wizard/vendor.svg',
			'settings_key'      => '_dollie_setup_admin_settings',
			'documentation_url' => 'http://commonsinabox.org/dollie_setup-agency-overview/?modal=1',
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

	}
}
