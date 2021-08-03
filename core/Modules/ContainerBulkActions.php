<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Log;
use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;

/**
 * Class Container
 *
 * @package Dollie\Core\Modules
 */
class ContainerBulkActions extends Singleton {

	public const LOG_ACTION_STARTED = 'wp-bulk-action-start';

	/**
	 * Container constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_filter( 'dollie/log/actions', [ $this, 'log_action_filter' ], 10, 2 );

		add_action( 'wp_ajax_dollie_do_bulk_action', [ $this, 'do_bulk_action' ] );
		add_action( 'wp_ajax_dollie_check_bulk_action', [ $this, 'check_bulk_action' ] );
	}

	/**
	 * Log actions
	 *
	 * @param array $actions
	 * @param array $values
	 *
	 * @return array
	 */
	public function log_action_filter( $actions, $values ) {
		$actions[ self::LOG_ACTION_STARTED ] = [
			'title'   => __( 'Sites Bulk Action Started', 'dollie' ),
			'content' => __( sprintf( '%s action has been sent to the selected containers.', $values[0] ), 'dollie' ),
			'type'    => 'bulk',
			'link'    => false,
		];

		return $actions;
	}

	/**
	 * Get allowed bulk commands
	 *
	 * @return array
	 */
	public function get_allowed_commands() {
		return [
			'restart'               => __( 'Restart', 'dollie' ),
			'stop'                  => __( 'Stop', 'dollie' ),
			'update-plugins'        => __( 'Update Plugins', 'dollie' ),
			'update-themes'         => __( 'Update Themes', 'dollie' ),
			'create-backup'         => __( 'Create Backup', 'dollie' ),
			'regenerate-screenshot' => __( 'Regenerate Screenshot', 'dollie' ),
		];
	}

	/**
	 * Get allowed bulk commands in progress
	 *
	 * @return array
	 */
	public function get_allowed_commands_in_progress() {
		return [
			'restart'               => __( 'Restarting', 'dollie' ),
			'stop'                  => __( 'Stopping', 'dollie' ),
			'update-plugins'        => __( 'Updating Plugins', 'dollie' ),
			'update-themes'         => __( 'Updating Themes', 'dollie' ),
			'create-backup'         => __( 'Creating Backup', 'dollie' ),
			'regenerate-screenshot' => __( 'Regenerating Screenshot', 'dollie' ),
		];
	}

	/**
	 * Execute bulk command
	 *
	 * @return void
	 */
	public function do_bulk_action() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'dollie_do_bulk_action' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid request.', 'dollie' ) ] );
		}

		$command = sanitize_text_field( $_REQUEST['command'] );

		if ( ! array_key_exists( $command, $this->get_allowed_commands() ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid command.', 'dollie' ) ] );
		}

		if ( empty( $_REQUEST['containers'] ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'No sites selected.', 'dollie' ) ] );
		}

		$query_args = [
			'post_type'      => 'container',
			'posts_per_page' => - 1,
			'post_status'    => 'publish',
			'post__in'       => $_REQUEST['containers'],
		];

		if ( ! current_user_can( 'manage_options' ) ) {
			$query_args['author'] = get_current_user_id();
		}

		$posts = get_posts( $query_args );

		if ( empty( $posts ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'There has been something wrong with your request.', 'dollie' ) ] );
			exit;
		}

		$targets               = [];
		$existing_bulk_actions = $this->get_bulk_actions();

		foreach ( $posts as $post ) {
			$exists = false;
			foreach ( $existing_bulk_actions as $action ) {
				if ( dollie()->get_wp_site_data( 'uri', $post->ID ) === $action['container_uri'] ) {
					$exists = true;
				}
			}

			// Execute new action on container only if no other action is in progress.
			if ( ! $exists ) {
				$targets[] = [
					'id'           => get_post_meta( $post->ID, 'wpd_container_id', true ),
					'uri'          => dollie()->get_wp_site_data( 'uri', $post->ID ),
					'is_blueprint' => dollie()->is_blueprint( $post->ID ),
				];
			}
		}

		$response = Api::process_response(
			Api::post(
				Api::ROUTE_CONTAINER_BULK_ACTION,
				[
					'targets' => $targets,
					'command' => $command,
				]
			)
		);

		if ( is_array( $response ) ) {
			foreach ( $posts as $post ) {
				foreach ( $response as &$item ) {
					if ( dollie()->get_wp_site_data( 'uri', $post->ID ) === $item['container_uri'] ) {
						$item['post_id'] = $post->ID;
					}
				}
			}

			$this->set_bulk_actions( $response );

			foreach ( $response as &$item ) {
				$item['text'] = $this->get_allowed_commands_in_progress()[ $item['action'] ];
			}
		}

		Log::add_front(
			self::LOG_ACTION_STARTED,
			dollie()->get_current_object( $posts[0]->ID ),
			[
				$this->get_allowed_commands()[ $command ],
			]
		);

		wp_send_json_success( $response );
	}

	/**
	 * Check bulk action
	 *
	 * @return void
	 */
	public function check_bulk_action() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'dollie_check_bulk_action' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid request.', 'dollie' ) ] );
		}

		wp_send_json_success( $this->check_bulk_actions() );
	}

	/**
	 * Set bulk actions
	 *
	 * @param array $data
	 * @return void
	 */
	public function set_bulk_actions( $data, $force = false ) {
		if ( ! $force ) {
			$data = array_merge( $this->get_bulk_actions(), $data );
		}

		update_option( 'wpd_container_bulk_actions_' . get_current_user_id(), $data );
	}

	/**
	 * Get bulk actions
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		return get_option( 'wpd_container_bulk_actions_' . get_current_user_id(), [] );
	}

	/**
	 * Remove action
	 *
	 * @param string $container_uri
	 * @return void
	 */
	public function remove_bulk_action( $container_uri ) {
		$actions = $this->get_bulk_actions();

		foreach ( $actions as $key => $action ) {
			if ( $action['container_uri'] === $container_uri ) {
				unset( $actions[ $key ] );
			}
		}

		$this->set_bulk_actions( $actions );
	}

	/**
	 * Check bulk actions
	 *
	 * @return array
	 */
	public function check_bulk_actions() {
		$actions = $this->get_bulk_actions();

		$response = [];
		$targets  = [];

		foreach ( $actions as $action ) {
			$targets[] = $action['execution_id'];
		}

		if ( ! empty( $targets ) ) {
			$response = Api::process_response(
				Api::post(
					Api::ROUTE_CONTAINER_BULK_ACTION_STATUS,
					[
						'targets' => $targets,
					]
				)
			);
		}

		if ( ! empty( $response ) ) {
			foreach ( $actions as $key => $action ) {
				foreach ( $response as &$item ) {
					if ( $item['execution_id'] === $action['execution_id'] && $item['status'] ) {
						$item['container_uri'] = $action['container_uri'];
						unset( $actions[ $key ] );

						if ( in_array( $item['action'], [ 'stop', 'restart' ] ) ) {
							Container::instance()->get_container_details( $action['post_id'], true );
						}
					}
				}
			}

			$this->set_bulk_actions( $actions, true );
		}

		return $response;
	}

}
