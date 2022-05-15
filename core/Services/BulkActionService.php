<?php

namespace Dollie\Core\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Log;
use Dollie\Core\Singleton;
use Dollie\Core\Api\ActionApi;

final class BulkActionService extends Singleton {
	use ActionApi;

	public const LOG_ACTION_STARTED = 'wp-bulk-action-start';
	public const LOG_UPDATE_WP_CORE = 'wp-bulk-update-wp-core';
	public const LOG_UPDATE_PLUGINS = 'wp-bulk-update-plugins';
	public const LOG_UPDATE_THEMES  = 'wp-bulk-update-themes';

	/**
	 * Log actions
	 *
	 * @param array $actions
	 * @param array $values
	 *
	 * @return array
	 */
	public function log_action_filter( $actions, $values, $log_id ) {
		$actions[ self::LOG_ACTION_STARTED ] = [
			'title'   => __( 'Sites Bulk Action', 'dollie' ),
			'content' => __( sprintf( '%s action has started on the selected sites.', $values[0] ), 'dollie' ),
			'type'    => 'bulk',
			'link'    => false,
		];

		$actions[ self::LOG_UPDATE_WP_CORE ] = [
			'title'   => __( 'WP Core updated', 'dollie' ),
			'content' => __( sprintf( 'Site %s has completed updating the Wordpress Core.', $values[0] ), 'dollie' ),
			'type'    => 'bulk',
			'link'    => false,
		];

		$actions[ self::LOG_UPDATE_PLUGINS ] = [
			'title'   => __( 'Plugins updated', 'dollie' ),
			'content' => __( sprintf( 'Site %s has completed updating plugins.', $values[0] ), 'dollie' ),
			'type'    => 'bulk',
			'link'    => false,
		];

		$actions[ self::LOG_UPDATE_THEMES ] = [
			'title'   => __( 'Themes updated', 'dollie' ),
			'content' => __( sprintf( 'Site %s has completed updating themes.', $values[0] ), 'dollie' ),
			'type'    => 'bulk',
			'link'    => false,
		];

		return $actions;
	}

	/**
	 * Log actions
	 *
	 * @param string $content
	 * @param array  $values
	 *
	 * @return string
	 */
	public function log_action_content_filter( $content, $values, $log_id ) {
		$bulk_actions = get_post_meta( $log_id, '_wpd_sub_logs', true );

		if ( ! empty( $bulk_actions ) ) {

			foreach ( $bulk_actions as $bulk_log_id ) {
				$log      = get_post( $bulk_log_id );
				$content .= '<br> ' . $log->post_content;
			}
		}

		return $content;
	}

	/**
	 * Get allowed bulk commands
	 *
	 * @return array
	 */
	public function get_allowed_commands() {
		return [
			'update-wp-core' => __( 'Update WP Core', 'dollie' ),
			'update-plugins' => __( 'Update Plugins', 'dollie' ),
			'update-themes'  => __( 'Update Themes', 'dollie' ),
			'create-backup'  => __( 'Create Backup', 'dollie' ),
			'restart'        => __( 'Restart', 'dollie' ),
			'stop'           => __( 'Stop', 'dollie' ),
		];
	}

	/**
	 * Get allowed bulk commands in progress
	 *
	 * @return array
	 */
	public function get_allowed_commands_in_progress() {
		return [
			'update-wp-core' => __( 'Updating WP Core', 'dollie' ),
			'update-plugins' => __( 'Updating Plugins', 'dollie' ),
			'update-themes'  => __( 'Updating Themes', 'dollie' ),
			'create-backup'  => __( 'Creating Backup', 'dollie' ),
			'restart'        => __( 'Restarting', 'dollie' ),
			'stop'           => __( 'Stopping', 'dollie' ),
		];
	}

	/**
	 * Get allowed bulk commands in progress
	 *
	 * @return string
	 */
	public function get_log_action( $action ) {
		$actions = [
			'update-wp-core' => self::LOG_UPDATE_WP_CORE,
			'update-plugins' => self::LOG_UPDATE_PLUGINS,
			'update-themes'  => self::LOG_UPDATE_THEMES,
			'create-backup'  => Log::WP_SITE_BACKUP_STARTED,
			'restart'        => Log::WP_SITE_RESTARTED,
			'stop'           => Log::WP_SITE_STOPPED,
		];

		return $actions[ $action ] ?? '';
	}

	/**
	 * Get allowed bulk commands in progress
	 *
	 * @return string
	 */
	public function get_log_failed_action( $action ) {
		$actions = [
			'update-wp-core' => self::LOG_UPDATE_WP_CORE,
			'update-plugins' => self::LOG_UPDATE_PLUGINS,
			'update-themes'  => self::LOG_UPDATE_THEMES,
			'create-backup'  => Log::WP_SITE_BACKUP_STARTED,
			'restart'        => Log::WP_SITE_RESTARTED,
			'stop'           => Log::WP_SITE_STOPPED,
		];

		return $actions[ $action ] ?? '';
	}

