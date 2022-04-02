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
		'title',
		'hash',
		'url',
		'custom_url',
		'login_link',
		'wp_version',
		'php_verison',
		'screenshot',
		'storage_size',
		'admin',
		'posts_count',
		'pages_count',
		'users_count',
		'comments',
		'backups',
		'plugins',
		'themes',
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
			if ( 'title' === $key ) {
				wp_update_post(
					[
						'ID'         => $this->post->ID,
						'post_title' => $value,
					]
				);
			} else {
				update_post_meta( $this->post->ID, "dollie_container_{$key}", $value );
			}
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

	/**
	 * Check if it is running
	 *
	 * @return boolean
	 */
	public function is_running(): bool {
		return 'Running' === $this->get_status();
	}

	/**
	 * Check if it is running
	 *
	 * @return boolean
	 */
	public function is_stopped(): bool {
		return 'Stopped' === $this->get_status();
	}

	/**
	 * Check if it is failed
	 *
	 * @return boolean
	 */
	public function is_failed(): bool {
		return 'Deploy Failed' === $this->get_status();
	}

	/**
	 * Check if it is deploying
	 *
	 * @return boolean
	 */
	public function is_deploying(): bool {
		return 'Deploying' === $this->get_status();
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
		return get_current_user_id() === $this->get_author_id();
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
	 * Get post author ID
	 *
	 * @return integer
	 */
	public function get_author_id(): int {
		return $this->post->post_author;
	}

	/**
	 * Get post author name
	 *
	 * @return string
	 */
	public function get_author_name(): string {
		return get_the_author_meta( 'display_name', $this->get_author_id() );
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

		$role = get_user_meta( $this->get_author_id(), 'wpd_client_site_permissions', true );

		if ( empty( $role ) || ! is_string( $role ) ) {
			if ( user_can( $this->get_author_id(), 'manage_options' ) ) {
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
	 * Get WP version
	 *
	 * @return boolean|string
	 */
	public function get_wp_version(): bool|string {
		return $this->get_meta( 'wp_version' );
	}

	/**
	 * Get PHP version
	 *
	 * @return boolean|string
	 */
	public function get_php_version(): bool|string {
		return $this->get_meta( 'php_version' );
	}

	/**
	 * Get storage size
	 *
	 * @return string
	 */
	public function get_storage_size(): string {
		$size = $this->get_meta( 'storage_size' );

		if ( false === $size ) {
			return '0 MB';
		}

		$base     = log( $size, 1024 );
		$suffixes = [ '', 'KB', 'MB', 'GB', 'TB' ];

		return round( pow( 1024, $base - floor( $base ) ) ) . ' ' . $suffixes[ floor( $base ) ];
	}

	/**
	 * Get admin
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	public function get_admin( string $type ): string {
		$data = $this->get_meta( 'admin' );

		if ( is_array( $data ) && isset( $data[ $type ] ) ) {
			return $data[ $type ];
		}

		return '';
	}

	/**
	 * Get posts count
	 *
	 * @return integer
	 */
	public function get_posts_count(): int {
		return (int) $this->get_meta( 'posts_count' );
	}

	/**
	 * Get pages count
	 *
	 * @return integer
	 */
	public function get_pages_count(): int {
		return (int) $this->get_meta( 'pages_count' );
	}

	/**
	 * Get users count
	 *
	 * @return integer
	 */
	public function get_users_count(): int {
		return (int) $this->get_meta( 'users_count' );
	}

	/**
	 * Get comments stats
	 *
	 * @param string $type
	 *
	 * @return int
	 */
	public function get_comments_stats( string $type = 'total' ): int {
		$data = $this->get_meta( 'comments' );

		if ( is_array( $data ) && isset( $data[ $type ] ) ) {
			return (int) $data[ $type ];
		}

		return 0;
	}

	/**
	 * Get backups count
	 *
	 * @return integer
	 */
	public function get_backups_count(): int {
		$backups = $this->get_meta( 'backups' );

		return is_array( $backups ) ? count( $backups ) : 0;
	}

	/**
	 * Get backups
	 *
	 * @param bool $backups
	 *
	 * @return array
	 */
	public function get_backups( bool $force = false ): array {
		if ( $force ) {
			$backups = $this->get_container_backup( $this->get_hash() );

			if ( is_array( $backups ) ) {
				$this->set_meta( 'backups', $backups );
			}

			return $backups;
		}

		$backups = $this->get_meta( 'backups' );

		return is_array( $backups ) ? $backups : [];
	}

	/**
	 * Get backup restores
	 *
	 * @return array
	 */
	public function get_backup_restores(): array {
		$backups = $this->get_backups();

		return array_filter(
			$backups,
			function( $v ) {
				return true === $v['restore'];
			}
		);
	}

	/**
	 * Create backup
	 *
	 * @return \WP_Error|array
	 */
	public function create_backup(): \WP_Error|array {
		return $this->create_container_backup( $this->get_hash() );
	}

	/**
	 * Restore backup
	 *
	 * @param string $backup
	 * @param string $type
	 *
	 * @return \WP_Error|array
	 */
	public function restore_backup( string $backup, string $type ): \WP_Error|array {
		return $this->restore_container_backup( $this->get_hash(), $backup, $type );
	}

	/**
	 * Get plugins
	 *
	 * @param boolean $force
	 *
	 * @return boolean|array
	 */
	public function get_plugins( bool $force = false ): bool|array {
		if ( $force ) {
			$plugins = $this->get_container_plugins( $this->get_hash() );

			$this->update_meta(
				[
					'plugins' => $plugins,
				]
			);

			return $plugins;
		}

		return $this->get_meta( 'plugins' );
	}

	/**
	 * Get themes
	 *
	 * @param boolean $force
	 *
	 * @return boolean|array
	 */
	public function get_themes( bool $force = false ): bool|array {
		if ( $force ) {
			$themes = $this->get_container_themes( $this->get_hash() );

			$this->update_meta(
				[
					'themes' => $themes,
				]
			);

			return $themes;
		}

		return $this->get_meta( 'themes' );
	}

	/**
	 * Get active theme
	 *
	 * @return boolean|array
	 */
	public function get_active_theme(): bool|array {
		foreach ( $this->get_themes() as $theme ) {
			if ( $theme['active'] ) {
				return $theme;
			}
		}

		return false;
	}

	/**
	 * Get active theme name
	 *
	 * @return string
	 */
	public function get_active_theme_name(): string {
		$active_theme = $this->get_active_theme();

		if ( false === $active_theme ) {
			return '';
		}

		return $active_theme['name'];
	}

	/**
	 * Count updatable plugins
	 *
	 * @return integer
	 */
	public function get_updatable_plugins_count(): int {
		$counter = 0;

		foreach ( $this->get_plugins() as $plugin ) {
			if ( $plugin['update'] ) {
				$counter++;
			}
		}

		return $counter;
	}

	/**
	 * Count updatable themes
	 *
	 * @return integer
	 */
	public function get_updatable_themes_count(): int {
		$counter = 0;

		foreach ( $this->get_themes() as $plugin ) {
			if ( $plugin['update'] ) {
				$counter++;
			}
		}

		return $counter;
	}

	/**
	 * Update plugins
	 *
	 * @param array $plugins
	 *
	 * @return \WP_Error|array
	 */
	public function update_plugins( array $plugins ): \WP_Error|array {
		return $this->update_container_plugins( $this->get_hash(), $plugins );
	}

	/**
	 * Update themes
	 *
	 * @param array $themes
	 *
	 * @return \WP_Error|array
	 */
	public function update_themes( array $themes ): \WP_Error|array {
		return $this->update_container_themes( $this->get_hash(), $themes );
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
	 * @return boolean|string|array
	 */
	public function get_meta( string $key ): bool|string|array {
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
	 * Check if site has staging
	 *
	 * @return boolean
	 */
	public function has_staging(): bool {
		return false;
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
