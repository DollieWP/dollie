<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class AutoLogin {

	public function __construct() {

		add_action( 'init', [ $this, 'do_autologin' ], - 1 );
	}

	function do_autologin() {


		// redirect_canonical() - make sure we set the correct cookie domain

		if ( ! isset( $_GET['s5token'] ) ) {

			return;
		}

		if ( ! defined( 'S5_APP_TOKEN' ) ) {
			return;

			// TODO - here we can add the new functionality check
			// check user meta and login
		}

		$token = S5_APP_TOKEN;

		if ( $_GET['s5token'] !== $token ) {
			return;
		}

		$blogusers = get_users( 'role=administrator&number=1' );
		// Array of WP_User objects.
		foreach ( $blogusers as $user ) {
			$community_admin = $user->user_login;
		}


		if ( get_option( 'wfp_flush_new_install' ) != 'yes' ) {
			// Clear Object Cache
			wp_cache_flush();
			update_option( 'wfp_flush_new_install', 'yes' );
		}

		$user = false;
		if ( isset( $_GET['user'] ) ) {
			$username = $_GET['user'];
			$user     = get_user_by( 'login', $username );
		}


		if ( ! $user ) {
			$all_users = get_users();
			foreach ( $all_users as $item ) {
				if ( $item->has_cap( 'administrator' ) ) {
					$user = $item;
					break;
				}
			}
		}

		// login as this user
		wp_clear_auth_cookie();
		wp_set_current_user( $user->ID, $user->user_login );
		wp_set_auth_cookie( $user->ID, true, is_ssl() );

		if ( is_user_logged_in() ) {
			if ( $_GET['location'] ) {
				wp_safe_redirect( get_site_url() . $_GET['location'] );
			} else {
				wp_safe_redirect( admin_url() );
			}
			exit;
		}

		if ( $_GET['location'] ) {
			wp_safe_redirect( get_site_url() . $_GET['location'] );
		} else {
			wp_safe_redirect( admin_url() );
		}
		exit;
	}

}
