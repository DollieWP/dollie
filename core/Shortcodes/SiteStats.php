<?php

namespace Dollie\Core\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use WP_Query;

/**
 * Class Sites
 *
 * @package Dollie\Core\Shortcodes
 */
final class SiteStats extends Singleton implements Base {

	private string $name = 'dollie-site-stats';

	private $labels;
	private $containers = [
		'app',
		'db'
	];
	private $keys = [
		'block'      => [ 'read', 'write' ],
		'cpu'        => [ 'usage' ],
		'disk_usage' => [ 'usage' ],
		'memory'     => [ 'usage', /*'mem_assigned', 'mem_hardlimit'*/ ],
		'network'    => [ 'in', 'out' ],
	];

	/**
	 * Sites constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->labels = [
			'block'      => __( 'Disk I/O', 'dollie' ),
			'cpu'        => __( 'CPU %', 'dollie' ),
			'disk_usage' => __( 'Disk', 'dollie' ),
			'memory'     => __( 'Memory', 'dollie' ),
			'network'    => __( 'Network', 'dollie' ),
		];

		add_action( 'init', [ $this, 'register' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue' ]
		);
	}

	public function enqueue() {
		wp_register_script( 'chartjs', DOLLIE_ASSETS_URL . 'js/chart.min.js', [], '4.1.1', true );
	}

	/**
	 * Add shortcode
	 *
	 * @return void
	 */
	public function register(): void {
		add_shortcode( $this->name, [ $this, 'shortcode' ] );
	}

	/**
	 * Shortcode logic
	 *
	 * @param array $atts
	 *
	 * @return string|null
	 */
	public function shortcode( $atts = [] ): ?string {

		$settings = shortcode_atts(
			[
				'id'        => dollie()->get_current_post_id(),
				'container' => 'app', //app|db
				'type'      => 'block'

			],
			$atts,
			$this->name
		);

		$stats = $this->get_data( $settings['id'] );
		if ( empty( $stats ) ) {
			return '';
		}

		[ $labels, $datasets ] = $this->prepare_stats( $stats, $settings['type'] );

		return dollie()->load_template(
			'widgets/site/site-stats',
			[
				'labels'   => json_encode( $labels ),
				'datasets' => json_encode( $datasets ),
				'chart_id' => 'stats-chart-' . random_int(100, 9999)
			]
		);

	}

	/**
	 * Prepare the data format for chartJS.
	 *
	 * @param $stats
	 * @param $type
	 *
	 * @return array
	 */
	private function prepare_stats( $stats, $type ) {
		$labels   = [];
		$datasets = [];

		foreach ( $this->containers as $container ) {
			if ( ! isset( $stats[ $container ] ) ) {
				continue;
			}
			foreach ( $this->keys[ $type ] as $key ) {

				$set = [];

				if ( ! isset( $stats[ $container ]['stats'][ $type ] ) ) {
					continue;
				}

				if ( empty( $stats[ $container ]['stats'][ $type ][ $key ])) {
					continue;
				}

				foreach ( $stats[ $container ]['stats'][ $type ][ $key ] as $time => $val ) {
					$labels[ md5(date( 'Y-m-d H:i', $time )) ] = date( 'j M H:i', $time );
					if ( $stats[ $container ]['stats'][ $type ]['units'] === 'bytes' ) {
						$val = round( $val / 1024 / 1024 );
					}
					$set[] = $val;
				}

				if ( ! empty( $labels ) ) {
					$datasets[] = [
						'label' => strtoupper( $container ) . " " . $this->labels[ $type ] . ' ' . ucfirst( $key ),
						'data'  => $set
					];
				}

			}

		}

		return [ array_values( $labels ), $datasets ];

	}

	/**
	 * Get stats data from API
	 *
	 * @param int|false $site_id
	 *
	 * @return array
	 */
	private function get_data( $site_id = null ): array {

		if ( empty( $site_id ) ) {
			$site_id = get_the_ID();
		}

		$container = dollie()->get_container( $site_id );
		if ( is_wp_error( $container ) ) {
			return [];
		}

		return $container->get_resource_usage();
	}

}
