<?php

namespace Dollie\Core\Elementor;

use Elementor\TemplateLibrary\Source_Base;
use Elementor\Plugin;

defined( 'ABSPATH' ) || die();

/**
 * Elementor template library remote source.
 *
 * Elementor template library remote source handler class is responsible for
 * handling remote templates from Elementor.com servers.
 *
 * @since 1.0.0
 */
class DollieSource extends Source_Base {

	/**
	 * Elementor template-library post-type slug.
	 */
	const CPT = 'page';

	private static $api_url = 'https://manager.getdollie.com/releases/packages/elementor/%s.json';


	public function get_id() {
		return 'dollie';
	}

	public function get_title() {
		return __( 'Dollie', 'dollie' );
	}

	public function register_data() {
	}

	public function get_items( $args = [] ) {
		return [];
	}

	public function get_item( $template_id ) {
		return [];
	}

	public function save_item( $template_data ) {
		return false;
	}

	public function delete_template( $template_id ) {
		return false;
	}

	public function export_template( $template_id ) {
		return false;
	}

	public function get_data( array $args, $context = 'display' ) {
		$data = self::get_template_content( $args['template_id'] );

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$data['content'] = $this->replace_elements_ids( $data['content'] );
		$data['content'] = $this->process_export_import_content( $data['content'], 'on_import' );

		if ( isset( $_POST['editor_post_id'] ) ) {
			$post_id  = (int) $_POST['editor_post_id']; // phpcs:ignore
			if ( $post_id > 0 ) {
				$document = Plugin::$instance->documents->get( $post_id );
				if ( $document ) {
					$data['content'] = $document->get_elements_raw_data( $data['content'], true );
				}
			}

		}

		return $data;
	}

	/**
	 * Fetch template content from server.
	 *
	 * @param array $template_id Template ID.
	 *
	 * @return \WP_Error|array
	 * @since 1.0.0
	 * @access public
	 *
	 */
	public static function get_template_content( $template_id ) {
		$url = sprintf( self::$api_url, $template_id );

		$response = wp_remote_get( $url, [
			'timeout' => 15,
		] );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response_code = (int) wp_remote_retrieve_response_code( $response );

		if ( 200 !== $response_code ) {
			return new \WP_Error( 'response_code_error', sprintf( 'The request returned with a status code of %s.', $response_code ) );
		}

		$template_content = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $template_content['error'] ) ) {
			return new \WP_Error( 'response_error', $template_content['error'] );
		}

		if ( empty( $template_content['content'] ) ) {
			return new \WP_Error( 'template_data_error', 'An invalid data was returned.' );
		}

		return $template_content;
	}

	/**
	 * Update  template.
	 *
	 * Update template on the database.
	 *
	 * @param array $new_data New template data.
	 *
	 * @return \WP_Error|true True if template updated, `WP_Error` otherwise.
	 * @since 1.0.0
	 * @access public
	 *
	 */
	public function update_item( $new_data ) {

		if ( ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error( 'save_error', __( 'Access denied.', 'elementor' ) );
		}

		$document = Plugin::$instance->documents->get( $new_data['id'], false );

		if ( ! $document ) {
			return new \WP_Error( 'save_error', __( 'Template not exist.', 'elementor' ) );
		}

		$array = [
			'elements' => $new_data['content'],
		];

		if ( isset( $new_data['page_settings'] ) ) {
			$array['settings'] = $new_data['page_settings'];
		}

		$document->save( $array );


		return true;
	}

}
