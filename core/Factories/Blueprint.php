<?php

namespace Dollie\Core\Factories;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Log;
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
		if ( ! $this->needs_updated() ) {
			return $this;
		}

		$data = $this->get_blueprint_by_id( $this->get_hash() );

		if ( is_array( $data ) && isset( $data[0] ) ) {
			$this->set_details( $data[0] );
			$this->sync_settings( $data[0] );
			$this->mark_updated();
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
	 * Get Additional Blueprint screenshot info
	 *
	 * @param string $location
	 *
	 * @return string
	 */
	public function set_screenshot_data() {
		$theme_url = $this->get_details( 'site.theme.screenshot' );

		if ( is_wp_error( $theme_url ) ) {
			return '';
		}

		update_post_meta( $this->get_id(), 'wpd_blueprint_active_theme_screenshot_url', $theme_url );
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
	public function update_remote_changes() {

		$this->update_snapshot_time();

		return $this->update_blueprint(
			$this->get_hash(),
			[
				'snapshot' => true,
				'fields'   => $this->get_dynamic_fields()
			]
		);
	}

	public function update_snapshot_time( $time = null ) {
		update_post_meta( $this->get_id(), 'wpd_blueprint_created', 'yes' );
		update_post_meta( $this->get_id(), 'wpd_blueprint_time', $time ?? current_time( 'mysql' ) );
	}

	public function update_remote_settings() {
		return $this->update_blueprint(
			$this->get_hash(),
			[
				'snapshot' => false,
				'settings' => [
					'title'       => $this->get_saved_title(),
					'description' => $this->get_saved_description(),
					'private'     => $this->is_private(),
				]
			]
		);
	}

	/**
	 * If remote setting is available then update locally, else get local data and return for remote update.
	 *
	 * @param $fetched_container
	 *
	 * @return array
	 */
	public function sync_settings( $fetched_container ) {

		$container_id = $this->get_id();

		$data = [
			'fields'   => [],
			'settings' => [],
		];

		if ( empty( $fetched_container['blueprintSetting'] ) ) {
			return [
				'fields'   => $this->get_dynamic_fields(),
				'settings' => [
					'title'       => $this->get_saved_title(),
					'description' => $this->get_saved_description(),
					'private'     => $this->is_private(),
				]
			];
		}

		if ( ! empty( $fetched_container['blueprintSetting']['customizer'] ) ) {
			update_field( 'wpd_dynamic_blueprint_data', $fetched_container['blueprintSetting']['customizer'], 'create_update_blueprint_' . $container_id );
		} else {
			$data['fields'] = $this->get_dynamic_fields();
		}

		if ( ! empty( $fetched_container['blueprintSetting']['title'] ) ) {
			update_post_meta( $container_id, 'wpd_installation_blueprint_title', $fetched_container['blueprintSetting']['title'] );
		} else {
			$data['settings']['title'] = $this->get_saved_title();
		}

		if ( ! empty( $fetched_container['blueprintSetting']['description'] ) ) {
			update_post_meta( $container_id, 'wpd_installation_blueprint_description', $fetched_container['blueprintSetting']['description'] );
		} else {
			$data['settings']['description'] = $this->get_saved_description();
		}

		if ( ! empty( $fetched_container['blueprintSetting']['private'] ) ) {
			update_post_meta( $container_id, 'wpd_private_blueprint', $fetched_container['blueprintSetting']['private'] ? 'yes' : '' );
		} else {
			$data['settings']['private'] = $this->is_private();
		}

		if ( ! empty( $fetched_container['blueprintSetting']['history'] ) ) {
			$this->update_snapshot_time( end( $fetched_container['blueprintSetting']['history'] ) );
		}

		if ( ! empty( $fetched_container['blueprintSetting']['roles'] ) ) {
			update_post_meta( $container_id, 'wpd_blueprint_roles', $fetched_container['blueprintSetting']['roles'] );
		}
		if ( ! empty( $fetched_container['blueprintSetting']['client_role'] ) ) {
			update_post_meta( $container_id, 'wpd_blueprint_client_role', $fetched_container['blueprintSetting']['client_role'] );
		}

		return $data;

	}

	/**
	 * Check if it is updated
	 *
	 * @return boolean
	 */
	public function is_updated() {
		return get_post_meta( $this->get_id(), 'wpd_blueprint_created', true ) && $this->get_changes_update_time();
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
	 * Check if it is private
	 *
	 * @return boolean
	 */
	public function is_vip(): bool {
		return true === get_field( 'show_blueprint_to_vip', $this->get_id() );
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

		// Add log.
		$this->add_log( Log::WP_BLUEPRINT_DELETED );

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

		// Add log.
		$this->add_log( Log::WP_BLUEPRINT_RESTORE_STARTED );

		return true;
	}

	/**
	 * Get title
	 *
	 * @return boolean|string
	 */
	public function get_saved_title() {
		return get_post_meta( $this->get_id(), 'wpd_installation_blueprint_title', true );
	}

	/**
	 * Get description
	 *
	 * @return boolean|string
	 */
	public function get_saved_description() {
		return get_post_meta( $this->get_id(), 'wpd_installation_blueprint_description', true );
	}

	/**
	 * Get save time
	 *
	 * @return boolean|string
	 */
	public function get_changes_update_time() {
		return get_post_meta( $this->get_id(), 'wpd_blueprint_time', true );
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
