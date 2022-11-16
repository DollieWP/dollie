<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;

/**
 * Class WooCommerce
 *
 * @package Dollie\Core\Modules
 */
class WooCommerce extends Singleton {

	/**
	 * WooCommerce constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'after_setup_theme', [ $this, 'add_theme_support' ] );

		add_filter( 'acf/fields/relationship/query/key=field_5e2c1adcc1543', [ $this, 'modify_query' ], 10, 3 );
		add_filter( 'acf/fields/relationship/query/key=field_5e2c1b94c1544', [ $this, 'modify_query' ], 10, 3 );
	}

	/**
	 * Add theme support
	 */
	public function add_theme_support() {
		add_theme_support( 'woocommerce' );
	}

	/**
	 * Modify query to include/exclude blueprints
	 *
	 * @param $args
	 * @param $field
	 * @param $post_id
	 *
	 * @return mixed
	 */
	public function modify_query( $args, $field, $post_id ) {
		$args['meta_query'][] = [
			'relation' => 'AND',
			[
				'key'   => 'dollie_container_type',
				'value' => '1',
			],
			[
				'key'   => 'wpd_blueprint_created',
				'value' => 'yes',
			],
			[
				'key'     => 'wpd_installation_blueprint_title',
				'compare' => 'EXISTS',
			],
		];

		return $args;
	}

}
