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

		$fetched_containers = [];

		if ( ! is_wp_error( $sites ) ) {
			$fetched_containers = array_merge( $fetched_containers, $sites );
		}

		if ( ! is_wp_error( $blueprints ) ) {
			$fetched_containers = array_merge( $fetched_containers, $blueprints );
		}

		if ( ! is_wp_error( $stagings ) ) {
			$fetched_containers = array_merge( $fetched_containers, $stagings );
		}

		$stored_containers = get_posts(
			[
				'numberposts' => -1,
				'post_type'   => 'container',
				'post_status' => [ 'publish', 'draft', 'trash' ],
			]
		);

		$synced_container_ids = [];

		foreach ( $fetched_containers as $key => $fetched_container ) {
			$exists = false;

			foreach ( $stored_containers as $stored_container ) {
				$old_container_hash = get_post_meta( $stored_container->ID, 'wpd_container_id', true );

				// If container was stored during Dollie V1.0, switch to new version
				if ( $old_container_hash ) {
					if ( $fetched_container['hash'] !== $old_container_hash ) {
						continue;
					}

					update_post_meta( $stored_container->ID, 'dollie_container_type', $fetched_container['type'] );
					delete_post_meta( $stored_container->ID, 'wpd_container_id' );

					$container = dollie()->get_container( $stored_container );
				} else {
					$container = dollie()->get_container( $stored_container );

					if ( is_wp_error( $container ) || $fetched_container['hash'] !== $container->get_hash() ) {
						continue;
					}
				}

				$exists = true;
				$container->set_details( $fetched_container );

				if ( ! $container->is_running() ) {
					wp_trash_post( $container->get_id() );
				} elseif ( $container->is_running() ) {
					wp_publish_post( $container->get_id() );
					$synced_container_ids[] = $container->get_id();
				}
			}

			// If no such container found, create one with details from server's container.
			if ( ! $exists ) {
				$author = get_current_user_id();

				if ( $fetched_container['owner_email'] ) {
					$user = get_user_by( 'email', $fetched_container['owner_email'] );

					if ( false !== $user ) {
						$author = $user->ID;
					}
				}

				$post_name = explode( '.', $fetched_container['url'] );
				$post_name = $post_name[0];

				$new_container_id = wp_insert_post(
					[
						'post_type'   => 'container',
						'post_status' => 'publish',
						'post_name'   => $post_name,
						'post_title'  => $post_name,
						'post_author' => $author,
						'meta_input'  => [
							'dollie_container_type' => $fetched_container['type'],
							'dollie_container_deployed' => 1,
						],
					]
				);

				$new_container_type = dollie()->get_container( $new_container_id );
				$new_container_type->set_details( $fetched_container );

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

		return $fetched_containers;
	}

}
