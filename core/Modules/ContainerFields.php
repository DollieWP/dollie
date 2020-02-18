<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Helpers;

/**
 * Class ContainerFields
 * @package Dollie\Core\Modules
 */
class ContainerFields extends Singleton {

	/**
	 * @var mixed
	 */
	private $helpers;

	/**
	 * ContainerFields constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->helpers = Helpers::instance();

		add_action( 'cmb2_save_post_fields', [ $this, 'stop_container_action' ], 10, 3 );
		add_action( 'cmb2_save_post_fields', [ $this, 'run_container_support_action' ], 10, 3 );

		add_filter( 'add_meta_boxes', [ $this, 'hide_meta_boxes_container' ] );
		add_filter( 'acf/input/meta_box_priority', [ $this, 'km_set_acf_metabox_priority' ], 10, 2 );
		add_filter( 'manage_container_posts_columns', [ $this, 'add_acf_columns' ] );
		add_action( 'manage_container_posts_custom_column', [ $this, 'custom_column' ], 10, 2 );
		add_filter( 'acf/update_value/name=wpd_container_status', [ $this, 'my_check_for_change' ], 10, 3 );
	}

	public function stop_container_action( $post_id, $updated, $cmb ) {
		if ( did_action( 'cmb2_save_post_fields' ) === 1 ) {
			if ( 'restart' === get_post_meta( $post_id, 'wpd_container_status', 1 ) ) {
				wpd_container_action( 'restart', $post_id );
				update_post_meta( $post_id, 'wpd_container_status', 'start' );
			}
			if ( 'stop' === get_post_meta( $post_id, 'wpd_container_status', 1 ) ) {
				wpd_container_action( 'stop', $post_id );
			}
			if ( 'start' === get_post_meta( $post_id, 'wpd_container_status', 1 ) ) {
				wpd_container_action( 'start', $post_id );
			}
		}
	}

	public function run_container_support_action( $post_id, $updated, $cmb ) {
		if ( did_action( 'cmb2_save_post_fields' ) === 1 ) {

			// hardcoded jobs?
			if ( 'reset-permissions' === get_post_meta( $post_id, 'wpd_container_support', 1 ) ) {
				wpd_start_rundeck_job( '7a95dfb4-fbbc-49bc-a5cb-509d2cff72de' );
			}
			if ( 'stop' === get_post_meta( $post_id, 'wpd_container_status', 1 ) ) {
				wpd_start_rundeck_job( '7a95dfb4-fbbc-49bc-a5cb-509d2cff72de' );
			}
			if ( 'start' === get_post_meta( $post_id, 'wpd_container_status', 1 ) ) {
				wpd_start_rundeck_job( '7a95dfb4-fbbc-49bc-a5cb-509d2cff72de' );
			}
			update_post_meta( $post_id, 'wpd_container_support', '' );
		}
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
				'status'       => __( 'Status', DOLLIE_SLUG ),
				'active_theme' => __( 'Theme', DOLLIE_SLUG ),
				'domain'       => __( 'Domain', DOLLIE_SLUG ),
				'blueprint'    => __( 'Blueprint', DOLLIE_SLUG ),
				'users'        => __( 'Users', DOLLIE_SLUG ),
				'size'         => __( 'Size', DOLLIE_SLUG ),
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
			wpd_container_action( $value, $post_id );
		}

		return $value;
	}

}
