<?php

namespace Dollie\Core\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Plugin;
use Dollie\Core\Singleton;

final class NoticeService extends Singleton {

	/**
	 * Elementor not installed notice
	 */
	public function missing_elementor() {
		$class = 'notice notice-warning dollie-notice dollie-setup dollie-connect-message';
		/* translators: %s: html tags */
		$message = sprintf( __( 'Welcome to Dollie! Before we continue we need to install the required %1$sElementor%2$s plugin.', 'dollie' ), '<strong>', '</strong>' );

		$plugin = 'elementor/elementor.php';

		if ( dollie()->is_plugin_installed( $plugin ) ) {
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}

			$action_url   = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin );
			$button_label = __( 'Activate Elementor', 'dollie' );
		} else {
			if ( ! current_user_can( 'install_plugins' ) ) {
				return;
			}

			$action_url   = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=elementor' ), 'install-plugin_elementor' );
			$button_label = __( 'Install Elementor', 'dollie' );
		}

		$button = '<p><a href="' . $action_url . '" class="button-primary">' . $button_label . '</a></p><p></p>';

		printf( '<div class="%1$s"><p>%2$s</p>%3$s</div>', esc_attr( $class ), $message, $button );
	}

	/**
	 * Displays notice on the admin dashboard if Elementor version is lower than the
	 * required minimum.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function minimum_elementor_version() {
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		if ( isset( $_GET['activate'] ) ) { // WPCS: CSRF ok, input var ok.
			unset( $_GET['activate'] ); // WPCS: input var ok.
		}

		$message = sprintf(
			'<span style="display: block; margin: 0.5em 0.5em 0 0; clear: both;">'
				/* translators: 1: Plugin name 2: Elementor */
				. esc_html__( '%1$s requires version %3$s or greater of %2$s plugin.', 'dollie' )
				. '</span>',
			'<strong>' . __( 'Dollie', 'dollie' ) . '</strong>',
			'<strong>' . __( 'Elementor', 'dollie' ) . '</strong>',
			Plugin::$minimum_elementor_version
		);

		$file_path   = 'elementor/elementor.php';
		$update_link = wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . $file_path, 'upgrade-plugin_' . $file_path );

		$message .= sprintf(
			'<span style="display: block; margin: 0.5em 0.5em 0 0; clear: both;">' .
				'<a class="button-primary" href="%1$s">%2$s</a></span>',
			$update_link,
			__( 'Update Elementor Now', 'dollie' )
		);

		printf( '<div class="notice notice-error"><p>%1$s</p></div>', $message );
	}

	/**
	 * Connect with API notice
	 *
	 * @return void
	 */
	public function not_connected(): void {
		$user = dollie()->get_user();

		$auth_service = AuthService::instance();

		if ( $auth_service->is_connected() || ! $user->can_manage_all_sites() || ! defined( 'ELEMENTOR_VERSION' ) ) {
			return;
		}

		dollie()->load_template( 'admin/notices/not-connected', [ 'auth_url' => $auth_service->get_auth_url() ], true );
	}

	/**
	 * No credits
	 *
	 * @return void
	 */
	public function subscription_no_credits(): void {

		$user = dollie()->get_user();

		if ( ! $user->can_manage_all_sites() ||
			! dollie()->auth()->is_connected() ) {
			return;
		}

		if ( isset( $_GET['wpd_check_subscription'] ) ) {
			delete_transient( 'wpd_partner_subscription' );
			wp_redirect( admin_url() );
			die();
		}

		if ( ! dollie()->subscription()->has_partner_subscription() && ! dollie()->subscription()->has_partner_hit_time_limit() && dollie_setup_get_setup_step() !== 'no-package' ) {
			dollie()->load_template( 'admin/notices/subscription-missing', [], true );
		} elseif ( ! dollie()->subscription()->has_partner_subscription() && dollie()->subscription()->has_partner_hit_time_limit() && dollie_setup_get_setup_step() !== 'no-package' ) {
			dollie()->load_template( 'admin/notices/subscription-time-limit', [], true );
		} elseif ( dollie()->subscription()->has_partner_subscription() &&
			0 === dollie()->subscription()->get_partner_deploy_limit() ) {
			dollie()->load_template( 'admin/notices/subscription-sites-limit', [], true );
		}
	}

	/**
	 * No credits
	 *
	 * @return void
	 */
	public function subscription_not_verified(): void {
		if ( ! current_user_can( 'manage_options' ) ||
			! dollie()->auth()->is_connected() ) {
			return;
		}

		if ( isset( $_GET['wpd_check_subscription'] ) ) {
			delete_transient( 'wpd_partner_subscription' );
			wp_redirect( admin_url() );
			die();
		}

		if ( ! dollie()->subscription()->has_partner_verified() ) {
			dollie()->load_template( 'admin/notices/subscription-not-verified', [], true );
		}
	}

	/**
	 * Container manager notice
	 *
	 * @return void
	 */
	public function container_manager(): void {
		if ( 'container' !== get_post_type() ) {
			return;
		}

		$container = dollie()->get_container( $_GET['post'] );

		if ( is_wp_error( $container ) ) {
			return;
		}

		dollie()->load_template( 'admin/notices/container-management', [ 'container' => $container ], true );
	}

	/**
	 * Change role notice
	 *
	 * @return void
	 */
	public function change_user_role(): void {
		dollie()->load_template( 'admin/notices/change-user-role', [], true );
	}

}
