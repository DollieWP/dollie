<?php

namespace Dollie\Core\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;

final class NotificationService extends Singleton {

	public function all_slack( $action, $content, $object ) {
		if ( get_field( 'wpd_slack_notifications', 'options' ) && get_field( 'wpd_slack_webhook_url', 'options' ) ) {
			$slack_actions = get_field( 'wpd_slack_actions', 'options' );

			if ( in_array( $action, $slack_actions ) && class_exists( '\Maknz\Slack\Client' ) ) {
				$slack_client  = new \Maknz\Slack\Client( get_field( 'wpd_slack_webhook_url', 'options' ) );
				$slack_content = $content['title'] . '. ' . $content['content'];

				if ( $content['link'] === true ) {
					$slack_content .= ' <' . get_permalink( $object->ID ) . '|View site>';
				}

				$slack_client->send( $slack_content );
			}
		}
	}

	public function deployed_email( $object ) {
		if ( get_field( 'wpd_deployed_site_notification', 'options' ) ) {

			$headers = [ 'Content-Type: text/html; charset=UTF-8' ];

			// client email.
			$client      = get_user_by( 'id', $object->post_author );
			$client_site = get_post( $object->ID );
			$site_data   = get_post_meta( $object->ID, '_wpd_setup_data', true );

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
					get_permalink( $object->ID ),
					$client_site->post_name,
					$client->user_login,
					$site_data['email'],
					$site_data['username'],
					$site_data['password'],
				],
				$message
			);

			wp_mail( $to, $subject, $message, $headers );

			// admin email.
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
					get_permalink( $object->ID ),
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

}
