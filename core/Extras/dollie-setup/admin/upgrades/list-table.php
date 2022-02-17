<?php
namespace DOLLIE_SETUP\Admin\Upgrades;

use DOLLIE_SETUP\Upgrades\Upgrade_Registry;

if ( ! class_exists( '\WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class List_Table extends \WP_List_Table {

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct( [
			'plural'   => 'upgrades',
			'singular' => 'upgrade',
			'ajax'     => false
		] );
	}

	/**
	 * Message to be displayed when there are no items.
	 *
	 * @return void
	 */
	public function no_items() {
		esc_html_e( 'No upgrades found.', 'dollie-setup' );
	}

	/**
	 * Get the list of columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = [
			'name'            => esc_html__( 'Name', 'dollie-setup' ),
			'total_items'     => esc_html__( 'Total Items', 'dollie-setup' ),
			'total_processed' => esc_html__( 'Total Processed', 'dollie-setup' ),
		];

		return $columns;
	}

	/**
	 * Default column values if no callback found
	 *
	 * @param object $item
	 * @param string $column_name
	 *
	 * @return string
	 */
	protected function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'name':
				return $item->name;

			case 'total_processed':
				return $item->get_processed_count();

			case 'total_items':
				return $item->get_items_count();

			default:
				return isset( $item->$column_name ) ? $item->$column_name : '';
		}
	}

	/**
	 * Render the checkbox column
	 *
	 * @param object $item
	 * @return string
	 */
	protected function column_cb( $item ) {
		return '';
	}

	/**
	 * Render the upgrade name column.
	 *
	 * @param object $item
	 *
	 * @return string
	 */
	public function column_name( $item ) {
		$url = add_query_arg( [
			'page'   => 'dollie_setup-upgrades',
			'action' => 'view',
			'id'     => $item->id,
		], self_admin_url( 'admin.php' ) );

		$actions         = [];
		$actions['edit'] = sprintf(
			'<a href="%1$s" data-id="%2$d" title="%3$s">%4$s</a>',
			esc_url( $url ),
			esc_attr( $item->id ),
			esc_html__( 'View Upgrade', 'dollie-setup' ),
			esc_html__( 'View', 'dollie-setup' )
		);

		return sprintf(
			'<a href="%1$s"><strong>%2$s</strong></a> %3$s',
			esc_url( $url ),
			esc_html( $item->name ),
			$this->row_actions( $actions )
		);
	}

	/**
	 * Extra controls to be displayed between bulk actions and pagination.
	 *
	 * @param string $which
	 * @return void
	 */
	protected function extra_tablenav( $which ) {
		if ( 'bottom' !== $which ) {
			return;
		}

		if ( empty( $this->items ) ) {
			return;
		}

		$url = add_query_arg( [
			'page'   => 'dollie_setup-upgrades',
			'action' => 'view',
			'id'     => 'all',
		], self_admin_url( 'admin.php' ) );

		printf(
			'<a href="%1$s" class="button button-primary button-large">%2$s</a>',
			esc_url( $url ),
			__( 'Start upgrade process', 'dollie-setup' )
		);
	}

	/**
	 * Prepare the class items.
	 *
	 * @return void
	 */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$this->_column_headers = [ $columns ];

		$this->items = Upgrade_Registry::get_instance()->get_all_registered();
	}
}