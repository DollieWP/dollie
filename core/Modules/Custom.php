<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Helpers;
use Dollie\Core\Log;

/**
 * Class Custom
 * @package Dollie\Core\Modules
 */
class Custom extends Singleton {

	/**
	 * @var mixed
	 */
	private $helpers;

	/**
	 * Custom constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->helpers = Helpers::instance();

		add_filter( 'wds_log_post_types', 'add_my_post_type_to_logs' );

		add_filter( 'wds_log_post_log_types', static function ( $terms ) {
			if ( ! isset( $terms['Cron'] ) ) {
				$terms['Cron'] = array(
					'slug'        => 'cron',
					'description' => 'background-color: #FDCB6E',
				);
			}
			if ( ! isset( $terms['Undeploy'] ) ) {
				$terms['Undeploy'] = array(
					'slug'        => 'undeploy',
					'description' => 'background-color: #D63031',
				);
			}
			if ( ! isset( $terms['Deploy'] ) ) {
				$terms['Deploy'] = array(
					'slug'        => 'deploy',
					'description' => 'background-color: #2ECD71',
				);
			}
			if ( ! isset( $terms['Setup'] ) ) {
				$terms['Setup'] = array(
					'slug'        => 'setup',
					'description' => 'background-color: #0984E3',
				);
			}
			if ( ! isset( $terms['Action'] ) ) {
				$terms['Action'] = array(
					'slug'        => 'action',
					'description' => 'background-color: #F39C12',
				);
			}

			return $terms;
		}
		);
	}

	public function add_my_post_type_to_logs( $posttypes ) {
		$posttypes['dollie-logs'] = 'Dollie';

		return $posttypes;
	}

}
