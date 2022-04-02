<?php

namespace Dollie\Core\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;

final class BlueprintService extends Singleton {
	/**
	 * Set cookie
	 *
	 * @return void
	 */
	public function set_cookie(): void {
		if ( isset( $_GET[ DOLLIE_BLUEPRINTS_COOKIE_PARAM ] ) && (int) $_GET[ DOLLIE_BLUEPRINTS_COOKIE_PARAM ] > 0 ) {
			$cookie_id = sanitize_text_field( $_GET[ DOLLIE_BLUEPRINTS_COOKIE_PARAM ] );
		}

		if ( isset( $cookie_id ) ) {
			setcookie( DOLLIE_BLUEPRINTS_COOKIE, $cookie_id, time() + ( 86400 * 30 ), '/' );
		}
	}

	/**
	 * Blueprint notice
	 *
	 * @return void
	 */
	public function notice(): void {
		if ( ! is_singular( 'container' ) ) {
			return;
		}

		$container = dollie()->get_container();

		if ( is_wp_error( $container ) || ! $container->is_blueprint() || 'Deploying' !== $container->get_status() ) {
			return;
		}

		$updated_time = $container->get_changes_update_time();

		if ( ! $updated_time ) {
			dollie()->load_template( 'notices/blueprint-staging', [ 'container' => $container ] );
		} else {
			dollie()->load_template( 'notices/blueprint-live', [ 'container' => $container ] );
		}
	}

	/**
	 * Return dynamic fields AJAX
	 *
	 * @return void
	 */
	public function ajax_get_dynamic_fields(): void {
		$container = dollie()->get_container( (int) $_POST['blueprint'] );

		if ( is_wp_error( $container ) || ! $container->is_blueprint() ) {
			wp_send_json_error();
			exit;
		}

		$customizer = dollie()->load_template(
			'notice',
			[
				'icon'    => 'fas fa-exclamation-circle',
				'title'   => __( 'Realtime Customizer', 'dollie' ),
				'message' => dollie()->load_template( 'notices/dynamic-fields', [ 'fields' => $container->get_dynamic_fields() ] ),
			],
		);

		wp_send_json_success(
			[
				'fields' => $customizer,
			]
		);
		exit;
	}

	/**
	 * Get dynamic fields
	 *
	 * @param $deploy_data
	 * @param $domain
	 * @param $blueprint_id
	 *
	 * @return array
	 */
	public function get_dynamic_fields( $deploy_data, $domain, $blueprint_id ): array {
		if ( isset( $_POST['wpd_bp_data'] ) && is_array( $_POST['wpd_bp_data'] ) ) {
			$customizer = [];
			$container  = dollie()->get_container( $blueprint_id );

			if ( is_wp_error( $container ) ) {
				return $deploy_data;
			}

			$fields = $container->get_dynamic_fields();

			foreach ( $fields as $field ) {
				if ( ! empty( $field['placeholder'] ) && isset( $_POST['wpd_bp_data'][ $field['placeholder'] ] ) ) {
					$customizer[ $field['placeholder'] ] = $_POST['wpd_bp_data'][ $field['placeholder'] ];
				}
			}

			if ( ! empty( $customizer ) ) {
				$deploy_data['bp_customizer'] = $customizer;
			}
		}

		return $deploy_data;
	}

	/**
	 * Check dynamic fields AJAX
	 *
	 * @return void
	 */
	public function ajax_check_dynamic_fields(): void {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'check_dynamic_fields_nonce' ) ) {
			wp_send_json_error();
		}

		$container = dollie()->get_container( $_REQUEST['container'] );

		if ( is_wp_error( $container ) ) {
			wp_send_json_error();
		}

		$response = $container->check_dynamic_fields();

		if ( is_wp_error( $response ) ) {
			wp_send_json_error();
		}

		$missing_fields = array_filter(
			$response,
			function( $value ) {
				return false === $value;
			}
		);

		if ( empty( $missing_fields ) ) {
			$message = dollie()->load_template( 'notices/dynamic-fields-validated' );
		} else {
			$message = dollie()->load_template( 'notices/dynamic-fields-incomplete', [ 'fields' => $missing_fields ] );
		}

		wp_send_json_success( [ 'output' => $message ] );
	}

	/**
	 * Get all available
	 *
	 * @param string $type html|null
	 *
	 * @return array
	 */
	public function get( string $type = null ): array {
		$data = [];

		$posts = get_posts(
			[
				'post_type'      => 'container',
				'posts_per_page' => -1,
				'meta_query'     => [
					'relation' => 'AND',
					[
						'key'   => 'wpd_blueprint_created',
						'value' => 'yes',
					],
					[
						'key'   => 'wpd_is_blueprint',
						'value' => 'yes',
					],
					[
						'key'     => 'wpd_installation_blueprint_title',
						'compare' => 'EXISTS',
					],
				],
			]
		);

		if ( empty( $posts ) ) {
			return $data;
		}

		foreach ( $posts as $post ) {
			$container = dollie()->get_container( $post );

			if ( is_wp_error( $container ) || $container->is_private() ) {
				continue;
			}

			if ( 'html' === $type ) {
				$image = dollie()->load_template( 'parts/blueprint-image', [ 'container' => $container ] );
			} else {
				$image = $container->get_screenshot();
			}

			$data[ $container->get_id() ] = $image;
		}

		return apply_filters( 'dollie/blueprints', $data );
	}
}