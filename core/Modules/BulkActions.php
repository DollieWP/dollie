<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Log;
use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;

/**
 * Class BulkActions
 *
 * @package Dollie\Core\Modules
 */
class BulkActions extends Singleton {

	public const LOG_ACTION_STARTED        = 'wp-bulk-action-start';
	public const LOG_UPDATE_PLUGINS        = 'wp-bulk-update-plugins';
	public const LOG_UPDATE_THEMES         = 'wp-bulk-update-themes';
	public const LOG_REGENERATE_SCREENSHOT = 'wp-bulk-regenerate-screenshot';

	/**
	 * Container constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_filter( 'dollie/log/actions', [ $this, 'log_action_filter' ], 10, 3 );
		add_filter( 'dollie/log/actions/content', [ $this, 'log_action_content_filter' ], 10, 3 );

		add_action( 'wp_ajax_dollie_do_bulk_action', [ $this, 'do_bulk_action' ] );
		add_action( 'wp_ajax_dollie_check_bulk_action', [ $this, 'check_bulk_action' ] );
		add_action( 'wp_ajax_dollie_get_bulk_action_data', [ $this, 'get_action_data' ] );
	}

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

		$actions[ self::LOG_UPDATE_PLUGINS ]        = [
			'title'   => __( 'Plugins updated', 'dollie' ),
			'content' => __( sprintf( 'Site %s has completed updating plugins.', $values[0] ), 'dollie' ),
			'type'    => 'bulk',
			'link'    => false,
		];
		$actions[ self::LOG_UPDATE_THEMES ]         = [
			'title'   => __( 'Themes updated', 'dollie' ),
			'content' => __( sprintf( 'Site %s has completed updating themes.', $values[0] ), 'dollie' ),
			'type'    => 'bulk',
			'link'    => false,
		];
		$actions[ self::LOG_REGENERATE_SCREENSHOT ] = [
			'title'   => __( 'Screenshot regenerated', 'dollie' ),
			'content' => __( sprintf( 'Site %s has completed regenerating screenshot.', $values[0] ), 'dollie' ),
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
			'update-plugins'        => __( 'Update Plugins', 'dollie' ),
			'update-themes'         => __( 'Update Themes', 'dollie' ),
			'create-backup'         => __( 'Create Backup', 'dollie' ),
			'regenerate-screenshot' => __( 'Regenerate Screenshot', 'dollie' ),
			'restart'               => __( 'Restart', 'dollie' ),
			'stop'                  => __( 'Stop', 'dollie' ),
		];
	}

	/**
	 * Get allowed bulk commands in progress
	 *
	 * @return array
	 */
	public function get_allowed_commands_in_progress() {
		return [
			'update-plugins'        => __( 'Updating Plugins', 'dollie' ),
			'update-themes'         => __( 'Updating Themes', 'dollie' ),
			'create-backup'         => __( 'Creating Backup', 'dollie' ),
			'regenerate-screenshot' => __( 'Regenerating Screenshot', 'dollie' ),
			'restart'               => __( 'Restarting', 'dollie' ),
			'stop'                  => __( 'Stopping', 'dollie' ),
		];
	}

	/**
	 * Get allowed bulk commands in progress
	 *
	 * @return string
	 */
	public function get_log_action( $action ) {
		$actions = [
			'update-plugins'        => self::LOG_UPDATE_PLUGINS,
			'update-themes'         => self::LOG_UPDATE_THEMES,
			'create-backup'         => Log::WP_SITE_BACKUP_STARTED,
			'regenerate-screenshot' => self::LOG_REGENERATE_SCREENSHOT,
			'restart'               => Log::WP_SITE_RESTARTED,
			'stop'                  => Log::WP_SITE_STOPPED,
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
			'update-plugins'        => self::LOG_UPDATE_PLUGINS,
			'update-themes'         => self::LOG_UPDATE_THEMES,
			'create-backup'         => Log::WP_SITE_BACKUP_STARTED,
			'regenerate-screenshot' => self::LOG_REGENERATE_SCREENSHOT,
			'restart'               => Log::WP_SITE_RESTARTED,
			'stop'                  => Log::WP_SITE_STOPPED,
		];

		return $actions[ $action ] ?? '';
	}

	/**
	 * Execute bulk command
	 *
	 * @return void
	 */
	public function do_bulk_action() {
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

		$send_request = true;
		$command_data = [];

		if ( ( 'update-plugins' === $command || 'update-themes' === $command ) ) {
			if ( ! isset( $_REQUEST['command_data'] ) || ! is_array( $_REQUEST['command_data'] ) || empty( $_REQUEST['command_data'] ) ) {
				$send_request = false;
			} else {
				$command_data = $_REQUEST['command_data'];

				foreach ( $command_data as &$item ) {
					foreach ( $posts as $post ) {
						if ( (int) $item['id'] === $post->ID ) {
							$item['container_uri'] = dollie()->get_wp_site_data( 'uri', $post->ID );
						}
					}
				}
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

		$log_id = Log::add_front(
			self::LOG_ACTION_STARTED,
			dollie()->get_current_object( $posts[0]->ID ),
			[
				$this->get_allowed_commands()[ $command ],
			]
		);

		if ( is_array( $response ) ) {
			foreach ( $posts as $post ) {
				foreach ( $response as &$item ) {
					if ( dollie()->get_wp_site_data( 'uri', $post->ID ) === $item['container_uri'] ) {
						$item['post_id'] = $post->ID;
						$item['log_id']  = $log_id;
					}
				}
			}

			$this->set_bulk_actions( $response );

			foreach ( $response as &$item ) {
				$item['text'] = $this->get_allowed_commands_in_progress()[ $item['action'] ];
			}
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

							$site_object = dollie()->get_current_object( $action['post_id'] );
							$log_action  = $this->get_log_action( $action['action'] );

							// Add individual log
							$sub_log_id = Log::add_front(
								$log_action,
								$site_object,
								[
									$site_object->slug,
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

						Container::instance()->get_container_details( $action['post_id'], true );
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
			$containers[] = dollie()->get_wp_site_data( 'uri', $post->ID );
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
							if ( $container_uri === dollie()->get_wp_site_data( 'uri', $post->ID ) ) {
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
							if ( $container_uri === dollie()->get_wp_site_data( 'uri', $post->ID ) ) {
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
