<?php

namespace Dollie\Core\Jobs;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Api\SiteApi;
use Dollie\Core\Api\BlueprintApi;
use Dollie\Core\Api\StagingApi;
use Dollie\Core\Utils\ConstInterface;

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

		add_action( 'admin_init', [ $this, 'sync_containers' ] );
	}

	/**
	 * Sync with query string
	 */
	public function sync_containers() {
		if ( isset( $_GET['dollie-sync-containers'] ) ) {
			$this->run();
			if ( ! empty( $_GET['redirect_to'] ) ) {
				wp_safe_redirect( $_GET['redirect_to'] );
				exit;
			}
			
		}

		if ( isset( $_GET['dollie-sync-site'] ) && isset( $_GET['container-id'] ) && $_GET['container-id'] ) {
			$this->run_single_site( $_GET['container-id'] );
		}

		if ( isset( $_GET['dollie-sync-blueprint'] ) && isset( $_GET['container-id'] ) && $_GET['container-id'] ) {
			$this->run_single_blueprint( $_GET['container-id'] );
		}
	}

	public function run_single_site( $container_id ) {
		$data = $this->get_site_by_id( $container_id );

		if ( is_wp_error( $data ) || ! isset( $data[0] ) ) {
			return;
		}

		$this->run_single( $data[0] );
	}

	public function run_single_blueprint( $container_id ) {
		$data = $this->get_blueprint_by_id( $container_id );

		if ( is_wp_error( $data ) || ! isset( $data[0] ) ) {
			return;
		}

		$this->run_single( $data[0] );
	}

	public function run_single_with_data( $data ) {
		$this->run_single( $data );
	}

	private function run_single( $site ) {
		$stored_containers = get_posts(
			[
				'numberposts' => - 1,
				'post_type'   => 'container',
				'post_status' => [ 'publish', 'draft', 'trash' ],
			]
		);

		$container_id = 0;
		$exists       = false;

		foreach ( $stored_containers as $stored_container ) {
			$old_container_hash = get_post_meta( $stored_container->ID, 'wpd_container_id', true );

			// If container was stored during Dollie V1.0, switch to new version
			if ( $old_container_hash ) {
				if ( $site['hash'] !== $old_container_hash ) {
					continue;
				}

				update_post_meta( $stored_container->ID, 'dollie_container_type', $site['type'] );
				delete_post_meta( $stored_container->ID, 'wpd_container_id' );

				$container = dollie()->get_container( $stored_container );
			} else {
				$container = dollie()->get_container( $stored_container );

				if ( is_wp_error( $container ) || $site['hash'] !== $container->get_hash() ) {
					continue;
				}
			}

			$exists       = true;
			$container_id = $container->get_id();
			$container->set_details( $site );

			if ( $container->should_be_trashed() ) {
				wp_trash_post( $container->get_id() );
			} else {
				wp_publish_post( $container->get_id() );
				$synced_container_ids[] = $container->get_id();
			}
		}

		// If no such container found, create one with details from server's container.
		if ( ! $exists ) {
			$author = get_current_user_id();

			if ( $site['owner_email'] ) {
				$user = get_user_by( 'email', $site['owner_email'] );

				if ( false !== $user ) {
					$author = $user->ID;
				}
			}

			$post_name = explode( '.', $site['url'] );
			$post_name = $post_name[0];

			$container_id = wp_insert_post(
				[
					'post_type'   => 'container',
					'post_status' => 'publish',
					'post_name'   => $post_name,
					'post_title'  => $post_name,
					'post_author' => $author,
					'meta_input'  => [
						'dollie_container_type'     => $site['type'],
						'dollie_container_deployed' => 1,
					],
				]
			);

			$new_container_type = dollie()->get_container( $container_id );
			$new_container_type->set_details( $site );
		}

		if ( $site['type'] === ConstInterface::TYPE_BLUEPRINT ) {
			$bp = dollie()->get_container( $container_id );
			$bp->sync_settings( $site );
		}

		flush_rewrite_rules();
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

		$query = new \WP_Query(
			[
				'posts_per_page' => - 1,
				'post_type'      => 'container',
				'post_status'    => [ 'publish', 'draft', 'trash' ],
			]
		);

		$stored_containers    = $query->get_posts();
		$synced_container_ids = [];

		foreach ( $fetched_containers as $key => $fetched_container ) {
			$exists = false;

			$container_id = 0;

			foreach ( $stored_containers as $stored_container ) {
				$old_container_hash = get_post_meta( $stored_container->ID, 'wpd_container_id', true );

				// If container was stored during Dollie V1.0, switch to new version.
				if ( $old_container_hash ) {
					if ( $fetched_container['hash'] !== $old_container_hash ) {
						continue;
					}

					update_post_meta( $stored_container->ID, 'dollie_container_type', $fetched_container['type'] );
					update_post_meta( $stored_container->ID, 'dollie_vip_site',
						isset( $fetched_container['vip'] ) ? (int) $fetched_container['vip'] : 0
					);
					delete_post_meta( $stored_container->ID, 'wpd_container_id' );

					$container = dollie()->get_container( $stored_container );
				} else {
					$container = dollie()->get_container( $stored_container );

					if ( is_wp_error( $container ) || $fetched_container['hash'] !== $container->get_hash() ) {
						continue;
					}
				}

				$exists       = true;
				$container_id = $stored_container->ID;
				$container->set_details( $fetched_container );

				update_post_meta( $stored_container->ID, 'dollie_vip_site',
					isset( $fetched_container['vip'] ) ? (int) $fetched_container['vip'] : 0
				);

				if ( $container->should_be_trashed() ) {
					wp_trash_post( $container->get_id() );
				} else {
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

				$container_id = wp_insert_post(
					[
						'post_type'   => 'container',
						'post_status' => 'publish',
						'post_name'   => $post_name,
						'post_title'  => $post_name,
						'post_author' => $author,
						'meta_input'  => [
							'dollie_container_type'     => $fetched_container['type'],
							'dollie_vip_site'           => isset( $fetched_container['vip'] ) ? (int) $fetched_container['vip'] : 0,
							'dollie_container_deployed' => 1,
						],
					]
				);

				$new_container_type = dollie()->get_container( $container_id );
				$new_container_type->set_details( $fetched_container );

				$synced_container_ids[] = $container_id;
			}

			if ( $fetched_container['type'] === ConstInterface::TYPE_BLUEPRINT ) {
				$bp                                 = dollie()->get_container( $container_id );
				$fetched_containers[ $key ]['sync'] = $bp->sync_settings( $fetched_container );
			}
		}

		// Trash posts if they have no corresponding container.
		$stored_containers = get_posts(
			[
				'numberposts' => - 1,
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
