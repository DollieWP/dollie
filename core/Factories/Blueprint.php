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
			$this->set_details( $data[0] );
		}

		return $this;
	}

	/**
	 * Get login URL
	 *
	 * @param string $location
	 *
	 * @return string
	 */
	public function get_login_url( string $location = '' ): string {
		$location = $location ? "&location={$location}" : '';
		$username = $this->get_details( 'site.admin.username' );

		if ( is_wp_error( $username ) ) {
			return '';
		}

		$login_data = $this->get_blueprint_login_url( $this->get_hash(), $username );

		if ( is_wp_error( $login_data ) || ! isset( $login_data['token'] ) || ! $login_data['token'] ) {
			return '';
		}

		return "https://{$this->get_url()}/wp-login.php?s5token={$login_data['token']}{$location}";
	}

	/**
	 * Perform action
	 *
	 * @param string $action
	 *
	 * @return boolean|array
	 */
	public function perform_action( string $action ) {
		return $this->perform_blueprint_action( $this->get_hash(), $action );
	}

	/**
	 * Update changes
	 *
	 * @return boolean|array
	 */
	public function update_changes() {
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
	 * Delete
	 *
	 * @return boolean
	 */
	public function delete(): bool {
		$status = $this->delete_blueprint( $this->get_hash() );

		if ( is_wp_error( $status ) ) {
			return false;
		}

		$this->set_details( $status );

		parent::delete();

		return true;
	}

	/**
	 * Restore
	 *
	 * @return bool
	 */
	public function restore(): bool {
		$status = $this->restore_blueprint( $this->get_hash() );

		if ( is_wp_error( $status ) ) {
			return false;
		}

		$this->set_details( $status );

		parent::restore();

		return true;
	}

	/**
	 * Get title
	 *
	 * @return boolean|string
	 */
	public function get_saved_title() {
		return get_post_meta( $this->post->ID, 'wpd_installation_blueprint_title', true );
	}

	/**
	 * Get description
	 *
	 * @return boolean|string
	 */
	public function get_saved_description() {
		return get_post_meta( $this->post->ID, 'wpd_installation_blueprint_description', true );
	}

	/**
	 * Get save time
	 *
	 * @return boolean|string
	 */
	public function get_changes_update_time() {
		return get_post_time( $this->post->ID, 'dollie_blueprint_update_time', true );
	}

	/**
	 * Get available blueprints
	 *
	 * @return \WP_Error|array
	 */
	public function get_available_blueprints() {
		return [];
	}

	/**
	 * Check dynamic fields
	 *
	 * @return boolean|array
	 */
	public function check_dynamic_fields() {
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
}
