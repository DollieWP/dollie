<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;

/**
 * Class AccessControl
 *
 * @package Dollie\Core\Modules
 */
class AccessControl extends Singleton {

	/**
	 * Custom capabilities of custom post type
	 */
	private static $custom_caps = [
		'singular' => 'wpd_site',
		'plural'   => 'wpd_sites'
	];

	/**
	 * AccessControl constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'template_redirect', [ $this, 'logged_in_only' ] );
		add_action( 'template_redirect', [ $this, 'protect_launch_site' ] );
		add_action( 'template_redirect', [ $this, 'protect_container_access' ], 1 );
		add_action( 'template_redirect', [ $this, 'disable_blueprint_domain_access' ], 1 );
		//add_action( 'admin_init', [ $this, 'admin_access_only' ], 100 );
		add_action( 'user_has_cap', [ $this, 'restrict_form_delete' ], 10, 3 );

		add_filter( 'wp_dropdown_users_args', [ $this, 'allow_all_authors' ], 10, 2 );

		add_action( 'init', [ $this, 'sites_capabilities' ] );
		add_filter( 'acf/save_post', [ $this, 'acf_remove_cap_option' ], 10, 1 );

		add_filter( 'acf/load_field/name=wpd_view_sites_permission', [ $this, 'acf_set_roles' ] );
		add_filter( 'acf/load_field/name=manage_sites_permission', [ $this, 'acf_set_roles' ] );
		add_filter( 'acf/load_field/name=delete_sites_permission', [ $this, 'acf_set_roles' ] );

		add_filter( 'pre_get_posts', [ $this, 'sites_for_current_author' ] );

	}

	public function can_view_all_sites( $user_id = null ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		return user_can( $user_id, 'read_private_wpd_sites' );
	}

	public function can_manage_all_sites( $user_id = null ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		return user_can( $user_id, 'edit_others_wpd_sites' );
	}

	public function can_delete_all_sites( $user_id = null ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		return user_can( $user_id, 'delete_others__wpd_sites' );
	}

	public function sites_for_current_author( $query ) {
		if ( ! is_admin() ) {
			return $query;
		}

		global $pagenow;

		if ( 'edit.php' !== $pagenow || ! isset( $_GET['post_type'] ) || $_GET['post_type'] !== 'container' ) {
			return $query;
		}

		if ( ! $this->can_view_all_sites() ) {

			$query->set( 'author', get_current_user_id() );
		}

		return $query;
	}

	/**
	 * Remove option on ACF options save
	 *
	 * @param $post_id
	 */
	public function acf_remove_cap_option( $post_id ) {
		if ( 'options' !== $post_id ) {
			return;
		}

		delete_option( 'wpd_capabilities_added' );
	}

	/**
	 * Populate the roles for ACF option
	 *
	 * @param $field
	 *
	 * @return mixed
	 */
	public function acf_set_roles( $field ) {
		$field['choices'] = $this->get_roles();

		return $field;
	}

	/**
	 * Update capabilities for user roles
	 */
	public function sites_capabilities() {

		$option_name = 'wpd_capabilities_added';

		if ( get_option( $option_name ) ) {
			return;
		}

		$singular = self::$custom_caps['singular'];
		$plural   = self::$custom_caps['plural'];

		$view        = [
			"read_{$singular}",
		];
		$view_others = [
			"read_private_{$plural}",
		];

		$update        = [
			"edit_{$singular}",
			"edit_{$plural}",
			"publish_{$plural}",
			"edit_published_{$plural}",
		];
		$update_others = [
			"edit_others_{$plural}",
			"edit_private_{$plural}",
		];

		$delete = [
			"delete_{$singular}",
			"delete_{$plural}",
			"delete_published_{$plural}",
		];

		$delete_others = [
			"delete_private_{$plural}",
			"delete_others_{$plural}",
		];

		$all = array_merge( $view, $view_others, $update, $update_others, $delete, $delete_others );

		$default_mappings = [
			'subscriber'    => $view,
			'author'        => array_merge( $view, $update, $delete ),
			'editor'        => array_merge( $view, $update, $delete ),
			'customer'      => array_merge( $view, $update, $delete ),
			'administrator' => $all
		];

		// Get role settings.
		$settings_view   = get_field( 'wpd_view_sites_permission', 'options' );
		$settings_update = get_field( 'manage_sites_permission', 'options' );
		$settings_delete = get_field( 'delete_sites_permission', 'options' );

		if ( empty( $settings_view ) ) {
			$settings_view = [];
		}
		if ( empty( $settings_update ) ) {
			$settings_update = [];
		}
		if ( empty( $settings_delete ) ) {
			$settings_delete = [];
		}

		$roles = $this->get_roles();

		// Set the capabilities from Dollie settings.
		foreach ( $roles as $wp_role => $wp_role_name ) {

			// init role
			if ( ! isset( $default_mappings[ $wp_role ] ) ) {
				$default_mappings[ $wp_role ] = [];
			}

			// View
			if ( in_array( $wp_role, $settings_view, true ) ) {
				$default_mappings[ $wp_role ] = array_merge( $default_mappings[ $wp_role ], $view, $view_others );
			} elseif ( ! empty( $default_mappings[ $wp_role ] ) ) {

				//Keep any other capabilities
				$default_mappings[ $wp_role ] = array_diff( $default_mappings[ $wp_role ], $view_others );
			}

			// Update
			if ( in_array( $wp_role, $settings_update, true ) ) {

				$default_mappings[ $wp_role ] = array_merge( $default_mappings[ $wp_role ], $update, $update_others );
			} elseif ( ! empty( $default_mappings[ $wp_role ] ) ) {

				//Keep any other capabilities
				$default_mappings[ $wp_role ] = array_diff( $default_mappings[ $wp_role ], $update_others );
			}

			// Delete
			if ( in_array( $wp_role, $settings_delete, true ) ) {
				$default_mappings[ $wp_role ] = array_merge( $default_mappings[ $wp_role ], $delete, $delete_others );

			} elseif ( ! empty( $default_mappings[ $wp_role ] ) ) {

				//Keep any other capabilities
				$default_mappings[ $wp_role ] = array_diff( $default_mappings[ $wp_role ], $delete_others );
			}
		}

		// set roles capabilities
		foreach ( $default_mappings as $role => $mapping ) {

			$role = get_role( $role );

			if ( $role ) {
				foreach ( $all as $cap ) {
					if ( ! in_array( $cap, $mapping, true ) ) {
						$role->remove_cap( $cap );
						continue;
					}

					$role->add_cap( $cap );
				}
			}
		}

		update_option( $option_name, 1 );

	}

