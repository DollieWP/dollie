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
	public function notice() {
		if ( ! is_singular( 'container' ) ) {
			return;
		}

		$container = dollie()->get_container();

		if ( is_wp_error( $container ) || ! $container->is_blueprint() || $container->is_deploying() ) {
			return;
		}

		$updated_time = $container->get_changes_update_time();

		if ( ! $updated_time ) {
			dollie()->load_template( 'notices/blueprint-staging', [ 'container' => $container ], true );

			return;
		}

		dollie()->load_template( 'notices/blueprint-live', [ 'container' => $container ], true );
	}

	/**
	 * Change site title
	 *
	 * @param string $title
	 * @return string
	 */
	public function change_site_title_to_blueprint_title( $title ) {
		global $post;

		if ( ! $post ) {
			return $title;
		}

		$container = dollie()->get_container( $post->ID );

		if ( is_wp_error( $container ) || ! $container->is_blueprint() ) {
			return $title;
		}

		return $container->get_saved_title();
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

		$fields = $container->get_dynamic_fields();

		$fields = array_filter(
			$fields,
			function ( $v, $k ) {
				return ! empty( $v['placeholder'] );
			},
			ARRAY_FILTER_USE_BOTH
		);

		if ( empty( $fields ) ) {
			return;
		}

		$customizer = dollie()->load_template( 'notices/dynamic-fields', [ 'fields' => $fields ] );

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
	 *
	 * @return array
	 */
	public function get_dynamic_fields( $deploy_data ): array {
		if ( ! isset( $_POST['wpd_bp_data'] ) || ! is_array( $_POST['wpd_bp_data'] ) ) {
			return $deploy_data;
		}

		if ( ! is_array( $deploy_data ) || ! isset( $deploy_data['blueprint_id'] ) || false === $deploy_data['blueprint_id'] ) {
			return $deploy_data;
		}

		$customizer = [];
		$container  = dollie()->get_container( $deploy_data['blueprint_id'] );

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
			function ( $value ) {
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
	 * @return array
	 */
	public function get(): array {
		$data = [];

		$posts = get_posts(
			[
				'post_type'      => 'container',
				'posts_per_page' => - 1,
				'meta_query'     => [
					'relation' => 'AND',
					[
						'key'   => 'dollie_container_type',
						'value' => '1',
					],
				],
			]
		);

		if ( empty( $posts ) ) {
			return $data;
		}

		foreach ( $posts as $post ) {
			$container = dollie()->get_container( $post );
			$skip_this = apply_filters( 'dollie/blueprints/skip_display_list', false, $container );

			if ( is_wp_error( $container ) ||
				! $container->is_blueprint() ||
				$container->is_failed() ||
				$container->is_deploying() ) {
				continue;
			}

			if ( ! current_user_can( 'manage_options' ) &&
				( $skip_this ||
				! $container->is_running() ||
				! $container->is_updated() ||
				! $container->get_saved_title() ||
				$container->is_private() ||
				$container->is_scheduled_for_deletion() )
			) {
				continue;
			}

			$data[ $container->get_id() ] = $container;
		}

		return apply_filters( 'dollie/blueprints', $data );
	}
}
