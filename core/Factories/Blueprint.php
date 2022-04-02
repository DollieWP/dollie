<?php

namespace Dollie\Core\Factories;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use WP_Post;
use Dollie\Core\Api\BlueprintApi;

final class Blueprint extends BaseContainer {
	use BlueprintApi;

	/**
	 * Blueprint constructor
	 *
	 * @param WP_Post $post
	 */
	public function __construct( WP_Post $post ) {
		parent::__construct( $post );
	}

	/**
	 * Refresh blueprint details
	 *
	 * @return self
	 */
	public function fetch_details(): self {
		$data = $this->get_blueprint_by_id( $this->get_hash() );

		if ( is_array( $data ) && isset( $data[0] ) ) {
			$this->update_meta( $data[0] );
		}

		return $this;
	}

	/**
	 * Perform action
	 *
	 * @param string $action
	 *
	 * @return boolean|array
	 */
	public function perform_action( string $action ): bool|array {
		$action = $this->perform_blueprint_action( $this->get_hash(), $action );

		$this->after_status_change_event();

		return $action;
	}

	/**
	 * Update changes
	 *
	 * @param string $title
	 * @param string $description
	 *
	 * @return boolean|array
	 */
	public function update_changes( string $title, string $description ): bool|array {
		update_post_meta( $this->post->ID, 'dollie_blueprint_title', $title );
		update_post_meta( $this->post->ID, 'dollie_blueprint_description', $description );
		update_post_meta( $this->post->ID, 'dollie_blueprint_update_time', current_time( 'mysql' ) );

		return $this->update_blueprint( $this->get_hash() );
	}

	/**
	 * Check if it is private
	 *
	 * @return boolean
	 */
	public function is_private(): bool {
		return 'yes' === get_field( 'wpd_private_blueprint', $this->get_id() );
	}

	/**
	 * Undeploy
	 *
	 * @return boolean|array
	 */
	public function undeploy(): bool|array {
		return $this->delete_blueprint( $this->get_hash() );
	}

	/**
	 * Get title
	 *
	 * @return boolean|string
	 */
	public function get_saved_title(): bool|string {
		return get_post_meta( $this->post->ID, 'dollie_blueprint_title', true );
	}

	/**
	 * Get description
	 *
	 * @return boolean|string
	 */
	public function get_saved_description(): bool|string {
		return get_post_meta( $this->post->ID, 'dollie_blueprint_description', true );
	}

	/**
	 * Get save time
	 *
	 * @return boolean|string
	 */
	public function get_changes_update_time(): bool|string {
		return get_post_time( $this->post->ID, 'dollie_blueprint_update_time', true );
	}

	/**
	 * Check dynamic fields
	 *
	 * @return boolean|array
	 */
	public function check_dynamic_fields(): bool|array {
		$fields = [];

		foreach ( $this->get_dynamic_fields() as $field ) {
			$fields[] = $field['placeholder'];
		}

		return $this->check_blueprint_dynamic_fields( $this->get_hash(), $fields );
	}

	/**
	 * Get dynamic fields
	 *
	 * @return array
	 */
	public function get_dynamic_fields(): array {
		$fields = get_field( 'wpd_dynamic_blueprint_data', 'create_update_blueprint_' . $this->get_id() );

		if ( ! is_array( $fields ) ) {
			return [];
		}

		return $fields;
	}

	public function get_screenshot(): bool|string {
		if ( get_field( 'wpd_blueprint_image', $this->get_id() ) === 'custom' ) {
			$image = get_field( 'wpd_blueprint_custom_image', $this->get_id() );

			if ( is_array( $image ) ) {
				return $image['url'];
			}
		}

		return parent::get_screenshot();
	}
}