	/**
	 * Execute bulk command
	 *
	 * @return void
	 */
	public function execute_bulk_action() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'dollie_do_bulk_action' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid request.', 'dollie' ) ] );
			exit;
		}

		$command = sanitize_text_field( $_REQUEST['command'] );

		if ( ! array_key_exists( $command, $this->get_allowed_commands() ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid command.', 'dollie' ) ] );
			exit;
		}

		if ( empty( $_REQUEST['containers'] ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'No sites selected.', 'dollie' ) ] );
			exit;
		}

		switch ( $command ) {
			case 'update-plugins':
				if ( ! isset( $_REQUEST['command_data'] ) || ! is_array( $_REQUEST['command_data'] ) || empty( $_REQUEST['command_data'] ) ) {
					wp_send_json_error( [ 'message' => esc_html__( 'No sites have been selected.', 'dollie' ) ] );
					exit;
				}

				$posts = $this->get_containers( $_REQUEST['command_data'] );
				break;
			case 'update-themes':
				if ( ! isset( $_REQUEST['command_data'] ) || ! is_array( $_REQUEST['command_data'] ) || empty( $_REQUEST['command_data'] ) ) {
					wp_send_json_error( [ 'message' => esc_html__( 'No sites have been selected.', 'dollie' ) ] );
					exit;
				}

				$posts = $this->get_containers( $_REQUEST['command_data'] );
				break;
			default:
				$posts = $this->get_containers( $_REQUEST['containers'] );
		}

		if ( empty( $posts ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'No matching sites were found for your selection.', 'dollie' ) ] );
			exit;
		}

		$targets               = [];
		$existing_bulk_actions = $this->get_saved_bulk_actions();

		foreach ( $posts as $post ) {
			$container = dollie()->get_container( $post );

			if ( is_wp_error( $container ) ) {
				continue;
			}

			$exists = false;
			foreach ( $existing_bulk_actions as $action ) {
				if ( $container->get_original_url() === $action['container_hash'] ) {
					$exists = true;
				}
			}

			// Execute new action on container only if no other action is in progress.
			if ( ! $exists ) {
				$data = [];
				foreach ( $_REQUEST['command_data'] as $item ) {
					if ( (int) $item['id'] === $container->get_id() ) {
						$data[] = $item['value'];
					}
				}

				$targets[] = [
					'container_hash' => $container->get_hash(),
					'action'         => $command,
					'data'           => $data,
				];
			}
		}

		$response = $this->create_bulk_action( [ 'targets' => $targets ] );

		// $log_id = Log::add_front(
		// self::LOG_ACTION_STARTED,
		// dollie()->get_current_object( $posts[0]->ID ),
		// [
		// $this->get_allowed_commands()[ $command ],
		// ]
		// );

		if ( is_array( $response ) ) {
			$this->set_bulk_actions( $response );

			foreach ( $response as &$item ) {
				$item['text'] = $this->get_allowed_commands_in_progress()[ $item['action'] ];
			}

			unset( $item );
		}

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
			exit;
		}

		wp_send_json_success( $this->check_bulk_actions() );
	}

	/**
	 * Check bulk actions
	 *
	 * @return array
	 */
	public function check_bulk_actions() {
		$actions = $this->get_saved_bulk_actions();

		if ( empty( $actions ) ) {
			return [];
		}

		$action_hashes = [];
		foreach ( $actions as $action ) {
			if ( ! in_array( $action['container_hash'], $action_hashes ) ) {
				$action_hashes[] = $action['container_hash'];
			}
		}

		$response = $this->get_bulk_actions( $action_hashes );

		if ( is_wp_error( $response ) || empty( $response ) ) {
			$this->set_bulk_actions( [] );

			return [];
		}

		foreach ( $actions as $key => $action ) {
			$filtered_action = array_filter(
				$response,
				function( $v ) use ( $action ) {
					return $v['container_hash'] === $action['container_hash'];
				}
			);

			if ( empty( $filtered_action ) ) {
				unset( $actions[ $key ] );
			}

			// if we have a parent log id saved.
			// if ( isset( $action['log_id'] ) ) {
			// $container = dollie()->get_container( $action['post_id'] );

			// $log_action = $this->get_log_action( $action['action'] );

			// Add individual log
			// $sub_log_id = Log::add_front(
			// $log_action,
			// $container,
			// [
			// $container->get_slug(),
			// ]
			// );

			// update_post_meta( $sub_log_id, '_wpd_bulk_log', 1 );

			// update parent log with sub log ids.
			// $parent_logs = get_post_meta( $action['log_id'], '_wpd_sub_logs', true );

			// if ( ! $parent_logs ) {
			// $parent_logs = [];
			// }

			// $parent_logs[ $sub_log_id ] = $sub_log_id;
			// update_post_meta( $action['log_id'], '_wpd_sub_logs', $parent_logs );
			// }

		}

		$this->set_bulk_actions( $actions );

		return $actions;
	}

	/**
	 * Get action data
	 *
	 * @return void
	 */
	public function get_action_data() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'dollie_get_bulk_action_data' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid request.', 'dollie' ) ] );
			exit;
		}

		$command = sanitize_text_field( $_REQUEST['command'] );
		$posts   = $this->get_containers( $_REQUEST['containers'] );

		if ( empty( $posts ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'No matching sites were found for your selection.', 'dollie' ) ] );
			exit;
		}

		$content = '';

		if ( 'update-plugins' === $command ) {
			$plugins_data = [];

			foreach ( $posts as $post ) {
				$container = dollie()->get_container( $post );

				if ( is_wp_error( $container ) ) {
					continue;
				}

				foreach ( $container->get_plugins( true ) as $plugin ) {
					if ( isset( $plugins_data[ $plugin['slug'] ] ) ) {
						$plugins_data[ $plugin['slug'] ]['sites'][] = [
							'id'     => $container->get_id(),
							'title'  => $container->get_title(),
							'url'    => $container->get_url(),
							'update' => $plugin['update'],
						];
					} else {
						$plugins_data[ $plugin['slug'] ] = [
							'title'  => $plugin['name'],
							'update' => false,
							'sites'  => [
								[
									'id'     => $container->get_id(),
									'title'  => $container->get_title(),
									'url'    => $container->get_url(),
									'update' => $plugin['update'],
								],
							],
						];
					}
				}
			}

			foreach ( $plugins_data as &$plugin ) {
				$plugin['update'] = (bool) count(
					array_filter(
						$plugin['sites'],
						function( $v ) {
							return true === $v['update'];
						}
					)
				);
			}

			unset( $plugin );

			$content = dollie()->load_template( 'loop/parts/modal-actions/parts/plugins', [ 'plugins_data' => $plugins_data ] );
		}

		if ( 'update-themes' === $command ) {
			$themes_data = [];

			foreach ( $posts as $post ) {
				$container = dollie()->get_container( $post );

				if ( is_wp_error( $container ) ) {
					continue;
				}

				foreach ( $container->get_themes( true ) as $theme ) {
					if ( isset( $themes_data[ $theme['slug'] ] ) ) {
						$themes_data[ $theme['slug'] ]['sites'][] = [
							'id'     => $container->get_id(),
							'title'  => $container->get_title(),
							'url'    => $container->get_url(),
							'update' => $theme['update'],
						];
					} else {
						$themes_data[ $theme['slug'] ] = [
							'title'  => $theme['name'],
							'update' => false,
							'sites'  => [
								[
									'id'     => $container->get_id(),
									'title'  => $container->get_title(),
									'url'    => $container->get_url(),
									'update' => $theme['update'],
								],
							],
						];
					}
				}
			}

			foreach ( $themes_data as &$theme ) {
				$theme['update'] = (bool) count(
					array_filter(
						$theme['sites'],
						function( $v ) {
							return true === $v['update'];
						}
					)
				);
			}

			unset( $theme );

			$content = dollie()->load_template( 'loop/parts/modal-actions/parts/themes', [ 'themes_data' => $themes_data ] );
		}

		wp_send_json_success( $content );
	}

	/**
	 * Set bulk actions
	 *
	 * @return void
	 */
	public function set_bulk_actions( $data ) {
		update_option( 'wpd_container_bulk_actions_' . get_current_user_id(), $data );
	}

	/**
	 * Get bulk actions
	 *
	 * @return array
	 */
	public function get_saved_bulk_actions() {
		return get_option( 'wpd_container_bulk_actions_' . get_current_user_id(), [] );
	}

	/**
	 * Get containers data
	 *
	 * @param array  $data
	 * @param string $with
	 *
	 * @return array
	 */
	public function get_containers( $data ) {
		$ids = [];

		foreach ( $data as $container ) {
			$ids[] = (int) $container['id'];
		}

		$args = [
			'post_type'      => 'container',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'post__in'       => $ids,
		];

		if ( ! current_user_can( 'manage_options' ) ) {
			$args['author'] = get_current_user_id();
		}

		$posts = new \WP_Query( $args );
		$posts = $posts->get_posts();

		wp_reset_postdata();

		return $posts;
	}
}
