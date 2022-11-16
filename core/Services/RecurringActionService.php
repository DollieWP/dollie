<?php

namespace Dollie\Core\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Api\ActionApi;

final class RecurringActionService extends Singleton {
	use ActionApi;

	/**
	 * Get allowed commands
	 *
	 * @return array
	 */
	public function get_allowed_commands() {
		return [
			'update-wp-core'        => __( 'Update WP Core', 'dollie' ),
			'update-plugins'        => __( 'Update Plugins', 'dollie' ),
			'update-themes'         => __( 'Update Themes', 'dollie' ),
			'create-backup'         => __( 'Create Backup', 'dollie' ),
			'regenerate-screenshot' => __( 'Regenerate Screenshot', 'dollie' ),
			'restart'               => __( 'Restart', 'dollie' ),
			'stop'                  => __( 'Stop', 'dollie' ),
		];
	}

	/**
	 * Get allowed intervals
	 *
	 * @return array
	 */
	public function get_allowed_intervals() {
		return [
			'daily'        => __( 'Daily', 'dollie' ),
			'twiceDaily'   => __( 'Twice a day', 'dollie' ),
			'weekly'       => __( 'Weekly', 'dollie' ),
			'monthly'      => __( 'Montly', 'dollie' ),
			'twiceMonthly' => __( 'Twice a month', 'dollie' ),
		];
	}

	/**
	 * Create recurring action
	 *
	 * @return void
	 */
	public function create_recurring_action() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'dollie_create_recurring_action' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid request.', 'dollie' ) ] );
		}

		if ( ! isset( $_POST['data'] ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid request.', 'dollie' ) ] );
		}

		$params = [];
		parse_str( $_REQUEST['data'], $params );

		if ( ! $params['schedule-name'] ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid schedule name.', 'dollie' ) ] );
		}

		if ( ! array_key_exists( $params['action'], $this->get_allowed_commands() ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Action not allowed.', 'dollie' ) ] );
		}

		if ( ! array_key_exists( $params['interval'], $this->get_allowed_intervals() ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Interval not allowed.', 'dollie' ) ] );
		}

		$ids = [];
		foreach ( $params['containers'] as $container_id ) {
			$ids[] = $container_id;
		}

		$posts = dollie()->get_containers_by_ids( $ids );

		if ( empty( $posts ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'There has been something wrong with your request.', 'dollie' ) ] );
			exit;
		}

		$targets = [];

		foreach ( $posts as $post ) {
			$container = dollie()->get_container( $post );

			if ( is_wp_error( $container ) ) {
				continue;
			}

			$targets[] = $container->get_hash();
		}

		$response = $this->create_recurring_actions(
			[
				'name'             => $params['schedule-name'],
				'container_hashes' => $targets,
				'action'           => $params['action'],
				'interval'         => $params['interval'],
			]
		);

		wp_send_json_success( $response );
	}

	public function remove_recurring_container() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'dollie_delete_recurring_container' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid request.', 'dollie' ) ] );
		}

		if ( ! isset( $_REQUEST['uuid'] ) || ! $_REQUEST['uuid'] ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid request.', 'dollie' ) ] );
		}

		$uuid           = sanitize_text_field( $_REQUEST['uuid'] );
		$container_hash = sanitize_text_field( $_REQUEST['container_hash'] );

		wp_send_json_success( $this->delete_recurring_action( $uuid, $container_hash ) );
	}

	/**
	 * Remove recurring action
	 *
	 * @return void
	 */
	public function remove_recurring_action() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'dollie_delete_recurring_action' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid request.', 'dollie' ) ] );
		}

		if ( ! isset( $_REQUEST['uuid'] ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid request.', 'dollie' ) ] );
		}

		$uuid = sanitize_text_field( $_REQUEST['uuid'] );

		wp_send_json_success( $this->delete_recurring_action( $uuid ) );
	}

	/**
	 * Get selected sites template
	 *
	 * @return void
	 */
	public function get_selected_sites() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'dollie_get_selected_sites' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid request.', 'dollie' ) ] );
		}

		$ids = [];
		foreach ( $_REQUEST['containers'] as $container ) {
			$ids[] = $container['id'];
		}

		$posts = dollie()->get_containers_by_ids( $ids );

		if ( empty( $posts ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Something went wrong with your request.', 'dollie' ) ] );
			exit;
		}

		$targets = [];

		foreach ( $posts as $post ) {
			$container = dollie()->get_container( $post );

			if ( is_wp_error( $container ) ) {
				continue;
			}

			$targets[] = [
				'id'           => $container->get_id(),
				'name'         => $container->get_title(),
				'url'          => $container->get_url(),
				'container_id' => $container->get_hash(),
				'commands'     => [],
			];
		}

		wp_send_json_success(
			dollie()->load_template(
				'parts/recurring-action-selected-sites',
				[
					'targets' => $targets,
				]
			)
		);
	}

	/**
	 * Get schedule history
	 *
	 * @return void
	 */
	public function get_schedule_history() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'dollie_get_schedule_history' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid request.', 'dollie' ) ] );
		}

		$response = $this->get_recurring_actions();
		$data     = [];

		if ( is_array( $response ) && ! empty( $response ) ) {
			foreach ( $response as $schedule ) {
				$hashes = [];
				foreach ( $schedule['containers'] as $container ) {
					$hashes[] = $container['hash'];
				}

				$posts      = dollie()->get_containers_by_hashes( $hashes );
				$containers = [];
				$logs       = [];

				foreach ( $schedule['containers'] as $container ) {
					if ( ! empty( $container['logs'] ) ) {
						foreach ( $container['logs'] as $log ) {
							$logs[] = $log;
						}
					}
				}

				foreach ( $posts as $post ) {
					$container = dollie()->get_container( $post );

					if ( is_wp_error( $container ) ) {
						continue;
					}

					$containers[] = [
						'id'   => $container->get_id(),
						'name' => $container->get_title(),
						'url'  => $container->get_url(),
						'hash' => $container->get_hash(),
					];
				}

				$data[] = [
					'uuid'       => $schedule['uuid'],
					'name'       => $schedule['name'],
					'action'     => $schedule['action'],
					'interval'   => $schedule['interval'],
					'next_run'   => $schedule['next_run'],
					'containers' => $containers,
					'logs'       => $logs,
				];
			}
		}

		wp_send_json_success(
			dollie()->load_template(
				'parts/recurring-action-schedule-history',
				[
					'data' => $data,
				]
			)
		);
	}
}
