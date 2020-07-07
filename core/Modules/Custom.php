<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;

/**
 * Class Custom
 * @package Dollie\Core\Modules
 */
class Custom extends Singleton {

	/**
	 * Custom constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_filter( 'wds_log_post_types', [ $this, 'add_my_post_type_to_logs' ] );

		add_filter( 'wds_log_post_log_types', static function ( $terms ) {
			if ( ! isset( $terms['Cron'] ) ) {
				$terms['Cron'] = [
					'slug'        => 'cron',
					'description' => 'background-color: #FDCB6E',
				];
			}
			if ( ! isset( $terms['Undeploy'] ) ) {
				$terms['Undeploy'] = [
					'slug'        => 'undeploy',
					'description' => 'background-color: #D63031',
				];
			}
			if ( ! isset( $terms['Deploy'] ) ) {
				$terms['Deploy'] = [
					'slug'        => 'deploy',
					'description' => 'background-color: #2ECD71',
				];
			}
			if ( ! isset( $terms['Setup'] ) ) {
				$terms['Setup'] = [
					'slug'        => 'setup',
					'description' => 'background-color: #0984E3',
				];
			}
			if ( ! isset( $terms['Action'] ) ) {
				$terms['Action'] = [
					'slug'        => 'action',
					'description' => 'background-color: #F39C12',
				];
			}

			return $terms;
		}
		);
	}

	/**
	 * Add post type to logs
	 *
	 * @param array $post_types
	 *
	 * @return array
	 */
	public function add_my_post_type_to_logs( $post_types = [] ) {
		$post_types['dollie-logs'] = 'Dollie';

		return $post_types;
	}

}
