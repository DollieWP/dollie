<?php

namespace Dollie\Core\Factories;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use WP_Post;
use Dollie\Core\Utils\ConstInterface;
use Dollie\Core\Api\BackupApi;
use Dollie\Core\Api\ResourceApi;
use Dollie\Core\Api\ActionApi;

abstract class BaseContainer implements ConstInterface {
	use BackupApi;
	use ResourceApi;
	use ActionApi;

	/**
	 * @var WP_Post
	 */
	protected $post;

	/**
	 * @var array
	 */
	private $data = [
		'hash',
		'url',
		'custom_url',
		'login_link',
		'wp_version',
		'screenshot',
		'storage_size',
		'status',
		'type',
	];

	/**
	 * Base constructor
	 *
	 * @param WP_Post $post
	 */
	public function __construct( WP_Post $post ) {
		$this->post = $post;
	}

	/**
	 * Update meta
	 *
	 * @param array $data
	 *
	 * @return self
	 */
	public function update_meta( array $data ): self {
		array_filter(
			$data,
			function ( $v, $k ) {
				return in_array( $k, $this->data, true ) && is_string( $v );
			},
			ARRAY_FILTER_USE_BOTH
		);

		foreach ( $data as $key => $value ) {
			update_post_meta( $this->post->ID, "dollie_container_{$key}", $value );
		}

		return $this;
	}

	/**
	 * Update post
	 *
	 * @param array $data
	 *
	 * @return self
	 */
	public function update_post( array $data ): self {
		wp_update_post(
			array_merge(
				$data,
				[ 'ID' => $this->get_id() ]
			)
		);

		return $this;
	}

	public function is_running(): bool {
		return 'Running' === $this->get_status();
	}

	/**
	 * Check if it is deploying
	 *
	 * @return boolean
	 */
	public function is_deploying(): bool {
		return (bool) $this->get_meta( 'deploying' );
	}

