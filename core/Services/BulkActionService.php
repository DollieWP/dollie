<?php

namespace Dollie\Core\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Log;
use Dollie\Core\Singleton;

final class BulkActionService extends Singleton {
	public const LOG_ACTION_STARTED = 'wp-bulk-action-start';
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

		$posts = dollie()->containers_query( $_REQUEST['containers'] );

		if ( empty( $posts ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'No matching sites were found for your selection.', 'dollie' ) ] );
			exit;
		}

		$targets               = [];
		$existing_bulk_actions = $this->get_bulk_actions();

		foreach ( $posts as $post ) {
			$container = dollie()->get_container( $post );

			if ( is_wp_error( $container ) ) {
				continue;
			}

			$exists = false;
			foreach ( $existing_bulk_actions as $action ) {
				if ( $container->get_original_url() === $action['container_uri'] ) {
					$exists = true;
				}
			}

			// Execute new action on container only if no other action is in progress.
			if ( ! $exists ) {
				$targets[] = [
					'id'           => $container->get_hash(),
					'uri'          => $container->get_original_url(),
					'is_blueprint' => $container->is_blueprint(),
				];
			}
		}

		$send_request = true;
		$command_data = [];

		if ( ( 'update-plugins' === $command || 'update-themes' === $command ) ) {
			if ( ! isset( $_REQUEST['command_data'] ) || ! is_array( $_REQUEST['command_data'] ) || empty( $_REQUEST['command_data'] ) ) {
				$send_request = false;
			} else {
				$command_data = $_REQUEST['command_data'];

				foreach ( $command_data as &$item ) {
					foreach ( $posts as $post ) {
						$container = dollie()->get_container( $post );

						if ( is_wp_error( $container ) ) {
							continue;
						}

						if ( (int) $item['id'] === $container->get_id() ) {
							$item['container_uri'] = $container->get_original_url();
						}
					}
				}

				unset( $item );
			}
		}

		if ( ! $send_request ) {
			if ( 'update-plugins' === $command ) {
				wp_send_json_error( [ 'message' => esc_html__( 'Please select at least one plugin to update.', 'dollie' ) ] );
				exit;
			}

			if ( 'update-themes' === $command ) {
				wp_send_json_error( [ 'message' => esc_html__( 'Please select at least one theme to update.', 'dollie' ) ] );
				exit;
			}
		}

		$response = Api::post(
			Api::ROUTE_CONTAINER_BULK_ACTION,
			[
				'targets'      => $targets,
				'command'      => $command,
				'command_data' => $command_data,
			]
		);

		// $log_id = Log::add_front(
		// self::LOG_ACTION_STARTED,
		// dollie()->get_current_object( $posts[0]->ID ),
		// [
		// $this->get_allowed_commands()[ $command ],
		// ]
		// );

		if ( is_array( $response ) ) {
			foreach ( $posts as $post ) {
				$container = dollie()->get_container( $post );

				if ( is_wp_error( $container ) ) {
					continue;
				}

				foreach ( $response as &$item ) {
					if ( $container->get_original_url() === $item['container_uri'] ) {
						$item['post_id'] = $post->ID;
						// $item['log_id']  = $log_id;
					}
				}

				unset( $item );
			}

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
		$actions = $this->get_bulk_actions();

		if ( empty( $actions ) ) {
			return [];
		}

		$processing = get_option( 'wpd_container_bulk_actions_processing_' . get_current_user_id() );

		if ( $processing ) {
			return [];
		}

		update_option( 'wpd_container_bulk_actions_processing_' . get_current_user_id(), true );

		$response = [];
		$targets  = [];

		foreach ( $actions as $action ) {
			$targets[] = $action['execution_id'];
		}

		if ( ! empty( $targets ) ) {
			$response = Api::post(
				Api::ROUTE_CONTAINER_BULK_ACTION_STATUS,
				[
					'targets' => $targets,
				]
			);
		}

		if ( ! empty( $response ) ) {
			foreach ( $actions as $key => $action ) {
				foreach ( $response as &$item ) {
					if ( $item['execution_id'] === $action['execution_id'] && $item['status'] ) {
						$item['container_uri'] = $action['container_uri'];

						// if we have a parent log id saved.
						if ( isset( $action['log_id'] ) ) {
							$container = dollie()->get_container( $action['post_id'] );

							$log_action = $this->get_log_action( $action['action'] );

							// Add individual log
							$sub_log_id = Log::add_front(
								$log_action,
								$container,
								[
									$container->get_slug(),
								]
							);

							update_post_meta( $sub_log_id, '_wpd_bulk_log', 1 );

							// update parent log with sub log ids.
							$parent_logs = get_post_meta( $action['log_id'], '_wpd_sub_logs', true );

							if ( ! $parent_logs ) {
								$parent_logs = [];
							}

							$parent_logs[ $sub_log_id ] = $sub_log_id;
							update_post_meta( $action['log_id'], '_wpd_sub_logs', $parent_logs );
						}

						unset( $actions[ $key ] );

						// Container::instance()->get_container_details( $action['post_id'], true );
						break;
					}
				}
			}

			$this->set_bulk_actions( $actions, true );
		}

		update_option( 'wpd_container_bulk_actions_processing_' . get_current_user_id(), false );

		return $response;
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
		$posts   = dollie()->containers_query( $_REQUEST['containers'] );

		if ( empty( $posts ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'No matching sites were found for your selection.', 'dollie' ) ] );
			exit;
		}

		$containers = [];

		foreach ( $posts as $post ) {
			$container = dollie()->get_container( $post );

			if ( is_wp_error( $container ) ) {
				continue;
			}

			$containers[] = $container->get_original_url();
		}

		$data = '';

		if ( 'update-plugins' === $command ) {
			// $plugins_response = dollie()->get_containers_plugins( $containers );
			$plugins_response = false;
			$plugins_data     = [];

			if ( false !== $plugins_response ) {
				foreach ( $plugins_response as $container_uri => $data ) {
					foreach ( $data as $plugin ) {
						$plugins_data[] = [
							'title' => $plugin['title'],
							'name'  => $plugin['name'],
						];
					}
				}

				$plugins_data = array_unique( $plugins_data, SORT_REGULAR );

				foreach ( $plugins_response as $container_uri => $data ) {
					foreach ( $data as $plugin ) {
						foreach ( $posts as $post ) {
							$container = dollie()->get_container( $post );

							if ( is_wp_error( $container ) ) {
								continue;
							}

							if ( $container_uri === $container->get_original_url() ) {
								foreach ( $plugins_data as &$plugin_data ) {
									if ( $plugin_data['name'] === $plugin['name'] ) {
										$plugin_data['sites'][ $post->ID ] = [
											'title' => $post->post_title,
											'url'   => $container_uri,
										];
									}
								}
							}
						}
					}
				}
			}

			$data = dollie()->load_template( 'loop/parts/modal-actions/parts/plugins', [ 'plugins_data' => $plugins_data ] );
		}

		if ( 'update-themes' === $command ) {
			// $themes_response = dollie()->get_containers_themes( $containers );
			$themes_response = false;
			$themes_data     = [];

			if ( false !== $themes_response ) {
				foreach ( $themes_response as $container_uri => $data ) {
					foreach ( $data as $theme ) {
						$themes_data[] = [
							'title' => $theme['title'],
							'name'  => $theme['name'],
						];
					}
				}

				$themes_data = array_unique( $themes_data, SORT_REGULAR );

				foreach ( $themes_response as $container_uri => $data ) {
					foreach ( $data as $theme ) {
						foreach ( $posts as $post ) {
							$container = dollie()->get_container( $post );

							if ( is_wp_error( $container ) ) {
								continue;
							}

							if ( $container_uri === $container->get_original_url() ) {
								foreach ( $themes_data as &$theme_data ) {
									if ( $theme_data['name'] === $theme['name'] ) {
										$theme_data['sites'][ $post->ID ] = [
											'title' => $post->post_title,
											'url'   => $container_uri,
										];
									}
								}
							}
						}
					}
				}
			}

			$data = dollie()->load_template( 'loop/parts/modal-actions/parts/themes', [ 'themes_data' => $themes_data ] );
		}

		wp_send_json_success( $data );
	}

	/**
	 * Set bulk actions
	 *
	 * @param array $data
	 *
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
	 *
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
}
