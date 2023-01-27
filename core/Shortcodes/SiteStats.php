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

	private $name = 'dollie-site-stats';

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
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue' ] );
		add_action( 'wp_ajax_wpd_site_stats', [ $this, 'ajax_get_stats_data' ] );
	}

	public function enqueue() {
		wp_register_script( 'chartjs', DOLLIE_ASSETS_URL . 'js/chart.min.js', [], '4.1.1', true );
		wp_register_script( 'dollie-chartjs', DOLLIE_ASSETS_URL . 'js/dollie-chart.js', [], DOLLIE_VERSION, true );
		wp_localize_script( 'dollie-chartjs', 'wpdChart', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' )
		] );
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
	 * @return void
	 */
	public function ajax_get_stats_data(): void {

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( [ 'message' => 'Unauthorized' ] );
			exit;
		}


		if ( ! isset( $_GET['id'] ) || (int) $_GET['id'] === 0 ) {
			wp_send_json_error( [ 'message' => 'Missing data' ] );
			exit;
		}

		$id = (int) $_GET['id'];

		if ( ! dollie()->get_user()->can_view_site( $id ) ) {
			wp_send_json_error( [ 'message' => 'Unauthorized' ] );
			exit;
		}

		$type  = sanitize_text_field( $_GET['type'] );
		$stats = $this->get_data( $id );

		if ( empty( $stats ) ) {
			wp_send_json_error( [ 'message' => 'Something went wrong' ] );
			exit;
		}

		wp_send_json_success( $this->prepare_stats( $stats, $type ) );
		exit;
	}

	/**
	 * Shortcode logic
	 *
	 * @param array $atts
	 *
	 * @return string|null
	 * @throws \Exception
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

		return dollie()->load_template(
			'widgets/site/site-stats',
			[
				'type'     => $settings['type'],
				'site_id'  => $settings['id'],
				'chart_id' => 'stats-chart-' . random_int( 100, 9999 )
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
		if ( empty( $type ) ) {
			$type = 'cpu';
		}

		foreach ( $this->containers as $container ) {
			if ( ! isset( $stats[ $container ] ) ) {
				continue;
			}
			foreach ( $this->keys[ $type ] as $key ) {

				$set = [];

				if ( ! isset( $stats[ $container ]['stats'][ $type ] ) ) {
					continue;
				}

				if ( empty( $stats[ $container ]['stats'][ $type ][ $key ] ) ) {
					continue;
				}

				foreach ( $stats[ $container ]['stats'][ $type ][ $key ] as $time => $val ) {
					$labels[ md5( date( 'Y-m-d H:i', $time ) ) ] = date( 'j M H:i', $time );
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

		return [ 'labels' => array_values( $labels ), 'datasets' => $datasets ];

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
