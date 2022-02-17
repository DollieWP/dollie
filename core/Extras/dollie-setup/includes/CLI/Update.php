<?php
namespace Dollie_Setup\CLI;

use WP_CLI;

/**
 * Commands applicable to updating DOLLIE_SETUP.
 *
 * ## EXAMPLES
 *
 *     # Updates the DOLLIE_SETUP theme.
 *     $ wp dollie_setup update theme
 *
 * @package dollie_setup
 */
class Update extends \WP_CLI_Command {
	/**
	 * Updates the DOLLIE_SETUP plugins and theme, if applicable.
	 *
	 * ## EXAMPLES
	 *
	 *     # Updates the DOLLIE_SETUP plugins and theme.
	 *     $ wp dollie_setup update all
	 */
	public function all( $args, $assoc_args ) {
		WP_CLI::runcommand( 'dollie_setup update plugins --yes' );

		WP_CLI::line( 'Updating theme...' );
		WP_CLI::runcommand( 'dollie_setup update theme' );
	}

	/**
	 * Updates the DOLLIE_SETUP theme.
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp dollie_setup update theme
	 *     Downloading install package from http://github.com/cuny-academic-commons/dollie_setup-theme/archive/1.0.15.zip...
	 *     Unpacking the package...
	 *     Installing the theme...
	 *     Renamed Github-based project from 'dollie_setup-theme-1.0.15' to 'dollie_setup-theme'.
	 *     Removing the old version of the theme...
	 *     Theme updated successfully.
	 *     Success: Installed 1 of 1 themes.
	 */
	public function theme( $args, $assoc_args ) {
		// check for theme upgrades
		$theme = dollie_setup_get_theme_to_update();
		if ( empty( $theme ) ) {
			$dollie_setup_theme_name = dollie_setup_get_theme_prop( 'name' );
			$current_theme_name      = dollie_setup_get_theme()->get( 'Name' );
			if ( $dollie_setup_theme_name && $dollie_setup_theme_name === $current_theme_name ) {
				WP_CLI::success( 'You are already running the latest version of the theme, ' . dollie_setup_get_theme_prop( 'directory_name' ) );
			} else {
			}

			return;
		}

		// Sanity check.
		if ( $theme !== dollie_setup_get_theme_prop( 'directory_name' ) ) {
			WP_CLI::error( 'Package theme does not match' );
		}

		// Run the update, using WP-CLI's native 'theme' command.
		WP_CLI::runcommand( 'theme install ' . dollie_setup_get_theme_prop( 'download_url' ) . ' --force' );
	}

