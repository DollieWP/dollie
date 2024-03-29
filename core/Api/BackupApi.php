<?php

namespace Dollie\Core\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

trait BackupApi {
	use Api;

	/**
	 * Get
	 *
	 * @param string $container_hash
	 *
	 * @return \WP_Error|array
	 */
	public function get_container_backup( string $container_hash ) {
		return $this->get_request( "backups/{$container_hash}" );
	}

	/**
	 * Create
	 *
	 * @param string $container_hash
	 *
	 * @return \WP_Error|array
	 */
	public function create_container_backup( string $container_hash ) {
		return $this->post_request( "backups/{$container_hash}" );
	}

	/**
	 * Restore
	 *
	 * @param string $container_hash
	 * @param string $snapshotId
	 * @param string $type
	 *
	 * @return \WP_Error|array
	 */
	public function restore_container_backup( string $container_hash, string $snapshotId, string $type ) {
		return $this->post_request(
			"backups/{$container_hash}/restore",
			[
				'snapshotId' => $snapshotId,
				'type'       => $type,
			]
		);
	}
}
