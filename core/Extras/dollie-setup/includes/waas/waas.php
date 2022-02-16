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
 * For plugin manifest, see {@link CBox_Plugins_Waas}.
 *
 * @todo Name subject to change.
 *
 * @since 1.1.0
 */
class CBox_Package_Waas extends CBox_Package {
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
			'network'           => true,
			'icon_url'          => dollie_setup()->plugin_url( 'admin/images/logo-dollie_setup-ol_vert.png' ),
			'badge_url'         => dollie_setup()->plugin_url( 'admin/images/logo-dollie_setup-ol_vert.png' ),
			'badge_url_2x'      => dollie_setup()->plugin_url( 'admin/images/logo-dollie_setup-ol_vert-2x.png' ),
			'documentation_url' => 'http://commonsinabox.org/dollie_setup-openlab-overview/?modal=1'
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
			'tab_plugin_optional' => __( 'Community Features', 'dollie-setup' )
		);
	}

	/**
	 * Register theme.
	 *
	 * @since 1.1.0
	 */
	protected static function theme() {
		return array(
			'name'           => 'DOLLIE_SETUP OpenLab',
			'version'        => '1.3.2',
			'directory_name' => 'openlab-theme',
			'download_url'   => DOLLIE_SETUP_PLUGIN_DIR . 'includes/zip/openlab-theme-1.3.2.zip',
			'screenshot_url' => dollie_setup()->plugin_url( 'admin/images/screenshot_openlab_theme.png' ),
			'force_install'  => true
		);
	}

	/**
	 * Custom hooks used during package initialization.
	 *
	 * @since 1.1.0
	 */
	protected function custom_init() {
		add_filter( 'site_option_menu_items', array( __CLASS__, 'menu_items_cb' ) );
		add_filter( 'default_site_option_menu_items', array( __CLASS__, 'menu_items_cb' ) );
	}

	/**
	 * Register upgrader.
	 *
	 * @since 1.2.0
	 */
	public static function upgrader() {
		do_action( 'dollie_setupol_register_upgrader' );
	}

	/**
	 * Callback for forcing the Plugins page to be available to non-super-admins.
	 *
	 * @since 1.1.1
	 *
	 * @param array
	 * @return array
	 */
	public static function menu_items_cb( $retval ) {
		if ( empty( $retval['plugins'] ) ) {
			$retval['plugins'] = 1;
		}
		return $retval;
	}

	/**
	 * Deactivation routine.
	 *
	 * @since 1.1.0
	 */
	public static function deactivate() {
		// Deactivate DOLLIE_SETUP-OpenLab-Core plugin, as it's OL-specific only.
		deactivate_plugins( 'dollie_setup-openlab-core/dollie_setup-openlab-core.php', true );
	}
}
