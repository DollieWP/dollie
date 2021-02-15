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
		add_filter( 'admin_body_class', [ $this, 'add_blueprint_admin_class' ] );
		add_action( 'manage_container_posts_custom_column', [ $this, 'custom_column' ], 10, 2 );
		add_filter( 'acf/update_value/name=wpd_container_status', [ $this, 'check_container_status' ], 10, 3 );
	}

	/**
	 * Adds one or more classes to the body tag in the dashboard.
	 *
	 * @link https://wordpress.stackexchange.com/a/154951/17187
	 * @param  String $classes Current body classes.
	 * @return String          Altered body classes.
	 */
	public function add_blueprint_admin_class( $classes ) {
		global $pagenow;

		if ( ( 'post.php' === $pagenow || 'container' === get_post_type() ) && isset( $_GET['post'] ) ) {
			$is_blueprint = get_post_meta( $_GET['post'], 'wpd_blueprint_time' );
			if ( $is_blueprint ) {
				$classes .= ' dollie-is-blueprint';
			}
		}

		if (isset($_GET['blueprint']) && !empty($_GET['blueprint'])) {
			$classes .= ' dollie-is-blueprint';
		}

		return $classes;
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
		if ( isset( $_GET['blueprint'] ) && ! empty( $_GET['blueprint'] ) ) {
			return array_merge(
				$columns,
				[
					'updated' => __( 'Blueprint Updated', 'dollie' ),
					'users'   => __( 'Users', 'dollie' ),
					'size'    => __( 'Size', 'dollie' ),
					'status'  => __( 'Status', 'dollie' ),
				],
			);
		} else {
			return array_merge(
				$columns,
				[
					'domain' => __( 'Domain', 'dollie' ),
					'users'  => __( 'Users', 'dollie' ),
					'size'   => __( 'Size', 'dollie' ),
					'status' => __( 'Status', 'dollie' ),
				]
			);

		}
	}

	/**
	 * Add custom column
	 *
	 * @param $column
	 * @param $post_id
	 */
	public function custom_column( $column, $post_id ) {
		$search_meta = '';

		if ( isset( $_GET['blueprint'] ) && ! empty( $_GET['blueprint'] ) ) {
			switch ( $column ) {
				case 'updated':
					$search_meta = 'wpd_blueprint_time';
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
		} else {
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
		}

		if ( $search_meta ) {
			$meta = get_post_meta( $post_id, $search_meta, true );
			if ( 'stop' === $meta ) {
				echo '<mark class="site-status status-stop">' . get_post_meta( $post_id, $search_meta, true ) . '</mark>';
			} elseif ( 'start' === $meta ) {
				echo '<mark class="site-status status-start">' . get_post_meta( $post_id, $search_meta, true ) . '</mark>';
			} else {
				echo get_post_meta( $post_id, $search_meta, true );
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
