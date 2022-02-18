<?php
/**
 * Package: Waas Core class
 *
 * @package    Dollie_Setup
 * @subpackage Package
 * @since      1.1.0
 */

/**
 * The "Waas" DOLLIE_SETUP package.
 *
 * For plugin manifest, see {@link Dollie_Setup_Plugins_Waas}.
 *
 * @todo Name subject to change.
 *
 * @since 1.1.0
 */
class Dollie_Setup_Package_Waas extends Dollie_Setup_Package {
	/**
	 * @var string Display name for our package.
	 */
	public static $name = 'Waas';

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
			'network'           => false,
			'icon_url'          => dollie_setup()->plugin_url( 'admin/images/logo-dollie_setup-ol_vert.png' ),
			'badge_url'         => dollie_setup()->plugin_url( 'admin/images/logo-dollie_setup-ol_vert.png' ),
			'badge_url_2x'      => dollie_setup()->plugin_url( 'admin/images/logo-dollie_setup-ol_vert-2x.png' ),
			'documentation_url' => 'http://commonsinabox.org/dollie_setup-openlab-overview/?modal=1',
		);
	}

	/**
	 * String setter method.
	 *
	 * @since 1.1.0
	 *
	 * @return array
	 */
	protected static function strings() {
		return array(
			'tab_plugin_optional' => __( 'Community Features', 'dollie-setup' ),
		);
	}

	/**
	 * Register theme.
	 *
	 * @since 1.1.0
	 */
	protected static function theme()
	{
		return array(
			'name'           => 'Hello Dollie Theme',
			'version'        => '1.0.0',
			'directory_name' => 'hello-dollie',
			'download_url'   => DOLLIE_SETUP_PLUGIN_DIR . 'includes/zip/hello-dollie-1.0.0.zip',
			'admin_settings' => 'themes.php',
			'screenshot_url' => dollie_setup()->plugin_url('admin/images/screenshot_dollie_setup_theme.png'),
		);
	}

}
