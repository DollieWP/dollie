<?php

namespace Dollie\Core\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;

final class RecurringActionService extends Singleton {
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
			'regenerate-screenshot' => __( 'Regenerate Screenshot', 'dollie' ),
			'restart'               => __( 'Restart', 'dollie' ),
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

		$containers_ids = [];
		$params         = [];
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

		foreach ( $params['containers'] as $container_id ) {
			$containers_ids[]['id'] = $container_id;
		}

		$posts = dollie()->containers_query( $containers_ids );

		if ( empty( $posts ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'There has been something wrong with your request.', 'dollie' ) ] );
			exit;
		}

		$targets = [];

		foreach ( $posts as $post ) {
			$container_id = get_post_meta( $post->ID, 'wpd_container_id', true );

			if ( $container_id ) {
				$targets[] = [
					'container_id'  => get_post_meta( $post->ID, 'wpd_container_id', true ),
					'container_uri' => dollie()->get_wp_site_data( 'uri', $post->ID ),
				];
			}
		}

		$response = Api::post(
			Api::ROUTE_CONTAINER_RECURRING_ACTION_CREATE,
			[
				'name'     => $params['schedule-name'],
				'targets'  => $targets,
				'action'   => $params['action'],
				'interval' => $params['interval'],
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

		$response = Api::post(
			Api::ROUTE_CONTAINER_RECURRING_CONTAINER_DELETE,
			[
				'uuid'         => sanitize_text_field( $_REQUEST['uuid'] ),
				'container_id' => sanitize_text_field( $_REQUEST['container_id'] ),
			]
		);

		wp_send_json_success( $response );
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

		$response = Api::post(
			Api::ROUTE_CONTAINER_RECURRING_ACTION_DELETE,
			[
				'uuid' => sanitize_text_field( $_REQUEST['uuid'] ),
			]
		);

		wp_send_json_success( $response );
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

		$posts = dollie()->containers_query( $_REQUEST['containers'] );

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

		$response = Api::post(
			Api::ROUTE_CONTAINER_RECURRING_ACTION_GET,
		);

		$data = [];

		if ( is_array( $response ) && ! empty( $response ) ) {
			foreach ( $response as $schedule ) {
				$containers_ids = [];

				foreach ( $schedule['containers'] as $container ) {
					$containers_ids[]['container_id'] = $container['id'];
				}

				if ( ! empty( $containers_ids ) ) {
					$posts = dollie()->containers_query( $containers_ids, 'container_id' );
				} else {
					$posts = [];
				}

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
					$container    = dollie()->get_container( $post );
					$container_id = get_post_meta( $post->ID, 'wpd_container_id', true );

					if ( $container_id ) {
						$containers[] = [
							'id'           => $container->get_id(),
							'name'         => $container->get_title(),
							'url'          => $container->get_url(),
							'container_id' => $container_id,
						];
					}
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
				'parts/recurring-action-selected-sites',
				[
					'data' => $data,
				]
			)
		);
	}
}
