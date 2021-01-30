<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;

/**
 * Class ContainerFields
 *
 * @package Dollie\Core\Modules
 */
class ContainerFields extends Singleton {

	/**
	 * ContainerFields constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_filter( 'add_meta_boxes', [ $this, 'hide_meta_boxes_container' ] );
		add_filter( 'acf/input/meta_box_priority', [ $this, 'set_acf_metabox_priority' ], 10, 2 );
		add_filter( 'manage_container_posts_columns', [ $this, 'add_acf_columns' ] );
		add_action( 'manage_container_posts_custom_column', [ $this, 'custom_column' ], 10, 2 );
		add_filter( 'acf/update_value/name=wpd_container_status', [ $this, 'check_container_status' ], 10, 3 );
	}

	/**
	 * Hide meta boxes
	 */
	public function hide_meta_boxes_container() {
		remove_meta_box( 'postcustom', 'container', 'normal' );
	}

	/**
	 * Set acf meta box priority
	 *
	 * @param $priority
	 * @param $field_group
	 *
	 * @return string
	 */
	public function set_acf_metabox_priority( $priority, $field_group ) {
		if ( 'Blueprints' === $field_group['title'] ) {
			$priority = 'high';
		}

		return $priority;
	}

	/**
	 * Add acf columns
	 *
	 * @param $columns
	 *
	 * @return array
	 */
	public function add_acf_columns( $columns ) {
		return array_merge(
			$columns,
			[
				'domain'       => __( 'Domain', 'dollie' ),
				'users'        => __( 'Users', 'dollie' ),
				'size'         => __( 'Size', 'dollie' ),
				'status'       => __('Status', 'dollie'),
			]
		);
	}

	/**
	 * Add custom column
	 *
	 * @param $column
	 * @param $post_id
	 */
	public function custom_column( $column, $post_id ) {
		$search_meta = '';

		switch ( $column ) {
			case 'domain':
				$search_meta = 'wpd_domains';
				break;
			case 'users':
				$search_meta = 'wpd_installation_users';
				break;
			case 'size':
				$search_meta = 'wpd_installation_size';
				break;
			case 'status':
				$search_meta = 'wpd_container_status';
				break;
		}

		if ( $search_meta ) {
			$meta = get_post_meta($post_id, $search_meta, true);
				if ( $meta == 'stop' ) {
					echo '<mark class="site-status status-stop">' . get_post_meta($post_id, $search_meta, true). '</mark>';
				} elseif ($meta == 'start') {
					echo '<mark class="site-status status-start">' . get_post_meta($post_id, $search_meta, true) . '</mark>';
				} else {
					echo get_post_meta($post_id, $search_meta, true);
				}
		}
	}

	/**
	 * Change container status
	 *
	 * @param $value
	 * @param $post_id
	 * @param $field
	 *
	 * @return mixed
	 */
	public function check_container_status( $value, $post_id, $field ) {
		if ( Container::instance()->get_status() !== $value ) {
			Container::instance()->trigger( $value, $post_id );
		}

		return $value;
	}

}
