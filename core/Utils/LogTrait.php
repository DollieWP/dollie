<?php

namespace Dollie\Core\Utils;

use Dollie\Core\Log;
use Dollie\Core\Services\NotificationService;

trait LogTrait {

	abstract public function get_id();

	/**
	 * Log message
	 *
	 * @param string $action
	 * @param array $values
	 * @param string $extra_content
	 *
	 * @return false|int|\WP_Error
	 */
	public function add_log( string $action, array $values = [], string $extra_content = '' ) {

		$object = get_post( $this->get_id() );

		// If no custom values are set use the default one - post_name
		if ( empty( $values ) ) {
			$values = [ $object->post_name ];
		}

		if ( ! is_array( $values ) ) {
			$values = [ $values ];
		}

		$content = Log::get_content( $action, $values );

		if ( '' !== $extra_content ) {
			$content['content'] .= '<br class="extra-content">' . $extra_content;
		}

		// create the log entry.
		$log_id = Log::add( $content['title'], $content['content'], $content['type'] );

		// continue to add the user front-end log data.
		if ( ! $log_id || empty( $object ) || ! is_object( $object ) || ! isset( $object->ID ) ) {
			return false;
		}

		// Set status to unread and save site ID
		update_post_meta( $log_id, '_wpd_read', 0 );
		update_post_meta( $log_id, '_wpd_site_id', $object->ID );
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
			'post_author' => $object->post_author,
		];

		wp_update_post( $args );

		// Add email notification based on type.
		if ( $action === Log::WP_SITE_DEPLOYED ) {
			NotificationService::instance()->deployed_email( $object );
		}

		// Slack notification.
		NotificationService::instance()->all_slack( $action, $content, $object );

		return $log_id;
	}
}
