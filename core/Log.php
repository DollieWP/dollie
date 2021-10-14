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
	const WP_BLUEPRINT_DEPLOY_STARTED   = 'wp-blueprint-deploy-start';
	const WP_BLUEPRINT_DEPLOYED         = 'wp-blueprint-deployed';
	const WP_BLUEPRINT_DEPLOY_FAILED    = 'wp-blueprint-deploy-failed';
	const WP_SITE_REMOVAL_SCHEDULED     = 'wp-site-removal-scheduled';
	const WP_SITE_STARTED               = 'wp-site-started';
	const WP_SITE_RESTARTED             = 'wp-site-restarted';
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
			if ( ! is_string( $message ) ) {
				$message = print_r( $message, true );
			}

			return \WDS_Log_Post::log_message( 'dollie-logs', $title, $message, $type, $log_post_id, $completed );
		}

		return false;
	}

	public static function update_message( $log_post_id, $full_message = '', $title = '' ) {
		return \WDS_Log_Post::update_message( $log_post_id, $full_message, $title );
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

		// Add email notification for some based on type.
		if ( $action === self::WP_SITE_DEPLOYED ) {

			if ( get_field( 'wpd_deployed_site_notification', 'options' ) ) {

				$headers = [ 'Content-Type: text/html; charset=UTF-8' ];

				// client email
				$client      = get_user_by( 'id', $object->author );
				$client_site = get_post( $object->id );
				$site_data   = get_post_meta( $object->id, '_wpd_setup_data', true );

				$to      = $client->user_email;
				$subject = get_field( 'wpd_deployed_site_client_notification_subject', 'options' );
				$message = get_field( 'wpd_deployed_site_client_notification_body', 'options' );
				$message = str_replace(
					[
						'{dollie_site_url}',
						'{dollie_site_name}',
						'{dollie_user}',
						'{dollie_site_email}',
						'{dollie_site_username}',
						'{dollie_site_password}',
					],
					[
						get_permalink( $object->id ),
						$client_site->post_name,
						$client->user_login,
						$site_data['email'],
						$site_data['username'],
						$site_data['password'],
					],
					$message
				);

				wp_mail( $to, $subject, $message, $headers );

				// admin email
				$to      = get_option( 'admin_email' );
				$subject = get_field( 'wpd_deployed_site_admin_notification_subject', 'options' );
				$message = get_field( 'wpd_deployed_site_admin_notification_body', 'options' );
				$message = str_replace(
					[
						'{dollie_site_url}',
						'{dollie_site_name}',
						'{dollie_user}',
						'{dollie_site_email}',
						'{dollie_site_username}',
						'{dollie_site_password}',
					],
					[
						get_permalink( $object->id ),
						$client_site->post_name,
						$client->user_login,
						$site_data['email'],
						$site_data['username'],
						$site_data['password'],
					],
					$message
				);

				wp_mail( $to, $subject, $message, $headers );
			}
		}

		if ( get_field( 'wpd_slack_notifications', 'options' ) && get_field( 'wpd_slack_webhook_url', 'options' ) ) {
			$slack_actions = get_field( 'wpd_slack_actions', 'options' );

			if ( in_array( $action, $slack_actions ) && class_exists( '\Maknz\Slack\Client' ) ) {
				$slack_client  = new \Maknz\Slack\Client( get_field( 'wpd_slack_webhook_url', 'options' ) );
				$slack_content = $content['title'] . '. ' . $content['content'];

				if ( $content['link'] === true ) {
					$slack_content .= ' <' . get_permalink( $object->id ) . '|View site>';
				}

				$slack_client->send( $slack_content );
			}
		}

		return $log_id;
	}

	/**
	 * @param string $action
	 * @param array  $values
	 *
	 * @return array
	 */
	public static function get_content( $action, $values = [], $log_id = null ) {

		if ( ! isset( $values[1] ) ) {
			$values[1] = '';
		}

		$actions = [
			self::WP_SITE_DEPLOY_STARTED        => [
				'title'   => __( 'Site Launch Started', 'dollie' ),
				'content' => __( sprintf( 'Launching Your New Site %s. You\'ll get another notification when it is ready! ', $values[0] ), 'dollie' ),
				'type'    => 'deploy',
				'link'    => true,
			],
			self::WP_SITE_DEPLOYED              => [
				'title'   => __( 'Site Launch Completed', 'dollie' ),
				'content' => __( sprintf( 'Site %s has been successfully launched.', $values[0] ), 'dollie' ),
				'type'    => 'deploy',
				'link'    => true,
			],
			self::WP_SITE_DEPLOY_FAILED         => [
				'title'   => __( 'Site Launch Failed', 'dollie' ),
				'content' => __( sprintf( 'Site %s has failed to launch. Please contact our support if the issue persists.', $values[0] ), 'dollie' ),
				'type'    => 'deploy',
				'link'    => false,
			],
			self::WP_SITE_SETUP_COMPLETED       => [
				'title'   => __( 'Site Setup Completed', 'dollie' ),
				'content' => __( sprintf( 'Site setup is completed for %s.', $values[0] ), 'dollie' ),
				'type'    => 'setup',
				'link'    => true,
			],
			self::WP_SITE_SETUP_FAILED          => [
				'title'   => __( 'Site Setup Failed', 'dollie' ),
				'content' => __( sprintf( 'Site setup has failed for site %s.', $values[0] ), 'dollie' ),
				'type'    => 'setup',
				'link'    => false,
			],
			self::WP_BLUEPRINT_DEPLOY_STARTED   => [
				'title'   => __( 'Blueprint Launch Started', 'dollie' ),
				'content' => __( sprintf( 'Launching Your New Blueprint %s. You\'ll get another notification when it is ready! ', $values[0] ), 'dollie' ),
				'type'    => 'deploy',
				'link'    => true,
			],
			self::WP_BLUEPRINT_DEPLOYED         => [
				'title'   => __( 'Blueprint Launch Completed', 'dollie' ),
				'content' => __( sprintf( 'Blueprint %s has been successfully launched.', $values[0] ), 'dollie' ),
				'type'    => 'deploy',
				'link'    => true,
			],
			self::WP_BLUEPRINT_DEPLOY_FAILED    => [
				'title'   => __( 'Blueprint Launch Failed', 'dollie' ),
				'content' => __( sprintf( 'Blueprint %s has failed to launch. Please contact our support if the issue persists.', $values[0] ), 'dollie' ),
				'type'    => 'deploy',
				'link'    => false,
			],
			self::WP_SITE_BACKUP_STARTED        => [
				'title'   => __( 'Backup Triggered', 'dollie' ),
				'content' => __( sprintf( 'A new backup has been triggered for %s.', $values[0] ), 'dollie' ),
				'type'    => 'action',
				'link'    => true,
			],
			self::WP_SITE_REMOVAL_SCHEDULED     => [
				'title'   => __( 'Site Removal Notice', 'dollie' ),
				'content' => __( sprintf( 'Site %s has been stopped and scheduled for complete removal.', $values[0] ), 'dollie' ),
				'type'    => 'undeploy',
				'link'    => false,
			],
			self::WP_SITE_UNDEPLOYED            => [
				'title'   => __( 'Site Removed', 'dollie' ),
				'content' => __( sprintf( 'Site %s was removed.', str_replace( '__trashed', '', $values[0] ) ), 'dollie' ),
				'type'    => 'undeploy',
				'link'    => false,
			],
			self::WP_SITE_DELETED               => [
				'title'   => __( 'Site Deleted', 'dollie' ),
				'content' => __( sprintf( 'You have deleted the site %s.', $values[0] ), 'dollie' ),
				'type'    => 'undeploy',
				'link'    => false,
			],
			self::WP_SITE_STARTED               => [
				'title'   => __( 'Site Reactivated', 'dollie' ),
				'content' => __( sprintf( 'Site %s has been started.', $values[0] ), 'dollie' ),
				'type'    => 'deploy',
				'link'    => true,
			],
			self::WP_SITE_RESTARTED             => [
				'title'   => __( 'Site Restarted', 'dollie' ),
				'content' => __( sprintf( 'Site %s has been successfully restarted.', $values[0] ), 'dollie' ),
				'type'    => 'deploy',
				'link'    => true,
			],
			self::WP_SITE_STOPPED               => [
				'title'   => __( 'Site Stopped', 'dollie' ),
				'content' => __( sprintf( 'Site %s has been stopped.', $values[0] ), 'dollie' ),
				'type'    => 'undeploy',
				'link'    => false,
			],
			self::WP_SITE_DOMAIN_LINKED         => [
				'title'   => __( 'Domain Added', 'dollie' ),
				'content' => __( sprintf( 'Domain %s has been added to %s. Please hold on until the DNS changes have taken effect', $values[0], $values[1] ), 'dollie' ),
				'type'    => 'setup',
				'link'    => false,
			],
			self::WP_SITE_DOMAIN_ADDED          => [
				'title'   => __( 'Domain is Connected', 'dollie' ),
				'content' => __( sprintf( 'Domain setup is complete for %s.', $values[0] ), 'dollie' ),
				'type'    => 'setup',
				'link'    => true,
			],
			self::WP_SITE_DOMAIN_LINK_ERROR     => [
				'title'   => __( 'Domain Setup Error', 'dollie' ),
				'content' => __( sprintf( 'Domain %s could not be linked to %s.', $values[0], $values[1] ), 'dollie' ),
				'type'    => 'setup',
				'link'    => false,
			],
			self::WP_SITE_CLOUDFLARE_LINKED     => [
				'title'   => __( 'Cloudflare Connected', 'dollie' ),
				'content' => __( sprintf( 'CloudFlare account was linked to %s.', $values[0] ), 'dollie' ),
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
				'title'   => __( 'LetsEncrypt Failed', 'dollie' ),
				'content' => __( sprintf( 'Letsencrypt SSL wasn\'t generated for domain %s', $values[0] ), 'dollie' ),
				'type'    => 'setup',
			],
			self::WP_SITE_BLUEPRINT_DEPLOYED    => [
				'title'   => __( 'Blueprint Updated', 'dollie' ),
				'content' => __( sprintf( 'A new Blueprint has ben updated/deployed for %s.', $values[0] ), 'dollie' ),
				'type'    => 'blueprint',
				'link'    => true,
			],

		];

		$actions = apply_filters( 'dollie/log/actions', $actions, $values, $log_id );

		if ( isset( $actions[ $action ] ) ) {

			$actions[ $action ]['content'] = apply_filters( 'dollie/log/actions/content', $actions[ $action ]['content'], $values, $log_id );

			if ( ! empty( $log_id ) ) {
				$log         = get_post( $log_id );
				$log_content = wpautop( $log->post_content );
				$log_content = explode( '------------------------------------------------', $log_content );

				if ( count( $log_content ) > 1 ) {
					unset( $log_content[0] );
					$log_content                    = implode( '------------------------------------------------', $log_content );
					$actions[ $action ]['content'] .= $log_content;
				}
			}

			return $actions[ $action ];
		}

		return [];
	}
}