	/**
	 * Updates plugins.
	 *
	 * Will install or activate missing required plugins and will offer to
	 * update remaining active plugins.
	 *
	 * [--yes]
	 * : Answer yes to the confirmation message. Only applicable for plugins requiring upgrades.
	 *
	 * ## EXAMPLES
	 *
	 *     # Updates the DOLLIE_SETUP plugins, but will ask for confirmation before doing so.
	 *     $ wp dollie_setup update plugins
	 *     Attempting to update the following plugins:
	 *     +----------------------+-------------+-------------+
	 *     | Plugin               | Old Version | New Version |
	 *     +----------------------+-------------+-------------+
	 *     | BP Group Documents   | 1.12.0      | 1.12.1      |
	 *     | CAC Featured Content | 1.0.8       | 1.0.9       |
	 *     +----------------------+-------------+-------------+
	 *     Do you want to continue? [y/n] y
	 *     Downloading installation package from http://downloads.wordpress.org/plugin/bp-group-documents.1.12.1.zip...
	 *     Unpacking the package...
	 *     Installing the plugin...
	 *     Removing the old version of the plugin...
	 *     Plugin updated successfully.
	 *     Downloading installation package from http://downloads.wordpress.org/plugin/cac-featured-content.1.0.9.zip...
	 *     Unpacking the package...
	 *     Installing the plugin...
	 *     Removing the old version of the plugin...
	 *     Plugin updated successfully.
	 *     Success: Installed 2 of 2 plugins.
	 *
	 *     # Updates the DOLLIE_SETUP plugins, without confirmation.
	 *     $ wp dollie_setup update plugins --yes
	 */
	public function plugins( $args, $assoc_args ) {
		if ( ! class_exists( '\Dollie_Setup_Plugin_Upgrader' ) ) {
			require_once DOLLIE_SETUP_PLUGIN_DIR . 'admin/plugin-install.php';
		}

		$dollie_setup_plugins = \Dollie_Setup_Plugins::get_plugins();
		$dependencies         = \Dollie_Setup_Plugins::get_plugins( 'dependency' );

		// (1) Do required plugins first.
		$required = \Dollie_Setup_Plugins::get_plugins( 'required' );
		$required = \Dollie_Setup_Admin_Plugins::organize_plugins_by_state( $required );
		unset( $required['deactivate'] );

		$required = \Dollie_Setup_Updater::parse_plugins( $required );

		$activate = $urls = [];

		if ( ! empty( $required['install'] ) ) {
			$activate = $required['install'];

			WP_CLI::line( 'Installing missing required plugins:' );

			foreach ( $required['install'] as $plugin ) {
				if ( ! empty( $dependencies[ $plugin ]['download_url'] ) ) {
					$urls[] = $dependencies[ $plugin ]['download_url'];
				} else {
					$urls[] = $dollie_setup_plugins[ $plugin ]['download_url'];
				}
			}

			$urls = array_unique( $urls );

			// Install missing plugins.
			WP_CLI::runcommand( 'plugin install ' . implode( ' ', $urls ) . ' --force' );
			WP_CLI::line( '' );

			// Due to CLI, clear plugin dir cache after installation and re-init PD.
			wp_cache_delete( 'plugins', 'plugins' );
			\Plugin_Dependencies::init();
		}

		// If other plugins need to be activated, add it to our list.
		if ( ! empty( $required['activate'] ) ) {
			$activate = array_merge( $required['activate'], $activate );
			$activate = array_unique( $activate );
		}

		// (2) Activate missing plugins.
		if ( ! empty( $activate ) ) {
			\Dollie_Setup_Plugin_Upgrader::bulk_activate( $activate );

			WP_CLI::line( 'Activated missing required plugins: ' . wp_sprintf_l( '%l', $activate ) . '.' );
			WP_CLI::line( '' );
		}

		// (3) Do upgrades here.
		$plugins = \Dollie_Setup_Admin_Plugins::get_upgrades( 'active' );

		if ( empty( $plugins ) ) {
			if ( ! empty( $required ) ) {
				WP_CLI::line( 'All other active plugins are already up-to-date.' );
			} else {
				WP_CLI::line( 'All active plugins are already up-to-date.' );
			}
			return;
		}

		WP_CLI::line( 'Attempting to update the following plugins:' );

		$urls = [];

		foreach ( $plugins as $plugin ) {
			$loader           = \Plugin_Dependencies::get_pluginloader_by_name( $plugin );
			$items[ $plugin ] = array(
				'Plugin'      => $plugin,
				'Old Version' => \Plugin_Dependencies::$all_plugins[ $loader ]['Version'],
				'New Version' => isset( $dollie_setup_plugins[ $plugin ]['version'] ) ? $dollie_setup_plugins[ $plugin ]['version'] : $dependencies[ $plugin ]['version'],
			);

			if ( ! empty( $dependencies[ $plugin ]['download_url'] ) ) {
				$urls[] = $dependencies[ $plugin ]['download_url'];
			} else {
				$urls[] = $dollie_setup_plugins[ $plugin ]['download_url'];
			}
		}

		$urls = array_unique( $urls );

		// Output plugin table.
		WP_CLI\Utils\format_items( 'table', $items, array( 'Plugin', 'Old Version', 'New Version' ) );

		// Confirmation prompt, if necessary.
		WP_CLI::confirm( 'Do you want to continue?', $assoc_args );

		// Run the updater.
		WP_CLI::runcommand( 'plugin install ' . implode( ' ', $urls ) . ' --force' );
	}
}
