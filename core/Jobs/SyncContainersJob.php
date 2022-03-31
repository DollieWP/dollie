<?php

namespace Dollie\Core\Jobs;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Log;
use Dollie\Core\Api\SiteApi;
use Dollie\Core\Api\BlueprintApi;
use Dollie\Core\Api\StagingApi;

/**
 * Class SyncContainersJob
 *
 * @package Dollie\Core\Modules
 */
class SyncContainersJob extends Singleton {
	use SiteApi;
	use BlueprintApi;
	use StagingApi;

	/**
	 * Jobs constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'init', [ $this, 'init_recurring_tasks' ] );

		add_action( 'dollie/jobs/single/sync_containers', [ $this, 'run' ], 10 );
		add_action( 'dollie/jobs/recurring/sync_containers', [ $this, 'run' ], 10 );
	}

	/**
	 * Init recurring tasks
	 */
	public function init_recurring_tasks() {
		if ( false === as_next_scheduled_action( 'dollie/jobs/recurring/sync_containers' ) ) {
			as_schedule_recurring_action( strtotime( 'today' ), DAY_IN_SECONDS, 'dollie/jobs/recurring/sync_containers' );
		}
	}

	/**
	 * Create containers if they don't exist locally
	 * Delete containers if they are no longer running, eq. deleted, undeployed
	 *
	 * @return array|mixed
	 */
	public function run() {
		$sites      = $this->get_sites();
		$blueprints = $this->get_blueprints();
		$stagings   = $this->get_stagings();

		$containers = [];

		if ( ! is_wp_error( $sites ) ) {
			$containers = array_merge( $containers, $sites );
		}

		if ( ! is_wp_error( $blueprints ) ) {
			$containers = array_merge( $containers, $blueprints );
		}

		if ( ! is_wp_error( $stagings ) ) {
			$containers = array_merge( $containers, $stagings );
		}

		$stored_containers = get_posts(
			[
				'numberposts' => -1,
				'post_type'   => 'container',
				'post_status' => [ 'publish', 'draft', 'trash' ],
			]
		);

		$synced_container_ids = [];

		foreach ( $containers as $key => $container ) {
			$exists = false;

			foreach ( $stored_containers as $stored_container ) {
				$container_type = dollie()->get_container( $stored_container->ID );

				if ( is_wp_error( $container_type ) ) {
					continue;
				}

				if ( $container['hash'] !== $container_type->get_hash() ) {
					continue;
				}

				$exists = true;
				$container_type->update_meta( $container );

				if ( ! $container_type->is_running() ) {
					wp_trash_post( $container_type->get_id() );
				}

				if ( $container_type->is_running() ) {
					wp_publish_post( $container_type->get_id() );
					$synced_container_ids[] = $container_type->get_id();
				}
			}

			// If no such container found, create one with details from server's container.
			if ( ! $exists ) {
				$new_container_id = wp_insert_post(
					[
						'post_type'   => 'container',
						'post_status' => 'publish',
						'post_name'   => $container['url'],
						'post_title'  => $container['url'],
						'post_author' => get_current_user_id(),
						'meta_input'  => [
							'dollie_container_type' => $container['type'],
						],
					]
				);

				$new_container_type = dollie()->get_container( $new_container_id );
				$new_container_type->update_meta( $container );

				Log::add( 'Container added from sync ' . $container['url'] );
				$synced_container_ids[] = $new_container_id;
			}
		}

		// Trash posts if they have no corresponding container.
		$stored_containers = get_posts(
			[
				'post_type'   => 'container',
				'post_status' => [ 'publish', 'draft' ],
			]
		);

		foreach ( $stored_containers as $stored_container ) {
			if ( ! in_array( $stored_container->ID, $synced_container_ids, false ) ) {
				wp_trash_post( $stored_container->ID );
			}
		}

		flush_rewrite_rules();

		return $containers;
	}

}
