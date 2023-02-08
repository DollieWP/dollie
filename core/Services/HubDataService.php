<?php

namespace Dollie\Core\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Jobs\SyncContainersJob;
use Dollie\Core\Singleton;
use Dollie\Core\Utils\ConstInterface;

final class HubDataService extends Singleton implements ConstInterface {

	/**
	 * Load wizard route
	 *
	 * @return void
	 */
	public function load_route() {

		//check authorization
		$this->check_incoming_auth();

		if ( $_SERVER['REQUEST_METHOD'] === 'GET' ) {
			header( 'Content-Type: application/json; charset=utf-8' );
			echo json_encode( $this->get() );
		} elseif ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
			header( 'Content-Type: application/json; charset=utf-8' );
			echo json_encode( $this->set() );
		}
		exit;
	}

	private function get() {

		return [
			//'token'                => AuthService::instance()->get_token() ?: 'inactive',
			'hub_version'          => DOLLIE_VERSION,
			'total_products'       => count( dollie()->get_products() ),
			'total_sites'          => dollie()->count_total_sites(),
			'total_blueprints'     => dollie()->count_total_created_blueprints(),
			'sites'                => dollie()->get_sites(),
			'products'             => dollie()->get_products(),
			'blueprints'           => dollie()->get_blueprints(),
			'subscriptions_plugin' => get_option( 'options_wpd_subscription_plugin' ),
			'deployment_domain'    => get_option( 'options_wpd_api_domain' ),
			'preview_path'         => get_option( 'options_wpd_site_preview_path' ),
			'customers'            => count_users()['total_users'],
			'sales'                => $this->woo_sales(),
		];
	}

	private function set() {

		if ( ! isset( $_POST['type'], $_POST['operation'] ) ) {
			return [ 'success' => false, 'message' => 'No type or operation defined' ];
		}
		$type      = sanitize_text_field( $_POST['type'] );
		$operation = sanitize_text_field( $_POST['operation'] );

		if ( $type === 'site' ) {
			if ( $operation === 'add' && isset( $_POST['data'] ) ) {
				$data = sanitize_text_field( $_POST['data'] );
				SyncContainersJob::instance()->run_single_site( $data );
			}
		}
	}

	/**
	 * @return object|string
	 */
	private function woo_sales() {
		if ( class_exists( 'WooCommerce' ) ) {
			$report_data = $this->woo_get_sales_report_data();
			if ( $report_data ) {
				return $report_data;
			}
		}

		return 'WooCommerce not active';
	}

	private function woo_get_sales_report_data() {
		include_once( WC()->plugin_path() . '/includes/admin/reports/class-wc-admin-report.php' );
		include_once( WC()->plugin_path() . '/includes/admin/reports/class-wc-report-sales-by-date.php' );

		$sales_by_date                 = new \WC_Report_Sales_By_Date();
		$sales_by_date->start_date     = strtotime( date( 'Y-m-01', current_time( 'timestamp' ) ) );
		$sales_by_date->end_date       = strtotime( date( 'Y-m-d', current_time( 'timestamp' ) ) );
		$sales_by_date->chart_groupby  = 'day';
		$sales_by_date->group_by_query = 'YEAR(posts.post_date), MONTH(posts.post_date), DAY(posts.post_date)';

		return $sales_by_date->get_report_data();
	}

	public function check_incoming_auth() {

		// TODO Add Asymmetric encryption for the communication between hub and HQ
		$headers = getallheaders();
		if ( ! isset( $headers['Authorization'] ) || AuthService::instance()->get_token() !== $headers['Authorization'] ) {
			header( "HTTP/1.1 401 Unauthorized" );
			exit;
		}
	}
}
