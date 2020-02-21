<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;

/**
 * Class ContainerFields
 * @package Dollie\Core\Modules
 */
class ContainerFields extends Singleton {

	/**
	 * ContainerFields constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_filter( 'add_meta_boxes', [ $this, 'hide_meta_boxes_container' ] );
		add_filter( 'acf/input/meta_box_priority', [ $this, 'km_set_acf_metabox_priority' ], 10, 2 );
		add_filter( 'manage_container_posts_columns', [ $this, 'add_acf_columns' ] );
		add_action( 'manage_container_posts_custom_column', [ $this, 'custom_column' ], 10, 2 );
		add_filter( 'acf/update_value/name=wpd_container_status', [ $this, 'my_check_for_change' ], 10, 3 );
	}

	public function hide_meta_boxes_container() {
		remove_meta_box( 'postcustom', 'container', 'normal' );
	}

	public function km_set_acf_metabox_priority( $priority, $field_group ) {
		if ( 'Blueprints' === $field_group['title'] ) {
			$priority = 'high';
		}

		return $priority;
	}

	public function add_acf_columns( $columns ) {
		return array_merge(
			$columns,
			[
				'status'       => esc_html__( 'Status', 'dollie' ),
				'active_theme' => esc_html__( 'Theme', 'dollie' ),
				'domain'       => esc_html__( 'Domain', 'dollie' ),
				'blueprint'    => esc_html__( 'Blueprint', 'dollie' ),
				'users'        => esc_html__( 'Users', 'dollie' ),
				'size'         => esc_html__( 'Size', 'dollie' ),
			]
		);
	}

	public function custom_column( $column, $post_id ) {
		$search_meta = '';

		switch ( $column ) {
			case 'status':
				$search_meta = 'wpd_container_status';
				break;
			case 'active_theme':
				$search_meta = 'wpd_installation_themes';
				break;
			case 'domain':
				$search_meta = 'wpd_domains';
				break;
			case 'blueprint':
				$search_meta = 'wpd_is_blueprint';
				break;
			case 'users':
				$search_meta = 'wpd_installation_users';
				break;
			case 'size':
				$search_meta = 'wpd_installation_size';
				break;
		}

		if ( $search_meta ) {
			echo get_post_meta( $post_id, $search_meta, true );
		}
	}

	public function my_check_for_change( $value, $post_id, $field ) {
		$old_value = get_post_meta( $post_id, 'wpd_container_status', true );
		if ( $old_value !== $value ) {
			ContainerManagement::instance()->container_action( $value, $post_id );
		}

		return $value;
	}

}
