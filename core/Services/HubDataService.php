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

		// check authorization
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

		/* Filters */
		$users_filters = array(
			'orderby  '       => 'display_name',
			'exclude_admin  ' => false,
			// 'search  ' => '',
			// 'role__in' => array( 'author', 'subscriber' )

		);

		if ( isset( $_GET['filters'] ) ) {
			if ( isset( $_GET['filters']['users'] ) ) {
				$users_filters = $_GET['filters']['users']; // https://developer.wordpress.org/reference/classes/wp_user_query/
			}
		}

		/* Return single type data */
		if ( isset( $_GET['type'] ) ) {
			$type = sanitize_text_field( $_GET['type'] );

			if ( $type === 'sites' ) {
				return dollie()->get_sites();
			}

			if ( $type === 'access_groups' ) {
				return dollie()->access()->get_access_groups();
			}

			if ( $type === 'users' ) {
				return get_users( $users_filters );
			}

			if ( $type === 'sales' ) {
				return $this->woo_sales();
			}
		}

		return array(
			// 'token'                => AuthService::instance()->get_token() ?: 'inactive',
			'hub_version'          => DOLLIE_VERSION,
			'total_products'       => count( dollie()->get_products() ),
			'total_sites'          => dollie()->count_total_sites(),
			'total_blueprints'     => dollie()->count_total_created_blueprints(),
			'sites'                => dollie()->get_sites(),
			'products'             => dollie()->get_products(),
			'access_groups'        => dollie()->access()->get_access_groups(),
			'blueprints'           => dollie()->get_blueprints(),
			'subscriptions_plugin' => get_option( 'options_wpd_subscription_plugin' ),
			'deployment_domain'    => WorkspaceService::instance()->get_deployment_domain(),
			'preview_path'         => get_option( 'options_wpd_site_preview_path' ),
			'customers_total'      => count_users()['total_users'],
			'sales'                => $this->woo_sales(),
			'users'                => get_users( $users_filters ),
		);
	}

	private function set() {

		$response = '';

		$request = $_POST;
		if (empty($request)) {
			$request = file_get_contents("php://input");
			//convert the string of data to an array
			$request = json_decode($request, true);
		}


		if ( ! isset( $request['type'] ) ) {
			return array(
				'success' => false,
				'message' => 'No type or data defined',
			);
		}
		
		$type = sanitize_text_field( $request['type'] );

		if ( $type === 'sync_sites' ) {
			if ( ! empty( $request['site_data'] ) && is_array( $request['site_data'] ) ) {
				SyncContainersJob::instance()->run_single_with_data( $request['site_data'] );
				$response = 'Container sync complete';
			} else {
				$response = SyncContainersJob::instance()->run();
			}
		} elseif ( $type === 'client' ) {
			$data     = $request['data'];
			$response = wp_create_user( $data['username'], $data['password'], $data['email'] );

		} elseif ( $type === 'domain' ) {
			$domain = sanitize_text_field( $request['data'] );

			// set custom domain option.
			update_option( 'wpd_deployment_domain', $domain );

			$response = 'Domain has ben synced to hub!';
		}

		// TODO get the status too
		return array(
			'success' => true,
			'message' => $response,
		);
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
		include_once WC()->plugin_path() . '/includes/admin/reports/class-wc-admin-report.php';
		include_once WC()->plugin_path() . '/includes/admin/reports/class-wc-report-sales-by-date.php';

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
			header( 'HTTP/1.1 401 Unauthorized' );
			exit;
		}
	}
}
