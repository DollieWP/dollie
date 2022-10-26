<?php

namespace Dollie\Core\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;

final class AccessService extends Singleton {
	/**
	 * Custom capabilities of custom post type
	 */
	private static $custom_caps = [
		'singular' => 'wpd_site',
		'plural'   => 'wpd_sites',
	];

	/**
	 * Filter sites for current author
	 *
	 * @param [type] $query
	 *
	 * @return void
	 */
	public function sites_for_current_author( $query ) {
		if ( ! is_admin() ) {
			return $query;
		}

		global $pagenow;

		if ( 'edit.php' !== $pagenow || ! isset( $_GET['post_type'] ) || $_GET['post_type'] !== 'container' ) {
			return $query;
		}

		$user = dollie()->get_user();

		if ( ! $user->can_view_all_sites() ) {
			$query->set( 'author', $user->get_id() );
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
			'administrator' => $all,
		];

		// Get role settings.
		$settings_view   = get_option( 'options_wpd_view_sites_permission' );
		$settings_update = get_option( 'options_manage_sites_permission' );
		$settings_delete = get_option( 'options_delete_sites_permission' );

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

				// Keep any other capabilities
				$default_mappings[ $wp_role ] = array_diff( $default_mappings[ $wp_role ], $view_others );
			}

			// Update
			if ( in_array( $wp_role, $settings_update, true ) ) {

				$default_mappings[ $wp_role ] = array_merge( $default_mappings[ $wp_role ], $update, $update_others );
			} elseif ( ! empty( $default_mappings[ $wp_role ] ) ) {

				// Keep any other capabilities
				$default_mappings[ $wp_role ] = array_diff( $default_mappings[ $wp_role ], $update_others );
			}

			// Delete
			if ( in_array( $wp_role, $settings_delete, true ) ) {
				$default_mappings[ $wp_role ] = array_merge( $default_mappings[ $wp_role ], $delete, $delete_others );

			} elseif ( ! empty( $default_mappings[ $wp_role ] ) ) {

				// Keep any other capabilities
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

	/**
	 * Get roles
	 *
	 * @return array
	 */
	private function get_roles() {
		global $wp_roles;
		$roles = $wp_roles->get_names();

		unset( $roles['subscriber'], $roles['contributor'], $roles['author'], $roles['administrator'] );

		return $roles;
	}

	/**
	 * Get available sections
	 *
	 * @return mixed
	 */
	public function get_available_sections() {
		$available_sections_array = get_field( 'available_sections', 'option' );

		if ( get_field( 'wpd_enable_blueprints_for', 'option' ) === 'all' && ! current_user_can( 'manage_options' ) ) {
			$available_sections_array = array_filter(
				$available_sections_array,
				function ( $v, $k ) {
					return $v['value'] !== 'blueprints';
				},
				ARRAY_FILTER_USE_BOTH
			);
		}

		$available_sections_array[] = 'staging';

		return $available_sections_array;
	}

	/**
	 * Allow only logged in users
	 *
	 * @return void
	 */
	public function logged_in_only() {
		$login_id = dollie()->page()->get_login_id();

		if ( ! $login_id ) {
			return;
		}

		$dash_id     = dollie()->page()->get_dashboard_id();
		$woo_account = function_exists( 'is_account_page' ) &&
		               ( is_account_page()
		                 || is_wc_endpoint_url( 'lost-password' )
		                 || is_wc_endpoint_url( 'customer-logout' )
		               );

		if ( $woo_account ) {
			return;
		}

		if ( ! is_user_logged_in() && ( is_singular( 'container' ) || ( $dash_id && is_page( $dash_id ) ) ) ) {
			wp_redirect( get_permalink( $login_id ) );
			exit;
		}
	}

	/**
	 * Launch site only for logged in users
	 *
	 * @return void
	 */
	public function protect_launch_site() {
		$launch_id = dollie()->page()->get_launch_site_id();
		$dash_id   = dollie()->page()->get_dashboard_id();
		$sites_id  = dollie()->page()->get_sites_id();

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
	 *
	 * @return void
	 */
	public function protect_container_access() {
		$sub_page = get_query_var( 'sub_page' );
		$user     = dollie()->get_user();

		if ( $user->can_manage_all_sites() ) {
			return;
		}

		if ( is_post_type_archive( 'container' ) ) {
			wp_redirect( get_site_url( null, '/' ) );
			exit();
		}

		if ( is_singular( 'container' ) ) {
			global $post;

			// If something wrong with the logged in user or post author.
			if ( ! $user || empty( (int) $post->post_author ) ) {
				wp_redirect( get_site_url( null, '/' ) );
				exit();
			}

			// Is site owner?
			if ( $post && (int) $post->post_author !== $user->get_id() ) {
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

	/**
	 * Add body timestamp
	 *
	 * @param $classes
	 *
	 * @return array
	 */
	public function add_permissions_body_class( $classes ) {
		$user = dollie()->get_user();

		if ( $user->can_manage_all_sites() || $user->can_manage_options() ) {
			$classes[] = 'dol-is-admin';
		} else {
			$classes[] = 'dol-is-user';
		}

		return $classes;
	}

	/**
	 * Protect container access
	 *
	 * @param [type] $field
	 *
	 * @return void
	 */
	public function acf_field_admin_access( $field ) {
		acf_render_field_setting(
			$field,
			array(
				'label'        => __( 'Access Control for Dollie' ),
				'instructions' => 'Only show this field for users with the "Can Manage All Sites" Dollie permissions.',
				'name'         => 'dollie_admin_only',
				'type'         => 'true_false',
				'ui'           => 1,
			),
			true
		);
	}

	/**
	 * Protect container access
	 */
	public function acf_field_admin_prepare_access( $field ) {
		// bail early if no 'admin_only' setting.
		if ( empty( $field['dollie_admin_only'] ) ) {
			return $field;
		}

		// return false if is not Dollie admin (removes field).
		if ( ! dollie()->get_user()->can_manage_all_sites() ) {
			echo '
				<style type="text/css">
					.acf-field-' . substr( $field['key'], 6 ) . ' > .acf-label {display: none;}
				</style>';

			return false;
		}

		// return.
		return $field;
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

		$container = dollie()->get_container( $wp_query->post->ID );

		if ( is_wp_error( $container ) ) {
			return;
		}

		if ( 'domains' === $sub_page && $container->is_blueprint() ) {
			wp_redirect( get_permalink() );
			exit();
		}

		if ( 'blueprints' === $sub_page && ! $container->is_blueprint() ) {
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
		$dash_id = dollie()->page()->get_dashboard_id();

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
