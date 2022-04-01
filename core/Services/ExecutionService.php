<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Utils\ConstInterface;

final class ExecutionService extends Singleton implements ConstInterface {
	/**
	 * Check execution status by AJAX
	 */
	public function ajax_check() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'dollie_check_execution' ) ) {
			wp_send_json_error();
		}

		$execution = $this->get( $_REQUEST['container'], $_REQUEST['type'] );

		if ( ! $execution ) {
			wp_send_json_success();
		}

		if ( 0 === $execution['status'] ) {
			wp_send_json_error();
		}

		$data = $this->get_status( $execution['id'], $_REQUEST['type'] );

		if ( is_wp_error( $data ) ) {
			$this->remove( get_the_ID(), $_REQUEST['type'] );

			wp_send_json_success();
		}

			$this->save( $_REQUEST['container'], $data );

		if ( 0 !== $data['status'] ) {
		}

		wp_send_json_success();
	}

	/**
	 * Get execution status
	 *
	 * @param string $execution_id
	 * @param string $execution_type
	 *
	 * @return int|\WP_Error
	 */
	public function get_status( $execution_id, $execution_type = '' ) {
		$data = [
			'execution_id' => $execution_id,
		];

		if ( $execution_type ) {
			$data['execution_type'] = $execution_type;
		}

		$response = self::post( self::ROUTE_GET_EXECUTION, $data );

		if ( $response ) {
			if ( ! $response['execution_id'] ) {
				return new \WP_Error( 'failed', __( 'Execution does not exist', 'dollie' ) );
			}

			return $response;
		}

		return new \WP_Error( 'failed', __( 'Failed to get execution status', 'dollie' ) );
	}

	/**
	 * Get execution
	 *
	 * @param int    $container_id
	 * @param string $execution_type
	 *
	 * @return null|string
	 */
	public function get( $container_id, $execution_type ) {
		return get_post_meta( $container_id, 'dollie.' . $execution_type, true );
	}

	/**
	 * Save execution
	 *
	 * @param int   $container_id
	 * @param array $execution
	 *
	 * @return void
	 */
	public static function save( $container_id, $execution ) {
		update_post_meta(
			$container_id,
			'dollie.' . $execution['execution_type'],
			[
				'id'     => $execution['execution_id'],
				'status' => $execution['status'],
			]
		);
	}

	/**
	 * Remove execution
	 *
	 * @param int    $container_id
	 * @param string $execution_type
	 *
	 * @return void
	 */
	public static function remove( $container_id, $execution_type ) {
		delete_post_meta( $container_id, 'dollie.' . $execution_type );
	}
}
