<?php

namespace Dollie\Core\Factories;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class User {
	/**
	 * @var \WP_User|int
	 */
	private $user;

	/**
	 * User Constructor
	 *
	 * @param \WP_User|integer|null $user
	 */
	public function __construct( $user = null ) {
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
	 * Get display name
	 *
	 * @return string
	 */
	public function get_display_name(): string {
		return $this->user->display_name;
	}

	/**
	 * Get email
	 *
	 * @return string
	 */
	public function get_email(): string {
		return $this->user->user_email;
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
	 * Update meta
	 *
	 * @param string $key
	 * @param $value
	 *
	 * @return mixed
	 */
	public function set_meta( string $key, $value ): mixed {
		return update_user_meta( $this->user->ID, $key, $value );
	}

	/**
	 * Delete meta
	 *
	 * @param string $key
	 *
	 * @return boolean
	 */
	public function delete_meta( string $key ): bool {
		return delete_user_meta( $this->user->ID, $key );
	}

	/**
	 * Check if can manage options
	 *
	 * @return boolean
	 */
	public function can_manage_options(): bool {
		return user_can( $this->user->ID, 'manage_options' );
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
	 * @return mixed
	 */
	public function get_container_user_role() {
		$role = get_user_meta( $this->get_id(), 'wpd_client_site_permissions', true );

		if ( empty( $role ) ) {
			$role = 'default';
		}

		if ( 'default' === $role ) {
			if ( $this->can_manage_all_sites() ) {
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

	/**
	 * Check if user should have his containers deleted
	 *
	 * @return boolean
	 */
	public function should_have_containers_deleted() {
		if ( ! $this->get_meta( 'wpd_stop_container_at' ) ) {
			return false;
		}

		return $this->get_meta( 'wpd_stop_container_at' ) < current_time( 'timestamp' );
	}

	/**
	 * Delete or restore containers
	 *
	 * @param [type] $has_subscription
	 * @return void
	 */
	public function delete_or_restore_containers( $has_subscription ) {
		$this->set_meta( 'wpd_active_subscription', $has_subscription ? 'yes' : 'no' );

		if ( ! $has_subscription ) {
			if ( ! $this->get_meta( 'wpd_stop_container_at' ) ) {
				$this->set_meta( 'wpd_stop_container_at', current_time( 'timestamp' ) + 3 * 86400 );
			}
		} else {
			$this->delete_meta( 'wpd_stop_container_at' );
		}

		$query = new \WP_Query(
			[
				'author'         => $this->user->ID,
				'post_type'      => 'container',
				'posts_per_page' => - 1,
			]
		);

		$posts = $query->get_posts();

		foreach ( $posts as $post ) {
			$container = dollie()->get_container( $post );

			if ( is_wp_error( $container ) ) {
				continue;
			}

			if ( ! $container->is_scheduled_for_deletion() && $this->should_have_containers_deleted() && ! $has_subscription ) {
				$container->delete();
			} elseif ( $container->is_scheduled_for_deletion() && ! $this->should_have_containers_deleted() && $has_subscription ) {
				$container->restore();
			}
		}

		wp_reset_postdata();
		wp_reset_query();
	}
}
