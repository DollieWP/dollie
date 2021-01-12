<?php

namespace Dollie\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Logger
 *
 * @package Dollie\Core
 */
class Log {

	const WP_SITE_DEPLOY_STARTED        = 'wp-site-deploy-start';
	const WP_SITE_DEPLOYED              = 'wp-site-deployed';
	const WP_SITE_DEPLOY_FAILED         = 'wp-site-deploy-failed';
	const WP_SITE_SETUP_COMPLETED       = 'wp-site-setup-completed';
	const WP_SITE_SETUP_FAILED          = 'wp-site-setup-failed';
	const WP_SITE_BACKUP_STARTED        = 'wp-site-backup-started';
	const WP_SITE_REMOVAL_SCHEDULED     = 'wp-site-removal-scheduled';
	const WP_SITE_STARTED               = 'wp-site-started';
	const WP_SITE_STOPPED               = 'wp-site-stopped';
	const WP_SITE_UNDEPLOYED            = 'wp-site-undeployed';
	const WP_SITE_DELETED               = 'wp-site-deleted';
	const WP_SITE_DOMAIN_LINKED         = 'wp-site-domain-linked';
	const WP_SITE_DOMAIN_ADDED          = 'wp-site-domain-added';
	const WP_SITE_DOMAIN_LINK_ERROR     = 'wp-site-domain-link-error';
	const WP_SITE_CLOUDFLARE_LINKED     = 'wp-site-cloudflare-linked';
	const WP_SITE_CLOUDFLARE_ZONE_ADDED = 'wp-site-cloudflare-zone-added';
	const WP_SITE_LETSENCRYPT_FAILED    = 'wp-site-letsencrypt_failed';
	const WP_SITE_BLUEPRINT_DEPLOYED    = 'wp-site-blueprint-deployed';

	/**
	 * Add logs using WDS_Log_Post
	 *
	 * @param $title
	 * @param string $message
	 * @param string $type
	 * @param null   $log_post_id
	 * @param false  $completed
	 *
	 * @return false|int|\WP_Error The ID of the log post, or WP_Error upon failure.
	 */
	public static function add( $title, $message = '', $type = 'general', $log_post_id = null, $completed = false ) {
		if ( class_exists( \WDS_Log_Post::class ) ) {
			return \WDS_Log_Post::log_message( 'dollie-logs', $title, $message, $type, $log_post_id, $completed );
		}

		return false;
	}

	/**
	 * Add user notification
	 *
	 * @param string       $action Log action for content retrieval
	 * @param object|array $object Object with Site ID, Slug, Author
	 * @param array|string $values Values for content replacement
	 * @param string       $extra_content
	 *
	 * @return false|int|\WP_Error
	 */
	public static function add_front( $action, $object = null, $values = [], $extra_content = '' ) {

		if ( ! is_array( $values ) ) {
			$values = [ $values ];
		}

		$content = self::get_content( $action, $values );
		if ( '' !== $extra_content ) {
			$content['content'] .= '<br class="extra-content">' . $extra_content;
		}

		// create the log entry.
		$log_id = self::add( $content['title'], $content['content'], $content['type'] );

		if ( is_array( $object ) && ! empty( $object ) ) {
			$object = (object) $object;
		}

		// continue to add the user front-end log data.
		if ( ! $log_id || empty( $object ) || ! is_object( $object ) || ! isset( $object->id ) ) {
			return false;
		}

		// Set status to unread and save site ID
		update_post_meta( $log_id, '_wpd_read', 0 );
		update_post_meta( $log_id, '_wpd_site_id', $object->id );
		update_post_meta(
			$log_id,
			'_wpd_log_data',
			[
				'action' => $action,
				'values' => $values,
			]
		);

		// Set log for site owner
		$args = [
			'ID'          => $log_id,
			'post_author' => $object->author,
		];

		wp_update_post( $args );

		// TODO also add email notification for some based on type.

		return $log_id;

	}

