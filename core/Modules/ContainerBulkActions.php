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

		add_action( 'wp_ajax_dollie_do_bulk_action', [ $this, 'do_ajax_action' ] );

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
	 * Execute bulk command
	 *
	 * @return void
	 */
	public function do_ajax_action() {
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

		$targets = [];

		foreach ( $posts as $post ) {
			$targets[] = [
				'id'           => get_post_meta( $post->ID, 'wpd_container_id', true ),
				'uri'          => dollie()->get_wp_site_data( 'uri', $post->ID ),
				'is_blueprint' => dollie()->is_blueprint( $post->ID ),
			];
		}

		$response = Api::process_response( Api::post(
			Api::ROUTE_CONTAINER_BULK_ACTION,
			[
				'targets' => $targets,
				'command' => $command,
			]
		) );

		Log::add_front(
			self::LOG_ACTION_STARTED,
			dollie()->get_current_object( $posts[0]->ID ),
			[
				$this->get_allowed_commands()[ $command ],
			]
		);

		wp_send_json_success();
	}

}
