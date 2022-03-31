<?php

namespace Dollie\Core\Factories;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use WP_Post;
use Dollie\Core\Api\StagingApi;

final class User {

	/**
	 * @var \WP_User|int
	 */
	private $user;

	public function __construct( \WP_User|int $user = null ) {
		if ( is_null( $user ) ) {
			$this->user = wp_get_current_user();
		} elseif ( is_numeric( $user ) ) {
			$this->user = get_user_by( 'ID', $user );
		} else {
			$this->user = $user;
		}
	}

	/**
	 * Get ID
	 *
	 * @return integer
	 */
	public function get_id(): int {
		return $this->user->ID;
	}

	/**
	 * Get meta
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function get_meta( string $key ): mixed {
		return get_user_meta( $this->user->ID, $key, true );
	}

	/**
	 * View all sites permissions
	 *
	 * @return boolean
	 */
	public function can_view_all_sites(): bool {
		return user_can( $this->user->ID, 'read_private_wpd_sites' );
	}

	/**
	 * Manage all sites permissions
	 *
	 * @return boolean
	 */
	public function can_manage_all_sites(): bool {
		return user_can( $this->user->ID, 'edit_others_wpd_sites' );
	}

	/**
	 * Delete all sites permissions
	 *
	 * @return boolean
	 */
	public function can_delete_all_sites(): bool {
		return user_can( $this->user->ID, 'delete_others__wpd_sites' );
	}

	/**
	 * Get user user role for deploy
	 *
	 * @param null $user_id
	 *
	 * @return mixed|void
	 */
	public function get_container_user_role() {
		$role = get_user_meta( $this->get_id(), 'wpd_client_site_permissions', true );

		if ( empty( $role ) ) {
			$role = 'default';
		}

		if ( 'default' === $role ) {
			if ( user_can( $this->get_id(), 'manage_options' ) ) {
				$role = 'administrator';
			} else {
				$role = get_field( 'wpd_client_site_permission', 'options' );
			}
		}

		return $role ?: 'administrator';
	}

	/**
	 * Count containers
	 *
	 * @return integer
	 */
	public function count_containers(): int {
		$query = new \WP_Query(
			[
				'author'        => $this->get_id(),
				'post_type'     => 'container',
				'post_per_page' => -1,
				'post_status'   => 'publish',
			]
		);

		wp_reset_postdata();

		return $query->found_posts;
	}

	/**
	 * Count stagings
	 *
	 * @return integer
	 */
	public function count_stagings():int {
		$query = new \WP_Query(
			[
				'author'        => $this->get_id(),
				'post_type'     => 'container',
				'post_per_page' => -1,
				'post_status'   => 'publish',
				'meta_query'    => [
					[
						'key'   => 'wpd_has_staging',
						'value' => 'yes',
					],
				],
			]
		);

		wp_reset_postdata();

		return $query->found_posts;
	}
}
