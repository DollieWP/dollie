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
	public function get_container_backup( string $container_hash ): \WP_Error|array {
		return $this->get_request( "backups/{$container_hash}" );
	}

	/**
	 * Create
	 *
	 * @param string $container_hash
	 *
	 * @return \WP_Error|array
	 */
	public function create_container_backup( string $container_hash ): \WP_Error|array {
		return $this->post_request( "backups/{$container_hash}" );
	}

	/**
	 * Restore
	 *
	 * @param string $container_hash
	 * @param string $backup
	 * @param string $type
	 *
	 * @return \WP_Error|array
	 */
	public function restore_container_backup( string $container_hash, string $backup, string $type ): \WP_Error|array {
		return $this->post_request(
			"backups/{$container_hash}/restore",
			[
				'backup' => $backup,
				'type'   => $type,
			]
		);
	}
}
