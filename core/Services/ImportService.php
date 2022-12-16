<?php

namespace Dollie\Core\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Utils\ConstInterface;
use Elementor\Plugin;

final class ImportService extends Singleton implements ConstInterface {

	private const API_URL = 'https://manager.getdollie.com/releases/packages/';

	/**
	 * Set old elementor templates as imported.
	 * @return void
	 */
	public function set_old_templates_as_imported(): void {
		$imported_templates = get_option( 'dollie_imported_templates', [] );

		if ( isset( $imported_templates['elementor'] ) ) {
			return;
		}

		$imported_ids   = [];
		$template_pages = [
			'wpd_launch_page_id',
			'wpd_launch_blueprint_page_id',
			'wpd_dashboard_page_id',
			'wpd_customers_page_id',
			'wpd_login_page_id',
			'wpd_sites_page_id',
			'wpd_site_template_id',
			'wpd_site_launching_template_id'
		];
		foreach ( $template_pages as $template_page ) {
			$page_id = get_option( 'options_' . $template_page );
			if ( $page_id ) {
				$imported_ids[ $template_page ] = $page_id;
			}
		}

		if ( ! empty( $imported_ids ) ) {
			$imported_templates['elementor'] = $imported_ids;
			update_option( 'dollie_imported_templates', $imported_templates );
		}
	}

	public function import_elementor_template(): array {

		$pages_data = [
			'launch-site'      => [
				'title'     => sprintf( esc_html__( 'Launch New %s', 'dollie-setup' ), dollie()->string_variants()->get_site_type_string() ),
				'option'    => 'options_wpd_launch_page_id',
				'tpl'       => 'elementor_header_footer',
				'post_type' => 'page',
			],
			'launch-blueprint' => [
				'title'     => 'Launch New Blueprint',
				'option'    => 'options_wpd_launch_blueprint_page_id',
				'tpl'       => 'elementor_header_footer',
				'post_type' => 'page',
			],
			'dashboard'        => [
				'title'     => 'Dashboard',
				'option'    => 'options_wpd_dashboard_page_id',
				'tpl'       => 'elementor_header_footer',
				'post_type' => 'page',
			],
			'customers'        => [
				'title'     => 'Customers',
				'option'    => 'options_wpd_customers_page_id',
				'tpl'       => 'elementor_header_footer',
				'post_type' => 'page',
			],
			'sites'            => [
				'title'     => 'Sites',
				'option'    => 'options_wpd_sites_page_id',
				'tpl'       => 'elementor_header_footer',
				'post_type' => 'page',
			],
			'customer-login'   => [
				'title'     => 'Customer Login',
				'option'    => 'options_wpd_login_page_id',
				'tpl'       => 'elementor_canvas',
				'post_type' => 'page',
			],
			'single'           => [
				'title'     => 'Site template',
				'option'    => 'options_wpd_site_template_id',
				'tpl'       => 'elementor_header_footer',
				'post_type' => 'elementor_library',
			],
		];

		return $this->import_pages( $pages_data, 'elementor' );
	}

	public function import_gutenberg_template(): array {

		$pages_data = [
			'launch-site'      => [
				'title'     => sprintf( esc_html__( 'Launch New %s', 'dollie-setup' ), dollie()->string_variants()->get_site_type_string() ),
				'option'    => 'options_wpd_launch_page_id',
				'tpl'       => 'wpd-builder',
				'post_type' => 'page',
			],
			'launch-blueprint' => [
				'title'     => esc_html__( 'Launch New Blueprint', 'dollie-setup' ),
				'option'    => 'options_wpd_launch_blueprint_page_id',
				'tpl'       => 'wpd-builder',
				'post_type' => 'page',
			],
			'dashboard'        => [
				'title'     => 'Dashboard',
				'option'    => 'options_wpd_dashboard_page_id',
				'tpl'       => 'wpd-builder',
				'post_type' => 'page',
			],
			'sites'            => [
				'title'     => 'Sites',
				'option'    => 'options_wpd_sites_page_id',
				'tpl'       => 'wpd-builder',
				'post_type' => 'page',
			],
			'customers'        => [
				'title'     => 'Customers',
				'option'    => 'options_wpd_customers_page_id',
				'tpl'       => 'wpd-builder',
				'post_type' => 'page',
			],
			'site-tpl'         => [
				'title'     => 'Site template',
				'option'    => 'options_wpd_site_template_id',
				'tpl'       => 'wpd-builder',
				'post_type' => 'page',
				'status' => 'private'
			],
		];

		return $this->import_pages( $pages_data, 'gutenberg' );

	}

	/**
	 * @param $pages_data
	 * @param string $builder
	 *
	 * @return array
	 */
	public function import_pages( array $pages_data, string $builder = 'elementor' ): array {

		$options = [];

		foreach ( $pages_data as $slug => $page_data ) {

			// trash page if exists.
			$existing_id = get_option( $page_data['option'] );

			if ( $existing_id && get_post( $existing_id ) && get_post_status( $existing_id ) !== 'trash' ) {
				wp_trash_post( $existing_id );
			}

			$post_data   = [
				'post_title'    => $page_data['title'],
				'post_content'  => '',
				'post_type'     => $page_data['post_type'],
				'post_status'   => $page_data['status'] ?? 'publish',
				'page_template' => $page_data['tpl'],
			];
			$existing_id = wp_insert_post( $post_data );

			if ( empty( $existing_id ) || is_wp_error( $existing_id ) ) {
				continue;
			}

			update_option( $page_data['option'], $existing_id );
			$options[ str_replace( 'options_', '', $page_data['option'] ) ] = $existing_id;
			update_post_meta( $existing_id, '_wp_page_template', $page_data['tpl'] );

			// Add Elementor content.
			if ( $builder === 'elementor' ) {
				$this->import_elementor_data( $existing_id, $slug, $page_data );
			} elseif ( $builder === 'gutenberg' ) {
				$this->import_gutenberg_data( $existing_id, $slug, $page_data );
			}

		}

		return $options;
	}

	/**
	 * Add elementor data to the page
	 *
	 * @param $existing_id
	 * @param $slug
	 * @param $page_data
	 *
	 * @return void
	 */
	private function import_elementor_data( $existing_id, $slug, $page_data ): void {
		$source = Plugin::instance()->templates_manager->get_source( 'dollie' );
		$args   = [
			'template_id' => $page_data['post_type'] . '-' . $slug,
		];

		$el_data = $source->get_data( $args );

		if ( ! is_wp_error( $el_data ) ) {

			if ( isset( $el_data['type'] ) ) {
				update_post_meta( $existing_id, '_elementor_template_type', $el_data['type'] );
			}

			update_post_meta( $existing_id, '_elementor_edit_mode', 'builder' );
			$el_data['id'] = $existing_id;
			$source->update_item( $el_data );
		}
	}


	/**
	 * Import Gutenberg data from remote json file.
	 *
	 * @param $existing_id
	 * @param $slug
	 * @param $page_data
	 *
	 * @return void
	 */
	private function import_gutenberg_data( $existing_id, $slug, $page_data ) {

		// get json content from url file location.
		$response     = wp_remote_get( self::API_URL . 'gutenberg/' . $page_data['post_type'] . '-' . $slug . '.json' );
		$responseBody = wp_remote_retrieve_body( $response );
		$result       = json_decode( $responseBody );

		if ( ! is_wp_error( $result ) ) {
			// update post content
			$post = [
				'ID'           => $existing_id,
				'post_content' => $result->content->raw,
			];
			wp_update_post( $post );
		}
	}
}