	/**
	 * @param string $action
	 * @param array  $values
	 *
	 * @return array
	 */
	public static function get_content( $action, $values = [] ) {

		if ( ! isset( $values[1] ) ) {
			$values[1] = '';
		}

		$actions = [
			self::WP_SITE_DEPLOY_STARTED        => [
				'title'   => __( 'Site deploy started', 'dollie' ),
				'content' => __( sprintf( 'Deploying WordPress site %s. You\'ll get a notification when is ready. ', $values[0] ), 'dollie' ),
				'type'    => 'deploy',
				'link'    => false,
			],
			self::WP_SITE_DEPLOYED              => [
				'title'   => __( 'Site deployed', 'dollie' ),
				'content' => __( sprintf( 'WordPress site %s has been successfully created.', $values[0] ), 'dollie' ),
				'type'    => 'deploy',
				'link'    => true,
			],
			self::WP_SITE_DEPLOY_FAILED         => [
				'title'   => __( 'Site deploy failed', 'dollie' ),
				'content' => __( sprintf( 'WordPress site %s has failed to deploy. Please contact site admin if the issue persists.', $values[0] ), 'dollie' ),
				'type'    => 'deploy',
				'link'    => false,
			],
			self::WP_SITE_SETUP_COMPLETED       => [
				'title'   => __( 'Site setup completed', 'dollie' ),
				'content' => __( sprintf( 'Initial WordPress site setup has completed for %s.', $values[0] ), 'dollie' ),
				'type'    => 'setup',
				'link'    => true,
			],
			self::WP_SITE_SETUP_FAILED          => [
				'title'   => __( 'Site setup failed', 'dollie' ),
				'content' => __( sprintf( 'Initial site setup has failed for site %s.', $values[0] ), 'dollie' ),
				'type'    => 'setup',
				'link'    => false,
			],
			self::WP_SITE_BACKUP_STARTED        => [
				'title'   => __( 'Backup triggered', 'dollie' ),
				'content' => __( sprintf( 'A new backup has started for %s.', $values[0] ), 'dollie' ),
				'type'    => 'action',
				'link'    => true,
			],
			self::WP_SITE_REMOVAL_SCHEDULED     => [
				'title'   => __( 'Site removal notice', 'dollie' ),
				'content' => __( sprintf( 'Site %s has been scheduled for removal.', $values[0] ), 'dollie' ),
				'type'    => 'undeploy',
				'link'    => false,
			],
			self::WP_SITE_UNDEPLOYED            => [
				'title'   => __( 'Site undeployed', 'dollie' ),
				'content' => __( sprintf( 'Site %s was undeployed.', $values[0] ), 'dollie' ),
				'type'    => 'undeploy',
				'link'    => false,
			],
			self::WP_SITE_DELETED               => [
				'title'   => __( 'Site deleted', 'dollie' ),
				'content' => __( sprintf( 'You have deleted the site %s.', $values[0] ), 'dollie' ),
				'type'    => 'undeploy',
				'link'    => false,
			],
			self::WP_SITE_STARTED               => [
				'title'   => __( 'Site reactivated', 'dollie' ),
				'content' => __( sprintf( 'Site %s has been started.', $values[0] ), 'dollie' ),
				'type'    => 'deploy',
				'link'    => true,
			],
			self::WP_SITE_STOPPED               => [
				'title'   => __( 'Site stopped', 'dollie' ),
				'content' => __( sprintf( 'Site %s has been stopped.', $values[0] ), 'dollie' ),
				'type'    => 'undeploy',
				'link'    => false,
			],
			self::WP_SITE_DOMAIN_LINKED         => [
				'title'   => __( 'Domain linked', 'dollie' ),
				'content' => __( sprintf( 'Domain %s has been linked for %s.', $values[0], $values[1] ), 'dollie' ),
				'type'    => 'setup',
				'link'    => false,
			],
			self::WP_SITE_DOMAIN_ADDED          => [
				'title'   => __( 'Domain setup completed', 'dollie' ),
				'content' => __( sprintf( 'Using live real domain from this point onwards for %s.', $values[0] ), 'dollie' ),
				'type'    => 'setup',
				'link'    => true,
			],
			self::WP_SITE_DOMAIN_LINK_ERROR     => [
				'title'   => __( 'Domain setup error', 'dollie' ),
				'content' => __( sprintf( 'Domain %s could not be linked for %s.', $values[0], $values[1] ), 'dollie' ),
				'type'    => 'setup',
				'link'    => false,
			],
			self::WP_SITE_CLOUDFLARE_LINKED     => [
				'title'   => __( 'Cloudflare linked', 'dollie' ),
				'content' => __( sprintf( 'CloudFlare account was linked for %s.', $values[0] ), 'dollie' ),
				'type'    => 'setup',
				'link'    => false,
			],
			self::WP_SITE_CLOUDFLARE_ZONE_ADDED => [
				'title'   => __( 'Cloudflare Zone ID added', 'dollie' ),
				'content' => __( sprintf( 'CloudFlare Zone ID %s is used for analytics for %s.', $values[0], $values[1] ), 'dollie' ),
				'type'    => 'setup',
				'link'    => false,
			],
			self::WP_SITE_LETSENCRYPT_FAILED    => [
				'title'   => __( 'Letsencrypt failed', 'dollie' ),
				'content' => __( sprintf( 'Letsencrypt ssl wasn\'t generated for domain %s', $values[0] ), 'dollie' ),
				'type'    => 'setup',
			],
			self::WP_SITE_BLUEPRINT_DEPLOYED    => [
				'title'   => __( 'Blueprint updated', 'dollie' ),
				'content' => __( sprintf( 'A new Blueprint has be updated/deployed for %s.', $values[0] ), 'dollie' ),
				'type'    => 'blueprint',
				'link'    => true,
			],

		];

		if ( isset( $actions[ $action ] ) ) {
			return $actions[ $action ];
		}

		return [];
	}

}