	private function get_roles() {
		global $wp_roles;
		$roles = $wp_roles->get_names();

		unset( $roles['subscriber'], $roles['contributor'], $roles['author'], $roles['administrator'] );

		return $roles;
	}

	/**
	 * @return mixed
	 */
	public function get_available_sections() {
		$available_sections_array = get_field( 'available_sections', 'option' );

		if ( get_field( 'wpd_enable_blueprints_for', 'option' ) === 'all' && ! current_user_can( 'manage_options' ) ) {
			$available_sections_array = dollie()->remove_element_with_value( $available_sections_array, 'value', 'blueprints' );
		}

		$available_sections_array[] = 'staging';

		return $available_sections_array;
	}

	/**
	 * Allow only logged in users
	 */
	public function logged_in_only() {
		$login_id = dollie()->get_login_page_id();
		$dash_id  = dollie()->get_dashboard_page_id();

		if ( ! $login_id ) {
			return;
		}

		if ( ! is_user_logged_in() && ( is_singular( 'container' ) || ( $dash_id && is_page( $dash_id ) ) ) ) {
			wp_redirect( get_permalink( $login_id ) );
			exit;
		}
	}

	/**
	 * Launch site only for logged in users
	 */
	public function protect_launch_site() {
		$launch_id = dollie()->get_launch_page_id();
		$dash_id   = dollie()->get_dashboard_page_id();
		$sites_id  = dollie()->get_sites_page_id();

		if ( ! $launch_id || ! $dash_id || ! $sites_id ) {
			return;
		}

		if ( ! is_user_logged_in() && ( is_page( $launch_id ) || is_page( $sites_id ) ) ) {
			wp_redirect( get_permalink( $dash_id ) );
			exit();
		}
	}

	/**
	 * Protect container access
	 */
	public function protect_container_access() {
		global $wp_query;

		$sub_page = get_query_var( 'sub_page' );
		if ( ! current_user_can( 'manage_options' ) ) {

			if ( is_post_type_archive( 'container' ) ) {
				wp_redirect( get_site_url( null, '/' ) );
				exit();
			}

			if ( is_singular( 'container' ) ) {

				global $post;
				$current_user_id = get_current_user_id();
				if ( empty( $current_user_id ) || empty( (int) $post->post_author ) ) {
					wp_redirect( get_site_url( null, '/' ) );
					exit();
				}

				// Is site owner?
				if ( $post && (int) $post->post_author !== get_current_user_id() ) {
					wp_redirect( get_site_url( null, '/' ) );
					exit();
				}

				// Has access to the specific section?
				if ( $sub_page && ! dollie()->in_array_r( $sub_page, $this->get_available_sections() ) ) {
					wp_redirect( get_permalink() );
					exit();
				}
			}
		}
	}

	/**
	 * Protect container access
	 */
	public function disable_blueprint_domain_access() {
		global $wp_query;
		$sub_page = get_query_var( 'sub_page' );

		if ( ! is_singular( 'container' ) ) {
			return;
		}

		if ( 'domains' === $sub_page && dollie()->is_blueprint( $wp_query->post->ID ) ) {
			wp_redirect( get_permalink() );
			exit();
		}

		if ( 'blueprints' === $sub_page && ! dollie()->is_blueprint( $wp_query->post->ID ) ) {
			wp_redirect( get_permalink() );
			exit();
		}
	}

	/**
	 * @param $query_args
	 * @param $r
	 *
	 * @return mixed
	 */
	public function allow_all_authors( $query_args, $r ) {
		$query_args['who'] = '';

		return $query_args;
	}

	/**
	 * Admin access only for dashboard
	 */
	public function admin_access_only() {
		$dash_id = dollie()->get_dashboard_page_id();

		if ( ! $dash_id ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) && ! wp_doing_ajax() ) {
			$redirect = get_permalink( $dash_id );
			wp_redirect( $redirect );
			exit();
		}
	}

	/**
	 * Restrict deletion for Dollie forms
	 *
	 * @param $allcaps
	 * @param $caps
	 * @param $args
	 *
	 * @return mixed
	 */
	public function restrict_form_delete( $allcaps, $caps, $args ) {
		if ( defined( 'DOLLIE_DEV' ) && DOLLIE_DEV ) {
			return $allcaps;
		}

		if ( isset( $args[0], $args[2] ) && 'delete_post' === $args[0] && function_exists( 'af_get_form' ) ) {
			$form = af_get_form( $args[2] );

			if ( $form && isset( $form['key'] ) && strpos( $form['key'], 'form_dollie' ) !== false ) {
				$allcaps[ $caps[0] ] = false;
			}
		}

		return $allcaps;
	}

}
