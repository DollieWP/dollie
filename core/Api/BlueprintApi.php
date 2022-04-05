<?php

namespace Dollie\Core\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

trait BlueprintApi {
	use Api;

	/**
	 * Get blueprint by ID
	 *
	 * @param string $container_hash
	 *
	 * @return \WP_Error|array
	 */
	public function get_blueprint_by_id( string $container_hash ) {
		return $this->get_request( "blueprints/{$container_hash}" );
	}

	/**
	 * Get all blueprints
	 *
	 * @return \WP_Error|array
	 */
	public function get_blueprints() {
		return $this->get_request( 'blueprints' );
	}

	/**
	 * Update data
	 *
	 * @param string $container_hash
	 * @param array  $data
	 *
	 * @return \WP_Error|array
	 */
	public function update_blueprint( string $container_hash ) {
		return $this->get_request( "blueprints/{$container_hash}/update" );
	}

	/**
	 * Perform action
	 *
	 * @param string $container_hash
	 * @param string $action
	 *
	 * @return \WP_Error|array
	 */
	public function perform_blueprint_action( string $container_hash, string $action ) {
		return $this->post_request( "blueprints/{$container_hash}/action", [ 'action' => $action ] );
	}

	/**
	 * Check dynamic fields
	 *
	 * @param string $container_hash
	 * @param array  $fields
	 *
	 * @return \WP_Error|array
	 */
	public function check_blueprint_dynamic_fields( string $container_hash, array $fields ) {
		return $this->post_request( "blueprints/{$container_hash}/domain", [ 'fields' => $fields ] );
	}

	/**
	 * Delete
	 *
	 * @param string $container_hash
	 *
	 * @return \WP_Error|array
	 */
	public function delete_blueprint( string $container_hash ) {
		return $this->delete_request( "blueprints/{$container_hash}" );
	}
}
