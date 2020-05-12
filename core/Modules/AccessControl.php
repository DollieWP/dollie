<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;

/**
 * Class AccessControl
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

		add_filter( 'wp_dropdown_users_args', [ $this, 'allow_all_authors' ], 10, 2 );
		add_action( 'admin_init', [ $this, 'no_admin_access' ], 100 );


		add_action( 'user_has_cap', [ $this, 'restrict_form_delete' ], 10, 3 );


	}

	public function get_available_sections() {
		$available_sections_array = get_field( 'available_sections', 'option' );

		if ( get_field( 'wpd_enable_blueprints_for', 'option' ) === 'all' && ! current_user_can( 'manage_options' ) ) {
			$available_sections_array = dollie()->removeElementWithValue( $available_sections_array, 'value', 'blueprint' );
		}

		return $available_sections_array;
	}

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

	public function protect_launch_site() {
		$launch_id = dollie()->get_launch_page_id();
		$dash_id   = dollie()->get_dashboard_page_id();

		if ( ! $launch_id || ! $dash_id ) {
			return;
		}

		if ( ! is_user_logged_in() && is_page( $launch_id ) ) {
			wp_redirect( get_permalink( $dash_id ) );
			exit();
		}
	}

	// Todo - Demo functionality can be removed
	public function protect_container_access() {
		if ( ! current_user_can( 'manage_options' ) ) {

			if ( is_post_type_archive( 'container' ) ) {
				wp_redirect( get_site_url( null, '/' ) );
				exit();
			}

			global $post, $current_user;

			// Is site owner?
			if ( $post->post_author !== $current_user->ID && is_singular( 'container' ) ) {
				wp_redirect( get_site_url( null, '/' ) );
				exit();
			}

			// Has access to the specific section?
			if ( isset( $_GET['page'] ) && is_singular( 'container' ) && ! dollie()->in_array_r( $_GET['page'], $this->get_available_sections() ) ) {
				wp_redirect( get_permalink() );
				exit();
			}
		}
	}

	public function allow_all_authors( $query_args, $r ) {
		$query_args['who'] = '';

		return $query_args;
	}

	public function no_admin_access() {
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

		if ( isset( $args[0], $args[2] ) && $args[0] === 'delete_post' ) {

			$form = af_get_form( $args[2] );

			if ( $form && isset( $form['key'] ) && strpos( $form['key'], 'form_dollie' ) !== false ) {

				$allcaps[ $caps[0] ] = false;
			}

		}

		return $allcaps;

	}

}
