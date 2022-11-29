<?php

namespace Dollie\Core\Factories;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Utils\LogTrait;
use WP_Post;
use Dollie\Core\Utils\ConstInterface;
use Dollie\Core\Api\BackupApi;
use Dollie\Core\Api\ResourceApi;

abstract class BaseContainer implements ConstInterface {
	use BackupApi;
	use ResourceApi;
	use LogTrait;

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
		return 'Deploy Failure' === $this->get_status();
	}

	/**
	 * Check if it is underployed
	 *
	 * @return boolean
	 */
	public function should_be_trashed(): bool {
		return 'Undeployed' === $this->get_status() ||
			'Not Deployed' === $this->get_status() ||
			'Undeploying' === $this->get_status();
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
		return strpos( $this->get_original_url(), 'dollie-elementor-kits-sites' ) !== false;
	}

	/**
	 * Check if it is owned by current user
	 *
	 * @return boolean
	 */
	public function is_owned_by_current_user(): bool {
		$user = dollie()->get_user();

		return get_current_user_id() === $this->get_author_id() || $user->can_manage_all_sites();
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
	 * Check if container should be deleted
	 *
	 * @return boolean
	 */
	public function is_scheduled_for_deletion(): bool {
		$deleted_at = $this->get_details( 'deleted_at' );

		if ( is_wp_error( $deleted_at ) || empty( $deleted_at ) ) {
			return false;
		}

		return true;
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
	 * @param boolean $with_protocol
	 *
	 * @return string
	 */
	public function get_url( bool $with_protocol = false ): string {
		if ( $custom_url = $this->get_custom_domain() ) {
			return $with_protocol ? "https://{$custom_url}" : $custom_url;
		}

		return $with_protocol ? "https://{$this->get_original_url()}" : $this->get_original_url();
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
				if ( $url['status'] && substr( $url['name'], 0, 4 ) !== 'www.' ) {
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

		if ( ! $default_screenshot ) {
			$default_screenshot = DOLLIE_ASSETS_URL . 'img/generating-preview.png';
		}

		if ( 'Deploying' === $this->get_status() ) {
			return $default_screenshot;
		}

		$screenshot = get_the_post_thumbnail_url( $this->get_id() );

		if ( is_wp_error( $screenshot ) || ! $screenshot ) {
			return $default_screenshot;
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
	 * @param string $snapshotId
	 * @param string $type
	 *
	 * @return \WP_Error|array
	 */
	public function restore_backup( string $snapshotId, string $type ) {
		return $this->restore_container_backup( $this->get_hash(), $snapshotId, $type );
	}

	/**
	 * Get plugins
	 *
	 * @param boolean $force
	 *
	 * @return array
	 */
	public function get_plugins( bool $force = false ) {
		if ( $force ) {
			$plugins = $this->get_container_plugins( $this->get_hash() );

			if ( is_wp_error( $plugins ) ) {
				return [];
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
	 * @return array
	 */
	public function get_themes( bool $force = false ) {
		if ( $force ) {
			$themes = $this->get_container_themes( $this->get_hash() );

			if ( is_wp_error( $themes ) ) {
				return [];
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
	 * @return array
	 */
	public function get_active_theme(): array {
		$active_theme = $this->get_details( 'site.theme' );

		if ( is_wp_error( $active_theme ) ) {
			return [];
		}

		return $active_theme;
	}

	/**
	 * Get active theme name
	 *
	 * @return string
	 */
	public function get_active_theme_name(): string {
		$active_theme = $this->get_active_theme();

		if ( empty( $active_theme ) ) {
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

		if ( ! is_wp_error( $site_name ) ) {
			wp_update_post(
				[
					'ID'         => $this->get_id(),
					'post_title' => is_wp_error( $site_name ) ? __( 'Unnamed site', 'dolie' ) : $site_name,
				]
			);
		}

		if ( isset( $details['screenshot'] ) && $details['screenshot'] ) {
			if ( has_post_thumbnail( $this->get_id() ) ) {
				wp_delete_attachment( get_post_thumbnail_id( $this->get_id() ), true );
			}

			$screenshot_img = $details['screenshot'];

			$upload_dir = wp_upload_dir();
			$image_data = file_get_contents( $screenshot_img );
			$filename   = basename( $screenshot_img );

			if ( wp_mkdir_p( $upload_dir['path'] ) ) {
				$file = $upload_dir['path'] . '/' . $filename;
			} else {
				$file = $upload_dir['basedir'] . '/' . $filename;
			}

			file_put_contents( $file, $image_data );

			$wp_filetype = wp_check_filetype( $filename, null );

			$attach_id = wp_insert_attachment(
				[
					'post_mime_type' => $wp_filetype['type'],
					'post_title'     => sanitize_file_name( $filename ),
					'post_content'   => '',
					'post_status'    => 'inherit',
				],
				$file,
				$this->get_id()
			);

			require_once ABSPATH . 'wp-admin/includes/image.php';

			$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
			wp_update_attachment_metadata( $attach_id, $attach_data );
			set_post_thumbnail( $this->get_id(), $attach_id );
		}

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
			}

			return $this->find_value_recursively( $details, $composite_key );
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

	/**
	 * Check if site has VIP status
	 *
	 * @return boolean
	 */
	public function is_vip(): bool {
		return (bool) get_post_meta( $this->get_id(), 'dollie_vip_site', true );
	}

	/**
	 * Get customer login url
	 *
	 * @param string $location
	 *
	 * @return string
	 */
	public function get_customer_login_url( string $location = '' ): string {
		$url = home_url( 'site_login_redirect?site_id=' . $this->get_id() );

		$container_location = apply_filters( 'dollie/site/login_url/location', $location, $this );

		if ( ! empty( $container_location ) ) {
			$url = add_query_arg( 'location', $container_location, $url );
		}

		return wp_nonce_url( $url, 'get_site_login', '_nonce' );
	}

	/**
	 * Check if container needs updated
	 *
	 * @return boolean
	 */
	public function needs_updated(): bool {
		return ! (bool) get_transient( "container.updated.{$this->get_id()}" );
	}

	/**
	 * Mark container as updated
	 *
	 * @return void
	 */
	protected function mark_updated() {
		set_transient( "container.updated.{$this->get_id()}", '1', 1800 );
	}

	/**
	 * Mark container as not updated
	 *
	 * @return void
	 */
	public function mark_not_updated() {
		delete_transient( "container.updated.{$this->get_id()}" );
	}

	/**
	 * Get author user instance
	 *
	 * @return User
	 */
	public function user(): User {
		return new User( $this->get_author_id() );
	}

	/**
	 * Delete
	 *
	 * @return void
	 */
	protected function delete() {
		$this->set_details(
			[
				'deleted_at' => current_time( 'timestamp' ),
			]
		);
	}

	/**
	 * Restore
	 *
	 * @return void
	 */
	protected function restore() {
		$this->set_details(
			[
				'deleted_at' => '',
			]
		);
	}
}
