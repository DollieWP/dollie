<?php
namespace Dollie_Setup\CLI;

use WP_CLI;

/**
 * Updates and manages a DOLLIE_SETUP installation.
 *
 * ## EXAMPLES
 *
 *     # Display the DOLLIE_SETUP version
 *     $ wp dollie_setup version
 *     1.0.15
 *
 * @package dollie_setup
 */
class Core extends \WP_CLI_Command {
	/**
	 * Displays current DOLLIE_SETUP status.
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp dollie_setup status
	 *     Current DOLLIE_SETUP package: Agency
	 *
	 *     Current theme: Dollie Setup. No update available.
	 *
	 *     Active DOLLIE_SETUP plugins are all up-to-date.
	 */
	public function status( $args, $assoc_args ) {
		if ( ! dollie_setup_get_current_package_id() ) {
			// @todo Add 'wp dollie_setup install' command of some sort.
			WP_CLI::error( 'A DOLLIE_SETUP package is not active on the site.  Please install DOLLIE_SETUP before running this command.' );
		}

		WP_CLI::line( 'Current DOLLIE_SETUP package: ' . dollie_setup_get_package_prop( 'name' ) );
		WP_CLI::line( '' );

		// Theme status.
		$theme = dollie_setup_get_theme_to_update();
		if ( ! empty( $theme ) ) {
			WP_CLI::line( 'The theme has an update. Run "wp dollie_setup update theme" to update the theme.' );
		} else {
			$dollie_setup_theme = dollie_setup_get_package_prop( 'theme' );
			$current_theme      = dollie_setup_get_theme();

			if ( $dollie_setup_theme['name'] && $dollie_setup_theme['directory_name'] === $current_theme->get_template() ) {
				WP_CLI::line( 'Current theme: ' . $dollie_setup_theme['name'] . '. No update available.' );
			} elseif ( $dollie_setup_theme['name'] ) {
				WP_CLI::line( 'Current theme: ' . $current_theme->get( 'Name' ) . '. The DOLLIE_SETUP bundled theme, ' . $dollie_setup_theme['name'] . ', is available, but not activated.' );
				WP_CLI::line( 'You can activate the theme by running "wp theme activate ' . $dollie_setup_theme['directory_name'] . '"' );
			}
		}

		// Active plugin status.
		$plugins            = \Dollie_Setup_Admin_Plugins::get_upgrades( 'active' );
		$show_plugin_notice = $show_active_notice = false;
		if ( ! empty( $plugins ) ) {
			$show_plugin_notice = true;

			$items = array();

			WP_CLI::line( '' );
			WP_CLI::line( 'The following active plugins have an update available:' );

			$dollie_setup_plugins = \Dollie_Setup_Plugins::get_plugins();
			$dependencies         = \Dollie_Setup_Plugins::get_plugins( 'dependency' );

			foreach ( $plugins as $plugin ) {
				$loader  = \Plugin_Dependencies::get_pluginloader_by_name( $plugin );
				$items[] = array(
					'Plugin'          => $plugin,
					'Current Version' => \Plugin_Dependencies::$all_plugins[ $loader ]['Version'],
					'New Version'     => isset( $dollie_setup_plugins[ $plugin ]['version'] ) ? $dollie_setup_plugins[ $plugin ]['version'] : $dependencies[ $plugin ]['version'],
				);
			}

			WP_CLI\Utils\format_items( 'table', $items, array( 'Plugin', 'Current Version', 'New Version' ) );
		} else {
			$show_active_notice = true;
		}

		// Required plugins check.
		if ( ! isset( $dollie_setup_plugins ) ) {
			$dollie_setup_plugins = \Dollie_Setup_Plugins::get_plugins( 'required' );
		} else {
			$dollie_setup_plugins = $dollie_setup_plugins['required'];
		}

		$required = \Dollie_Setup_Admin_Plugins::organize_plugins_by_state( $dollie_setup_plugins );
		unset( $required['deactivate'] );

		if ( ! empty( $required ) ) {
			$show_plugin_notice = true;

			$items = array();

			WP_CLI::line( '' );
			WP_CLI::line( 'The following plugins are required and need to be either activated or installed:' );

			if ( ! isset( $dependencies ) ) {
				$dependencies = \Dollie_Setup_Plugins::get_plugins( 'dependency' );
			}

			foreach ( $required as $state => $plugins ) {
				switch ( $state ) {
					case 'activate':
						$action = 'Requires activation';
						break;

					case 'install':
						$action = 'Requires installation';
						break;
				}
				foreach ( $plugins as $plugin ) {
					$loader  = \Plugin_Dependencies::get_pluginloader_by_name( $plugin );
					$items[] = array(
						'Plugin'  => $plugin,
						'Version' => isset( $dollie_setup_plugins[ $plugin ]['version'] ) ? $dollie_setup_plugins[ $plugin ]['version'] : $dependencies[ $plugin ]['version'],
						'Action'  => $action,
					);
				}
			}

			WP_CLI\Utils\format_items( 'table', $items, array( 'Plugin', 'Version', 'Action' ) );
		}

		if ( $show_plugin_notice ) {
			WP_CLI::line( '' );
			WP_CLI::line( 'Run "wp dollie_setup update plugins" to update the plugins.' );
		} elseif ( $show_active_notice ) {
			WP_CLI::line( '' );
			WP_CLI::line( 'Active DOLLIE_SETUP plugins are all up-to-date.' );
		}
	}

	/**
	 * Displays the DOLLIE_SETUP version.
	 *
	 * ## EXAMPLES
	 *
	 *     # Display the WordPress version
	 *     $ wp dollie_setup version
	 *     1.0.15
	 */
	public function version( $args, $assoc_args ) {
		WP_CLI::line( dollie_setup()->version );
	}
}
