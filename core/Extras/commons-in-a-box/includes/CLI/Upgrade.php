<?php
namespace DOLLIE_SETUP\CLI;

use WP_CLI;
use DOLLIE_SETUP\Upgrades\Upgrade_Registry;

/**
 * Commands applicable to a DOLLIE_SETUP Upgrades API.
 *
 * ## EXAMPLES
 *
 *     # List the available upgrades.
 *     $ wp dollie_setup upgrade list
 *
 * @package dollie_setup
 */
class Upgrade extends \WP_CLI_Command {
	/**
	 * Lists all available upgrades.
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
	 * These fields will be displayed by default for each upgrade:
	 *
	 * * ID
	 * * Name
	 * * Total
	 * * Processed
	 *
	 * ## EXAMPLES
	 *
	 *     # List the available upgrades.
	 *     $ wp dollie_setup upgrade list
	 *
	 * @subcommand list
	 */
	public function list_( $args, $assoc_args ) {
		$upgrades = Upgrade_Registry::get_instance()->get_all_registered();

		$r = array_merge( [
			'format' => 'table',
			'fields' => [ 'ID', 'Name', 'Total', 'Processed' ]
		], $assoc_args );

		if ( ! is_array( $r['fields'] ) ) {
			$r['fields'] = explode( ',', $r['fields'] );
		}

		// Sanity check!
		if ( empty( $upgrades ) ) {
			WP_CLI::error( 'No upgrades are available.' );
		}

		$items = [];
		foreach ( $upgrades as $upgrade ) {
			$items[] = [
				'ID'        => $upgrade->id,
				'Name'      => $upgrade->name,
				'Total'     => $upgrade->get_items_count(),
				'Processed' => $upgrade->get_processed_count(),
			];
		}

		WP_CLI\Utils\format_items( $r['format'], $items, $r['fields'] );
	}

	/**
	 * Run the upgrade.
	 *
	 * ## OPTIONS
	 *
	 * <upgrade-id>
	 * : The upgrade ID.
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp dollie_setup upgrade run upgrade_nav_menus
	 */
	public function run( $args, $assoc_args ) {
		list( $id ) = $args;

		/** @var \DOLLIE_SETUP\Upgrades\Upgrade */
		$upgrade = Upgrade_Registry::get_instance()->get_registered( $id );

		if ( ! $upgrade ) {
			WP_CLI::error( sprintf( 'Upgrade "%s" does not exist.', $id ) );
		}

		$progress = \WP_CLI\Utils\make_progress_bar( 'Running the upgrade', $upgrade->get_items_count() );

		while ( $item = $upgrade->get_next_item() ) {
			$upgrade->process( $item );
			$upgrade->mark_as_processed( $item->id );

			$progress->tick();
		}

		// Mark process as finished.
		$upgrade->finish();
		$progress->finish();

		WP_CLI::success( sprintf(
			'Processed %1$d items from total %2$d.',
			$upgrade->get_processed_count(),
			$upgrade->get_items_count()
		) );
	}
}