	/**
	 * Check if container is demo
	 *
	 * @return boolean
	 */
	public function is_demo(): bool {
		if ( strpos( $this->get_original_url(), 'dollie-elementor-kits-sites' ) !== false ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if it is owned by current user
	 *
	 * @return boolean
	 */
	public function is_owned_by_current_user(): bool {
		return get_current_user_id() === $this->get_author();
	}

	/**
	 * Check if post is site
	 *
	 * @return boolean
	 */
	public function is_site(): bool {
		return self::TYPE_SITE === $this->get_type();
	}

	/**
	 * Check if post is blueprint
	 *
	 * @return boolean
	 */
	public function is_blueprint(): bool {
		return self::TYPE_BLUEPRINT === $this->get_type();
	}

	/**
	 * Check if post is staging
	 *
	 * @return boolean
	 */
	public function is_staging(): bool {
		return self::TYPE_STAGING === $this->get_type();
	}

	/**
	 * Get post ID
	 *
	 * @return integer
	 */
	public function get_id(): int {
		return $this->post->ID;
	}

	/**
	 * Get post title
	 *
	 * @return string
	 */
	public function get_title(): string {
		return $this->post->post_title;
	}

	/**
	 * Get post slug
	 *
	 * @return string
	 */
	public function get_slug(): string {
		return $this->post->slug;
	}

	/**
	 * Get post name
	 *
	 * @return string
	 */
	public function get_name(): string {
		return $this->post->post_name;
	}

	/**
	 * Get post author
	 *
	 * @return integer
	 */
	public function get_author(): int {
		return $this->post->post_author;
	}

	/**
	 * Get post permalink
	 *
	 * @param string $append
	 * @param array  $query
	 *
	 * @return string
	 */
	public function get_permalink( string $append = '', array $query = [] ): string {
		return add_query_arg( $query, trailingslashit( get_permalink( $this->get_id() ) ) . $append );
	}

	/**
	 * Get original url
	 *
	 * @return string
	 */
	public function get_original_url(): string {
		return $this->get_meta( 'url' );
	}

	/**
	 * Get url
	 *
	 * @return string
	 */
	public function get_url(): string {
		if ( $custom_url = $this->get_custom_domain() ) {
			return $custom_url;
		}

		return $this->get_original_url();
	}

	/**
	 * Get custom url
	 *
	 * @return string
	 */
	public function get_custom_domain(): string {
		if ( ! $this->is_site() ) {
			return $this->get_original_url();
		}

		return (string) $this->get_meta( 'custom_url' );
	}

	/**
	 * Get login url
	 *
	 * @param string $location
	 *
	 * @return string
	 */
	public function get_login_url( string $location = '' ): string {
		$location = $location ? "&location={$location}" : '';

		$role = get_user_meta( $this->get_author(), 'wpd_client_site_permissions', true );

		if ( empty( $role ) || ! is_string( $role ) ) {
			if ( user_can( $this->get_author(), 'manage_options' ) ) {
				$role = 'administrator';
			} else {
				$role = get_field( 'wpd_client_site_permission', 'options' );
			}
		}

		if ( 'administrator' !== $role && current_user_can( 'manage_options' ) ) {
			$username = get_option( 'options_wpd_admin_user_name' );
		} else {
			// $username = $this->get_login_username();
		}

		return 'https://example';
		// return "{$this->get_url()}/wp-login.php?s5token={$this->get_login_token($username)}{$location}";
	}

	/**
	 * Get type
	 *
	 * @return boolean|string
	 */
	public function get_type(): bool|string {
		return $this->get_meta( 'type' );
	}

	/**
	 * Get type string
	 *
	 * @return string
	 */
	public function get_type_string(): string {
		if ( $this->is_site() ) {
			return __( 'Site', 'dollie' );
		}

		if ( $this->is_blueprint() ) {
			return __( 'Blueprint', 'dollie' );
		}

		if ( $this->is_staging() ) {
			return __( 'Staging', 'dollie' );
		}

		return '';
	}

	/**
	 * Get hash
	 *
	 * @return boolean|string
	 */
	public function get_hash(): bool|string {
		return $this->get_meta( 'hash' );
	}

	/**
	 * Get status
	 *
	 * @return boolean|string
	 */
	public function get_status(): bool|string {
		return $this->get_meta( 'status' );
	}

	/**
	 * Get screenshot
	 *
	 * @return boolean|string
	 */
	public function get_screenshot(): bool|string {
		$default_screenshot = get_field( 'default_screenshot', 'option' );

		if ( $default_screenshot && 'Deploying' === $this->get_status() ) {
			return $default_screenshot;
		}

		return $this->get_meta( 'screenshot' );
	}

	/**
	 * Get backups count
	 *
	 * @return integer
	 */
	public function get_backups_count(): int {
		return (int) $this->get_meta( 'backups_count' );
	}

	/**
	 * Get backups
	 *
	 * @return boolean|array
	 */
	public function get_backups(): bool|array {
		$backups = $this->get_container_backup( $this->get_hash() );

		if ( is_array( $backups ) ) {
			$this->set_meta( 'backups_count', count( $backups ) );
		}

		return $backups;
	}

	/**
	 * Create backup
	 *
	 * @return boolean|array
	 */
	public function create_backup(): bool|array {
		return $this->create_container_backup( $this->get_hash() );
	}

	/**
	 * Restore backup
	 *
	 * @param string $backup
	 * @param string $type
	 *
	 * @return boolean|array
	 */
	public function restore_backup( string $backup, string $type ): bool|array {
		return $this->restore_container_backup( $this->get_hash(), $backup, $type );
	}

	/**
	 * Get plugins
	 *
	 * @return boolean|array
	 */
	public function get_plugins(): bool|array {
		return $this->get_container_plugins( $this->get_hash() );
	}

	/**
	 * Get themes
	 *
	 * @return boolean|array
	 */
	public function get_themes(): bool|array {
		return $this->get_container_themes( $this->get_hash() );
	}

	/**
	 * Update plugins
	 *
	 * @param array $plugins
	 *
	 * @return boolean|array
	 */
	public function update_plugins( array $plugins ): bool|array {
		return $this->update_container_plugins( $this->get_hash(), $plugins );
	}

	/**
	 * Update themes
	 *
	 * @param array $themes
	 *
	 * @return boolean|array
	 */
	public function update_themes( array $themes ): bool|array {
		return $this->update_container_themes( $this->get_hash(), $themes );
	}

	/**
	 * Set deploying
	 *
	 * @return self
	 */
	public function set_deploying(): self {
		$this->set_meta( 'deploying', true );

		return $this;
	}

	/**
	 * Set not deploying
	 *
	 * @return self
	 */
	public function set_not_deploying(): self {
		$this->delete_meta( 'deploying' );

		return $this;
	}

	/**
	 * Set cookie
	 *
	 * @return self
	 */
	public function set_cookie(): self {
		if ( $this->is_blueprint() ) {
			setcookie( DOLLIE_BLUEPRINTS_COOKIE, '', time() - 3600, '/' );
		}

		return $this;
	}

	/**
	 * Set meta in bulk
	 *
	 * @param array $metas
	 *
	 * @return self
	 */
	public function set_metas( array $metas ): self {
		foreach ( $metas as $key => $value ) {
			$this->set_meta( $key, $value );
		}

		return $this;
	}

	/**
	 * Set meta
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return self
	 */
	public function set_meta( string $key, mixed $value ): self {
		update_post_meta( $this->post->ID, "dollie_container_{$key}", $value );

		return $this;
	}

	/**
	 * Get meta
	 *
	 * @param string $key
	 *
	 * @return boolean|string
	 */
	public function get_meta( string $key ): bool|string {
		return get_post_meta( $this->post->ID, "dollie_container_{$key}", true );
	}

	/**
	 * Delete meta
	 *
	 * @param string $key
	 *
	 * @return self
	 */
	public function delete_meta( string $key ): self {
		delete_post_meta( $this->post->id, "dollie_container_{$key}" );

		return $this;
	}

	/**
	 * Flush cache
	 *
	 * @return void
	 */
	public function flush_cache() {
		delete_transient( 'dollie_container_api_request_' . $this->get_id() . '_get_container_wp_info' );
		delete_transient( 'dollie_container_api_request_' . $this->get_id() . '_get_container_site_info' );
		delete_transient( 'dollie_site_users_' . $this->get_id() );
		delete_transient( 'dollie_site_news_' . $this->get_id() );
		delete_transient( 'dollie_site_new_screenshot_' . $this->get_id() );
	}

	public function after_status_change_event() {
		// if ( self::ACTION_START === $action ) {
		// $container->remove_undeploy_schedule();
		// delete_post_meta( $post_id, 'wpd_stop_container_at' );
		// delete_post_meta( $post_id, 'wpd_scheduled_for_removal' );
		// delete_post_meta( $post_id, 'wpd_undeploy_container_at' );
		// delete_post_meta( $post_id, 'wpd_scheduled_for_undeployment' );

		// Update the site status so it counts as an active site.
		// wp_update_post(
		// [
		// 'ID'          => $post_id,
		// 'post_status' => 'publish',
		// ]
		// );

		// Log::add_front( Log::WP_SITE_STARTED, $current_query, $site );
		// } elseif ( 'stop' === $action ) {
		// Get today's timestamp.
		// $today        = mktime( 0, 0, 0, date( 'm' ), date( 'd' ), date( 'Y' ) );
		// $trigger_date = get_post_meta( $post_id, 'wpd_stop_container_at', true );

		// If our "stop" time has passed our current time, it's time to flip the switch and stop the container.
		// if ( ( $trigger_date && $trigger_date < $today ) || ! $trigger_date ) {
		// $delay_in_days = 7;

		// Calculate the "remove" date and set it 3 days into the future.
		// $trigger_date = mktime( 0, 0, 0, date( 'm' ), date( 'd' ) + $delay_in_days, date( 'Y' ) );
		// $this->set_status( $post_id, 'stopped' );
		// update_post_meta( $post_id, 'wpd_scheduled_for_undeployment', 'yes' );
		// update_post_meta( $post_id, 'wpd_undeploy_container_at', $trigger_date );
		// }

		// Log::add_front( Log::WP_SITE_REMOVAL_SCHEDULED, $current_query, $site );
		// } elseif ( 'undeploy' === $action ) {
		// delete_post_meta( $post_id, 'wpd_stop_container_at' );
		// delete_post_meta( $post_id, 'wpd_scheduled_for_removal' );
		// delete_post_meta( $post_id, 'wpd_undeploy_container_at' );
		// delete_post_meta( $post_id, 'wpd_scheduled_for_undeployment' );

		// Log::add_front( Log::WP_SITE_UNDEPLOYED, $current_query, $site );
		// }

		// $this->set_status( $post_id, $action );
		// Log::add( $site . ' status changed to: ' . $action );
	}
}
