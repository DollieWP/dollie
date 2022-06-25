<?php

namespace Dollie\Core\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;

final class DnsService extends Singleton {
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
	 * Remove zone
	 *
	 * @return void
	 */
	public function remove_zone() {
		if ( ! isset( $_GET['remove_zone'] ) ) {
			return;
		}

		$container = dollie()->get_container();

		if ( is_wp_error( $container ) ||
			! $container->is_owned_by_current_user() ||
			! $container->is_site() ) {
			return;
		}

		$container->delete_zone();

		wp_redirect( $container->get_permalink( 'domains' ) );
	}

	/**
	 * Remove route
	 *
	 * @return void
	 */
	public function remove_route() {
		if ( ! isset( $_REQUEST['remove_route'] ) ) {
			return;
		}

		$container = dollie()->get_container();

		// Prevent unauthorized access.
		if ( is_wp_error( $container ) ||
			 ! $container->is_owned_by_current_user() ||
			 ! $container->is_site() ) {
			return;
		}

		$container->delete_routes();

		wp_redirect( $container->get_permalink( 'domains' ) );
	}
}
