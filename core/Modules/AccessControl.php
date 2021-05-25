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
	 * AccessControl constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'template_redirect', [ $this, 'logged_in_only' ] );
		add_action( 'template_redirect', [ $this, 'protect_launch_site' ] );
		add_action( 'template_redirect', [ $this, 'protect_container_access' ], 1 );
		add_action( 'template_redirect', [ $this, 'disable_blueprint_domain_access' ], 1 );
		add_action( 'admin_init', [ $this, 'admin_access_only' ], 100 );
		add_action( 'user_has_cap', [ $this, 'restrict_form_delete' ], 10, 3 );

		add_filter( 'wp_dropdown_users_args', [ $this, 'allow_all_authors' ], 10, 2 );
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
