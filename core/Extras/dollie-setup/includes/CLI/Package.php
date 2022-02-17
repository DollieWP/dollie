<?php
namespace Dollie_Setup\CLI;

use WP_CLI;

/**
 * Commands applicable to a DOLLIE_SETUP package.
 *
 * ## EXAMPLES
 *
 *     # List the available DOLLIE_SETUP packages.
 *     $ wp dollie_setup package list
 *
 * @package dollie_setup
 */
class Package extends \WP_CLI_Command {
	/**
	 * Lists all available DOLLIE_SETUP packages.
	 *
	 * ## OPTIONS
	 *
	 * [--fields=<fields>]
	 * : Limit the output to specific fields.
	 *
	 * [--format=<format>]
	 * : Render output in a particular format.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - csv
	 *   - json
	 *   - yaml
	 * ---
	 *
	 * ## AVAILABLE FIELDS
	 *
	 * These fields will be displayed by default for each DOLLIE_SETUP package:
	 *
	 * * Package
	 * * Name
	 * * Theme
	 * * Network
	 * * Active
	 *
	 * These fields are optionally available:
	 *
	 * * Description
	 *
	 * ## EXAMPLES
	 *
	 *     # Lists all available DOLLIE_SETUP packages.
	 *     $ wp dollie_setup package list
	 *     +---------+---------+---------------+--------------+--------+
	 *     | Package | Name    | Theme         | Network      | Active |
	 *     +---------+---------+---------------+--------------+--------+
	 *     | agency | Agency | dollie_setup-theme    | Not required | Yes    |
	 *     | openlab | OpenLab | openlab-theme | Required     | No     |
	 *     +---------+---------+---------------+--------------+--------+
	 *
	 * @subcommand list
	 */
	public function list_( $args, $assoc_args ) {
		$packages = dollie_setup_get_packages();

		$r = array_merge( array(
			'format' => 'table',
			'fields' => array( 'Package', 'Name', 'Theme', 'Network', 'Active' )
		), $assoc_args );

		if ( ! is_array( $r['fields'] ) ) {
			$r['fields'] = explode( ',', $r['fields'] );
		}

		// Rare that this will happen, but sanity check!
		if ( empty( $packages ) ) {
			WP_CLI::error( 'No DOLLIE_SETUP packages are available.' );
		}

		$items = array();
		$i = 0;
		$description_enabled = array_search( 'Description', $r['fields'] );
		foreach ( $packages as $package => $class ) {
			$theme = dollie_setup_get_theme_prop( 'directory_name', $package );

			$items[$i] = array(
				'Package' => $package,
				'Name'    => dollie_setup_get_package_prop( 'name', $package ),
				'Theme'   => $theme ? $theme : 'No theme available',
				'Network' => dollie_setup_get_package_prop( 'network', $package ) ? 'Required' : 'Not required',
				'Active'  => dollie_setup_get_current_package_id() === $package ? 'Yes' : 'No',
			);

			if ( $description_enabled ) {
				// Description is stored in template part.
				ob_start();
				dollie_setup_get_template_part( 'description', $package );
				$description = ob_get_clean();
				$description = strip_tags( $description );
				$description = str_replace( array( "\t", "\n" ), ' ', $description );
				$description = trim( $description );

				$items[$i]['Description'] = $description;
			}

			++$i;
		}

		WP_CLI\Utils\format_items( $r['format'], $items, $r['fields'] );
	}

	/**
	 * Lists the plugins for a package.
	 *
	 * ## OPTIONS
	 *
	 * <package-id>
	 * : The package ID to list the plugins for.
	 *
	 * ## EXAMPLES
	 *
	 *     # Lists all registered plugins for the 'agency' package
	 *     $ wp dollie_setup package list-plugins agency
	 *
	 * @subcommand list-plugins
	 */
	public function list_plugins( $args, $assoc_args ) {
		$packages = dollie_setup_get_packages();

		// Error messaging.
		if ( empty( $packages ) ) {
			WP_CLI::error( 'No DOLLIE_SETUP packages are available.' );
		}
		if ( empty( $packages[ $args[0] ] ) ) {
			WP_CLI::error( "Package '{$args[0]}' does not exist." );
		}

		$class   = $packages[ $args[0] ];
		$plugins = $class::get_plugins( '' );

		// Don't show dependency tier.
		unset( $plugins['dependency'] );

		$header = "Plugins for {$args[0]}";
		WP_CLI::line( $header );
		WP_CLI::line( str_repeat( '=', strlen( $header ) ) . "\n" );

		foreach ( $plugins as $tier => $tier_plugins ) {
			WP_CLI::line( ucfirst( $tier ) . ":" );

			$t_plugins = array();
			foreach ( $tier_plugins as $plugin_name => $data ) {
				$t_plugins[] = "{$plugin_name} {$data['version']}";
			}

			WP_CLI::line( wp_sprintf_l( '%l', $t_plugins ) . "\n" );
		}
	}
}