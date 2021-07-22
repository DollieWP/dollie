<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;

/**
 * Class Logging
 *
 * @package Dollie\Core\Modules
 */
class Logging extends Singleton {

	/**
	 * Logging constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_filter( 'wds_log_post_types', [ $this, 'register_post_type' ] );
		add_filter( 'wds_log_post_log_types', [ $this, 'register_log_types' ] );
	}

	/**
	 * Register log post type
	 *
	 * @param array $post_types
	 *
	 * @return array
	 */
	public function register_post_type( $post_types = [] ) {
		$post_types['dollie-logs'] = 'Dollie';

		return $post_types;
	}

	/**
	 * Register log types
	 *
	 * @param $terms
	 *
	 * @return mixed
	 */
	public function register_log_types( $terms ) {
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
		if ( ! isset( $terms['Bulk'] ) ) {
			$terms['Bulk'] = [
				'slug'        => 'bulk',
				'description' => 'background-color: #F39C12',
			];
		}

		return $terms;
	}

}
