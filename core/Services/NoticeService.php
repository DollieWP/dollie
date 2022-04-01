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
		$class = 'notice notice-warning';
		/* translators: %s: html tags */
		$message = sprintf( __( '%1$sDollie%2$s requires %1$sElementor%2$s plugin installed & activated.', 'dollie' ), '<strong>', '</strong>' );

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
		$auth_service = AuthService::instance();

		if ( $auth_service->get_token() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		dollie()->load_template( 'admin/notices/not-connected', [ 'url' => $auth_service->get_auth_url() ], true );
	}

	/**
	 * Custom deploy domain
	 *
	 * @return void
	 */
	public function custom_deploy_domain(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! dollie()->is_api_connected() ) {
			return;
		}

		$deployment_domain        = get_option( 'wpd_deployment_domain' );
		$deployment_domain_status = get_option( 'wpd_deployment_domain_status' );

		if ( $deployment_domain && ! $deployment_domain_status ) {
			dollie()->load_template( 'admin/notices/custom-deploy-domain-pending', [], true );
		} elseif ( $deployment_domain && $deployment_domain_status && ! get_option( 'deployment_domain_notice' ) ) {
			dollie()->load_template( 'admin/notices/custom-deploy-domain-active', [], true );
		}
	}

	/**
	 * No credits
	 *
	 * @return void
	 */
	public function subscription_no_credits(): void {
		if ( ! current_user_can( 'manage_options' ) ||
			! dollie()->is_api_connected() ) {
			return;
		}

		if ( isset( $_GET['wpd_check_subscription'] ) ) {
			delete_transient( 'wpd_partner_subscription' );
			wp_redirect( admin_url() );
			die();
		}

		if ( ! dollie()->subscription()->has_partner_subscription() ) {
			dollie()->load_template( 'admin/notices/subscription-missing', [], true );
		} elseif ( dollie()->subscription()->has_partner_subscription() &&
			dollie()->subscription()->is_partner_subscription_trial() &&
			0 === dollie()->subscription()->get_partner_subscription_credits() ) {
			dollie()->load_template( 'admin/notices/subscription-trial-limit', [], true );
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

	/**
	 * Container demo notice
	 *
	 * @return void
	 */
	public function container_demo(): void {
		$container = dollie()->get_container();

		if ( is_wp_error( $container ) || ! $container->is_blueprint() || ! is_singular( 'container' ) ) {
			return;
		}

		dollie()->load_template( 'admin/notices/container-demo', [ 'container' => $container ], true );
	}
}
