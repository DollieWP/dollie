<?php

/*
Container object reference

[
  "hash" => ""
  "type" => 0
  "url" => ""
  "custom_urls" => [
	[
	  "name" => ""
	  "primary" => 0
	  "status" => 1
	]
  ]
  "status" => ""
  "screenshot" => ""
  "secret" => ""
  "token" => ""
  "php" => ""
  "size" => 0
  "cache" => [
	"method" => null
	"op_cache" => "1"
	"object_cache" => false
  ]
  "site" => [
	"name" => ""
	"description" => ""
	"stats" => [
	  "posts_count" => "1"
	  "pages_count" => "1"
	  "users_count" => 1
	  "comments_total" => 1
	  "comments_moderation" => 0
	  "comments_approved" => 1
	  "comments_spam" => 0
	  "comments_trash" => 0
	]
	"admin" => [
	  "username" => ""
	  "email" => ""
	]
	"multisite" => false
	"wp_version" => ""
	"plugins" => [
	  [
		"name" => "Powered Cache"
		"slug" => "powered-cache"
		"loader" => "powered-cache/powered-cache.php"
		"active" => true
		"update" => true
		"version" => "2.1.1"
		"author" => "PoweredCache"
		"uri" => "https://poweredcache.com"
	  ]
	]
	"themes" => array:2 [
	  [
		"name" => "Twenty Twenty"
		"slug" => "twentytwenty"
		"active" => true
		"update" => false
		"version" => "1.9"
		"author" => "the WordPress team"
		"uri" => "https://wordpress.org/themes/twentytwenty/"
	  ]
	]
	"updates" => [
	  "themes" => 0
	  "plugins" => 1
	  "core" => [
		"available" => false
		"update_to" => "5.9.2"
	  ]
	]
	"login_url" => ""
  ]
  "backups" => [
	[
	  "date" => "2022-04-04"
	  "hour" => "07:52:00"
	  "restore" => false
	  "size" => "173 MB"
	]
  ]
  "credentials" => [
	"secret" => ""
	"ip" => ""
	"username" => ""
	"password" => ""
	"port" => ""
  ]
]

*/

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
	 * Base constructor
	 *
	 * @param WP_Post $post
	 */
	public function __construct( WP_Post $post ) {
		$this->post = $post;
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
		$url = $this->get_details( 'url' );

		if ( is_wp_error( $url ) ) {
			return '';
		}

		return $url;
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

		$custom_urls = $this->get_details( 'custom_urls' );

		if ( ! is_wp_error( $custom_urls ) ) {
			foreach ( $custom_urls as $url ) {
				if ( $url['primary'] && $url['active'] ) {
					return $url['name'];
				}
			}
		}

		return '';
	}

	/**
	 * Get type
	 *
	 * @return string
	 */
	public function get_type(): string {
		$type = $this->get_details( 'type' );

		if ( is_wp_error( $type ) ) {
			return '';
		}

		return $type;
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
	 * @return string
	 */
	public function get_hash(): string {
		$hash = $this->get_details( 'hash' );

		if ( is_wp_error( $hash ) ) {
			return '';
		}

		return $hash;
	}

	/**
	 * Get status
	 *
	 * @return string
	 */
	public function get_status(): string {
		$status = $this->get_details( 'status' );

		if ( is_wp_error( $status ) ) {
			return '';
		}

		return $status;
	}

	/**
	 * Get screenshot
	 *
	 * @return string
	 */
	public function get_screenshot(): string {
		$default_screenshot = get_field( 'default_screenshot', 'option' );

		if ( $default_screenshot && 'Deploying' === $this->get_status() ) {
			return $default_screenshot;
		}

		$screenshot = $this->get_details( 'screenshot' );

		if ( is_wp_error( $screenshot ) ) {
			return '';
		}

		return $screenshot;
	}

	/**
	 * Get WP version
	 *
	 * @return string
	 */
	public function get_wp_version(): string {
		$wp_version = $this->get_details( 'site.wp_version' );

		if ( is_wp_error( $wp_version ) ) {
			return '';
		}

		return $wp_version;
	}

	/**
	 * Get PHP version
	 *
	 * @return string
	 */
	public function get_php_version(): string {
		$php = $this->get_details( 'php' );

		if ( is_wp_error( $php ) ) {
			return '';
		}

		return $php;
	}

	/**
	 * Get storage size
	 *
	 * @return string
	 */
	public function get_storage_size(): string {
		$size = $this->get_details( 'size' );

		if ( is_wp_error( $size ) ) {
			return '';
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
		$data = $this->get_details( 'site.admin' );

		if ( ! is_wp_error( $data ) && is_array( $data ) && isset( $data[ $type ] ) ) {
			return (int) $data[ $type ];
		}

		return '';
	}

	/**
	 * Get posts count
	 *
	 * @return integer
	 */
	public function get_posts_count(): int {
		$count = $this->get_details( 'site.stats.posts_count' );

		if ( is_wp_error( $count ) ) {
			return 0;
		}

		return (int) $count;
	}

	/**
	 * Get pages count
	 *
	 * @return integer
	 */
	public function get_pages_count(): int {
		$count = $this->get_details( 'site.stats.pages_count' );

		if ( is_wp_error( $count ) ) {
			return 0;
		}

		return (int) $count;
	}

	/**
	 * Get users count
	 *
	 * @return integer
	 */
	public function get_users_count(): int {
		$count = $this->get_details( 'site.stats.users_count' );

		if ( is_wp_error( $count ) ) {
			return 0;
		}

		return (int) $count;
	}

	/**
	 * Get comments stats
	 *
	 * @param string $type
	 *
	 * @return int
	 */
	public function get_comments_stats( string $type = 'total' ): int {
		$data = $this->get_details( 'site.stats.comments' );

		if ( ! is_wp_error( $data ) && is_array( $data ) && isset( $data[ $type ] ) ) {
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
		$backups = $this->get_backups();

		if ( is_wp_error( $backups ) || ! is_array( $backups ) ) {
			return 0;
		}

		return count( $backups );
	}

	/**
	 * Get backups
	 *
	 * @param bool $force
	 *
	 * @return \WP_Error|array
	 */
	public function get_backups( bool $force = false ) {
		if ( $force ) {
			$backups = $this->get_container_backup( $this->get_hash() );

			if ( is_wp_error( $backups ) ) {
				return $backups;
			}

			$this->set_details(
				[
					'backups' => $backups,
				]
			);

			return $backups;
		}

		$backups = $this->get_details( 'backups' );

		if ( is_wp_error( $backups ) ) {
			return [];
		}

		return $backups;
	}

	/**
	 * Get backup restores
	 *
	 * @return array
	 */
	public function get_backup_restores(): array {
		$backups = $this->get_backups();

		if ( is_wp_error( $backups ) ) {
			return [];
		}

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
	public function create_backup() {
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
	public function restore_backup( string $backup, string $type ) {
		return $this->restore_container_backup( $this->get_hash(), $backup, $type );
	}

	/**
	 * Get plugins
	 *
	 * @param boolean $force
	 *
	 * @return \WP_Error|array
	 */
	public function get_plugins( bool $force = false ) {
		if ( $force ) {
			$plugins = $this->get_container_plugins( $this->get_hash() );

			if ( is_wp_error( $plugins ) ) {
				return $plugins;
			}

			$this->set_details(
				[
					'site' => [
						'plugins' => $plugins,
						'updates' => [
							'plugins' => count(
								array_filter(
									$plugins,
									function( $v ) {
										return true === $v['update'];
									}
								)
							),
						],
					],
				]
			);

			return $plugins;
		}

		$plugins = $this->get_details( 'site.plugins' );

		if ( is_wp_error( $plugins ) ) {
			return [];
		}

		return $plugins;
	}

	/**
	 * Get themes
	 *
	 * @param boolean $force
	 *
	 * @return boolean|array
	 */
	public function get_themes( bool $force = false ) {
		if ( $force ) {
			$themes = $this->get_container_themes( $this->get_hash() );

			if ( is_wp_error( $themes ) ) {
				return $themes;
			}

			$this->set_details(
				[
					'site' => [
						'themes'  => $themes,
						'updates' => [
							'themes' => count(
								array_filter(
									$themes,
									function( $v ) {
										return true === $v['update'];
									}
								)
							),
						],
					],
				]
			);

			return $themes;
		}

		$themes = $this->get_details( 'site.themes' );

		if ( is_wp_error( $themes ) ) {
			return [];
		}

		return $themes;
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
		$count = $this->get_details( 'site.updates.plugins' );

		if ( is_wp_error( $count ) ) {
			return 0;
		}

		return (int) $count;
	}

	/**
	 * Count updatable themes
	 *
	 * @return integer
	 */
	public function get_updatable_themes_count(): int {
		$count = $this->get_details( 'site.updates.themes' );

		if ( is_wp_error( $count ) ) {
			return 0;
		}

		return (int) $count;
	}

	/**
	 * Get credentials
	 *
	 * @return array
	 */
	public function get_credentials(): array {
		$credentials = $this->get_details( 'credentials' );

		if ( is_wp_error( $credentials ) ) {
			return [
				'secret'   => '',
				'ip'       => '',
				'username' => '',
				'password' => '',
				'port'     => '',
			];
		}

		return $credentials;
	}

	/**
	 * Update plugins
	 *
	 * @param array $plugins
	 *
	 * @return \WP_Error|array
	 */
	public function update_plugins( array $plugins ) {
		return $this->update_container_plugins( $this->get_hash(), $plugins );
	}

	/**
	 * Update themes
	 *
	 * @param array $themes
	 *
	 * @return \WP_Error|array
	 */
	public function update_themes( array $themes ) {
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
	 * Set meta
	 *
	 * @param array $data
	 *
	 * @return self
	 */
	public function set_details( array $data ): self {
		$details = get_post_meta( $this->get_id(), 'dollie_container_details', true );

		if ( empty( $details ) ) {
			$details = $data;
		} elseif ( is_array( $details ) ) {
			$details = array_merge( $details, $data );
		}

		update_post_meta( $this->get_id(), 'dollie_container_details', $details );
		delete_transient( "container.details.{$this->get_id()}" );

		$site_name = $this->get_details( 'site.name' );

		wp_update_post(
			[
				'ID'         => $this->get_id(),
				'post_title' => is_wp_error( $site_name ) ? __( 'Unnamed site', 'dolie' ) : $site_name,
			]
		);

		return $this;
	}

	/**
	 * Get details
	 *
	 * @param string $key
	 *
	 * @return \WP_Error|boolean|string|array
	 */
	public function get_details( string $key = '' ) {
		$details = get_transient( "container.details.{$this->get_id()}" );

		if ( empty( $details ) ) {
			$details = get_post_meta( $this->get_id(), 'dollie_container_details', true );

			if ( empty( $details ) ) {
				return new \WP_Error( 'Container details are not set' );
			}

			set_transient( "container.details.{$this->get_id()}", $details );
		}

		if ( $key ) {
			$composite_key = explode( '.', $key );
			$composite_key = array_filter(
				$composite_key,
				function( $v ) {
					return ! empty( $v );
				}
			);

			if ( empty( $composite_key ) ) {
				return $details;
			}

			if ( count( $composite_key ) === 1 && isset( $details[ $composite_key[0] ] ) ) {
				return $details[ $composite_key[0] ];
			} else {
				return $this->find_value_recursively( $details, $composite_key );
			}
		}

		return $details;
	}

	/**
	 * Find value recursively
	 *
	 * @param array $details
	 * @param array $composite_key
	 *
	 * @return \WP_Error|boolean|array|string|integer
	 */
	private function find_value_recursively( array $details, array $composite_key ) {
		foreach ( $composite_key as $index => $key ) {
			if ( isset( $details[ $key ] ) ) {
				if ( count( $composite_key ) === ( $index + 1 ) ) {
					return $details[ $key ];
				}

				$new_composite_key = $composite_key;
				unset( $new_composite_key[ $index ] );
				$new_composite_key = array_values( $new_composite_key );

				return $this->find_value_recursively( $details[ $key ], $new_composite_key );
			}

			return new \WP_Error( 'Failed to find value' );
		}

		return new \WP_Error( 'Failed to find value' );
	}

	/**
	 * Check if site has staging
	 *
	 * @return boolean
	 */
	public function has_staging(): bool {
		return false;
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

	/**
	 * Delete post
	 *
	 * @return boolean|null
	 */
	protected function delete() {
		return wp_delete_post( $this->get_id(), true );
	}
}
