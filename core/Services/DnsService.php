<?php

namespace Dollie\Core\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Log;
use Dollie\Core\Factories\Site;

final class DnsService extends Singleton {
	/**
	 * Validate pending zone
	 *
	 * @return void
	 */
	public function validate_domain() {
		// if ( 'pending' === $dns_manager_status && $domain_zone ) {
		// $container = dollie()->get_container();

		// $zone_response = Api::post(
		// Api::ROUTE_DOMAIN_CHECK_ZONE,
		// [
		// 'container_uri' => $container->get_original_url(),
		// ]
		// );

		// if ( is_array( $zone_response ) ) {
		// if ( isset( $zone_response['container_uri'] ) && ! $zone_response['container_uri'] ) {
		// } elseif ( isset( $zone_response['status'] ) && $zone_response['status'] ) {
		// $domain_pending = get_post_meta( get_the_ID(), 'wpd_domain_pending', true );

		// $this->add_container_routes( $container, $domain_pending );
		// }
		// }
		// }
	}

	public function remove_domain() {
		if ( isset( $_GET['remove-domain'] ) ) {
			$container = dollie()->get_container();

			if ( is_wp_error( $container ) || ! $container->is_site() ) {
				return;
			}

			$container->delete_zone();

			wp_redirect( $container->get_permalink( 'domains' ) );
		}
	}

	/**
	 * Create record
	 *
	 * @return void
	 */
	public function create_record() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'dollie_create_record' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid request.', 'dollie' ) ] );
		}

		if ( ! isset( $_POST['data'] ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid request.', 'dollie' ) ] );
		}

		$params = [];
		parse_str( $_REQUEST['data'], $params );

		$container = dollie()->get_container( $params['container_id'] );

		if ( is_wp_error( $container ) ) {
			wp_send_json_error();
			exit;
		}

		$response = $container->create_record(
			[
				'type'     => $params['type'],
				'hostname' => $params['hostname'],
				'content'  => $params['content'],
				'priority' => isset( $params['priority'] ) ? $params['priority'] : '',
				'ttl'      => $params['ttl'],
			]
		);

		if ( is_wp_error( $response ) ) {
			wp_send_json_error();
			exit;
		}

		wp_send_json_success(
			dollie()->load_template(
				'widgets/site/pages/domain/records',
				[
					'records'   => $response,
					'container' => $container,
				],
			)
		);
		exit;
	}

	/**
	 * Remove record
	 *
	 * @return void
	 */
	public function remove_record() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'dollie_remove_record' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid request.', 'dollie' ) ] );
		}

		if ( ! isset( $_POST['record_id'] ) || ! isset( $_POST['container_id'] ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid request.', 'dollie' ) ] );
		}

		$params = $_POST;

		$container = dollie()->get_container( $params['container_id'] );

		if ( is_wp_error( $container ) ) {
			wp_send_json_error();
			exit;
		}

		$response = $container->delete_record( $params['record_id'] );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error();
			exit;
		}

		if ( ! $response['deleted'] ) {
			wp_send_json_error();
			exit;
		}

		wp_send_json_success();
	}

	/**
	 * Allowed caa tsgs
	 *
	 * @return array
	 */
	public function allowed_caa_tags() {
		return [
			'issue',
			'issuewild',
			'iodef',
		];
	}

	/**
	 * Remove route
	 */
	public function remove_route() {
		if ( ! is_user_logged_in() || ! isset( $_REQUEST['remove_route'] ) ) {
			return;
		}

		$container = dollie()->get_container();

		// Prevent unauthorized access.
		if ( is_wp_error( $container ) ||
			 ! current_user_can( 'manage_options' ) ||
			 ! $container->is_owned_by_current_user() ||
			 ! $container->is_site() ) {
			return;
		}

		$container->delete_routes();

		wp_redirect( $container->get_permalink( 'domains' ) );
	}
}
