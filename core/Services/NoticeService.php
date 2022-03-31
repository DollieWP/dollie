<?php

namespace Dollie\Core\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Plugin;
use Dollie\Core\Singleton;

final class NoticeService extends Singleton {
	/**
	 * Elementor not installed notice
	 */
	public function elementor_notice() {
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
	public function admin_notice_minimum_elementor_version() {
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
	 * Connect with dollie notice
	 */
	public function admin_auth_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( dollie()->is_api_connected() ) {
			return;
		}

		if ( isset( $_GET['hosted'] ) ) {
			update_option( 'dollie_hosted_by_us', 'yes' );
		}

		?>
		<div class="notice dollie-notice dollie-setup dollie-connect-message">
			<div class="dollie-inner-message">
				<div class="dollie-message-center">
					<h3><span>Setup -</span> <?php esc_html_e( 'Welcome to Dollie, let\'s get you started!', 'dollie' ); ?> </h3>
					<p><?php esc_html_e( 'To start building your platform we first need to securely authenticate this site with our Dollie API. Simply click on the button below to continue...', 'dollie' ); ?></p>
				</div>

				<div class="dollie-msg-button-right">
					<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="#33D399" viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
					</svg><?php echo Plugin::instance()->get_api_access_link(); ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Deployment domain notice
	 */
	public function admin_deployment_domain_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! dollie()->is_api_connected() ) {
			return;
		}

		$deployment_domain        = get_option( 'wpd_deployment_domain' );
		$deployment_domain_status = get_option( 'wpd_deployment_domain_status' );

		if ( $deployment_domain && ! $deployment_domain_status ) {
			?>
			<div class="notice dollie-notice">
				<div class="dollie-inner-message">
					<div class="dollie-message-center">
						<h3><?php esc_html_e( 'Dollie - Custom Domain is Pending', 'dollie' ); ?> </h3>
						<p><?php esc_html_e( 'Your custom domain has been added, but it may take up to 24 hours for the NS records to propagate. We will constantly check your domain and automatically replace your deployment domain with the custom one, once we detect the change. In the meantime, all your deploys will use the default deployment domain.', 'dollie' ); ?></p>
					</div>
				</div>
			</div>
			<?php
		} elseif ( $deployment_domain && $deployment_domain_status && ! get_option( 'deployment_domain_notice' ) ) {
			?>
			<div class="notice dollie-notice dollie-custom-domain-notice">
				<div class="dollie-inner-message">
					<div class="dollie-message-center">
						<h3><?php esc_html_e( 'Dollie - Custom Domain Activated', 'dollie' ); ?> </h3>
						<p><?php esc_html_e( 'Yey! Your custom deployment domain is ready to use. From now on, all your launched sites will use your custom domain.', 'dollie' ); ?></p>
					</div>
					<div>
						<a href="#" data-nonce="<?php echo esc_js( wp_create_nonce( 'dollie_notice' ) ); ?>" class="dollie-notice-dismiss">
						<?php esc_html_e( 'Dismiss', 'dollie' ); ?>
						</a>
					</div>
				</div>
			</div>

			<script type="text/javascript">
				jQuery(document).ready(function($) {
					$('.dollie-notice-dismiss').on('click', function(e) {
						e.preventDefault();

						$.post(
							ajaxurl, {
								action: 'dollie_hide_domain_notice',
								_dollie_nonce: $(this).data('nonce')
							},
							function(response) {
								response = response.trim();

								if (response === "1") {
									jQuery('.dollie-custom-domain-notice').remove();
								}
							}
						);
					});
				});
			</script>
			<?php
		}
	}

	/**
	 * Remove deployment domain notice
	 */
	public function remove_deployment_domain_notice() {
		if ( check_ajax_referer( 'dollie_notice', '_dollie_nonce' ) && update_option( 'deployment_domain_notice', true ) ) {
			echo 1;
		} else {
			echo 0;
		}
		die();
	}

	/**
	 * No credits notice
	 *
	 * @return void
	 */
	public function admin_subscription_no_credits() {
		if ( ! current_user_can( 'manage_options' ) ||
			! dollie()->is_api_connected() ) {
			return;
		}

		if ( isset( $_GET['wpd_check_subscription'] ) ) {
			delete_transient( 'wpd_partner_subscription' );
			wp_redirect( admin_url() );
			die();
		}

		?>
		<?php if ( ! dollie()->has_partner_subscription() ) : ?>
			<div class="notice dollie-notice dollie-notice-error">
				<div class="dollie-inner-message">
					<div class="dollie-message-center">
						<h3><?php esc_html_e( 'Dollie Error - No active subscription', 'dollie' ); ?> </h3>
						<p><?php esc_html_e( 'It seems like your Dollie Subscription is expired or inactive. Please note that your clients won\'t be able to deploy until you activate your subscription.', 'dollie' ); ?></p>
					</div>

					<?php
					printf(
						'<a href="https://partners.getdollie.com/?redirect=my-account/subscriptions/" class="button button-primary" target="_blank">%s</a>',
						esc_html__( 'Update Subscription', 'dollie' )
					);

					printf(
						'<a href="%s" class="button" style="margin-left: 10px;">%s</a>',
						esc_url( admin_url() . '?wpd_check_subscription' ),
						esc_html__( 'Re-Check Subscription Status', 'dollie' )
					);
					?>
				</div>
			</div>
		<?php elseif ( dollie()->has_partner_subscription() && dollie()->is_partner_subscription_trial() && 0 === dollie()->get_partner_subscription_credits() ) : ?>
			<div class="notice dollie-notice dollie-notice-error">
				<div class="dollie-inner-message">
					<div class="dollie-message-center">
						<h3><?php esc_html_e( 'Dollie Notice - Trial Subscription Limit Reached', 'dollie' ); ?> </h3>
						<p><?php esc_html_e( 'Your trial subscription has reached the maximum allowed deploys. Please note that your clients won\'t be able to deploy until you activate your subscription.', 'dollie' ); ?></p>
					</div>

					<?php
					printf(
						'<a href="https://partners.getdollie.com/?redirect=my-account/subscriptions/" class="button button-primary" target="_blank">%s</a>',
						esc_html__( 'Update Subscription', 'dollie' )
					);

					printf(
						'<a href="%s" class="button" style="margin-left: 10px;">%s</a>',
						esc_url( admin_url() . '?wpd_check_subscription' ),
						esc_html__( 'Check again', 'dollie' )
					);
					?>
				</div>
			</div>
		<?php endif; ?>
		<?php
	}

	/**
	 * Cannot connect
	 *
	 * @return void
	 */
	public function could_not_connect_message() {
		?>
		<div class="dol-border dol-border-solid dol-border-primary-100 dol-rounded dol-overflow-hidden">
			<div class="dol-flex dol-items-center dol-bg-red-600">
				<div class="dol-p-4 lg:dol-px-8 lg:dol-py-4 dol-bg-red-700 dol-flex dol-items-center dol-justify-center">
					<?php echo dollie()->icon()->alert( 'dol-text-white dol-text-2xl' ); ?>
				</div>
				<h4 class="dol-px-4 lg:dol-px-8 lg:dol-py-4 dol-m-0 dol-p-0 dol-text-white dol-text-base md:dol-text-xl">
					<?php esc_html_e( 'Sorry, we could not retrieve your site details', 'dollie' ); ?>
				</h4>
			</div>
			<div class="dol-px-4 dol-py-2 lg:dol-px-8 lg:dol-py-6 dol-bg-gray-100">
				<div class="dol-mb-4">
					<?php esc_html_e( 'We could not connect to your site to retrieve its details. This is usually caused by your WordPress site being unavailable or having a site-breaking error.', 'dollie' ); ?>
				</div>

				<div>
					<a href="<?php echo esc_url( get_permalink() . '?get-details' ); ?>" class="dol-text-sm dol-text-white hover:dol-text-white dol-inline-block dol-px-4 dol-py-2 dol-bg-gray-800 hover:dol-bg-gray-900 dol-rounded">
						<?php esc_html_e( 'Retry', 'dollie' ); ?>
					</a>

					<a href="<?php echo esc_url( get_site_url() . '/support' ); ?>" class="dol-text-sm dol-text-white hover:dol-text-white dol-inline-block dol-px-4 dol-py-2 dol-bg-gray-500 hover:dol-bg-gray-600 dol-rounded">
						<?php esc_html_e( 'Create a support ticket', 'dollie' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Container manager notice
	 *
	 * @return void
	 */
	public function container_manager_notice() {
		if ( 'container' !== get_post_type() ) {
			return;
		}

		$container = dollie()->get_container( $_GET['post'] );

		if ( is_wp_error( $container ) ) {
			return;
		}

		?>
		<br>
		<div style="margin-left: 0; z-index: 0" class="dollie-notice dollie-notice-error">
			<div class="dollie-inner-message">
				<?php if ( $container->is_blueprint() ) : ?>
					<div class="dollie-message-center">
						<h3><?php esc_html_e( 'Notice - How To Manage & Update This Blueprint', 'dollie' ); ?> </h3>
						<p>
							<?php
							echo wp_kses_post(
								sprintf(
									__( '<a href="%s">Manage this Blueprint using the front-end of your dashboard.</a> Use this page to take advanced actions, like stopping/removing the blueprint completely or assigning it to another user.', 'dollie' ),
									esc_url( $container->get_permalink() . '/blueprints' )
								)
							);
							?>
					</div>
				<?php else : ?>
					<div class="dollie-message-center">
						<h3><?php esc_html_e( 'Notice - How To Manage This Site', 'dollie' ); ?> </h3>
						<p>
							<?php
							echo wp_kses_post(
								sprintf(
									__( 'We recommend managing this site on the front-end of your installation using the <a href="%s">Site Dashboard</a>. Use this page to take advanced actions, like stopping or restarting your site.', 'dollie' ),
									esc_url( $container->get_permalink() )
								)
							);
							?>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Change role notice
	 *
	 * @return void
	 */
	public function change_role_notice_script() {
		?>
		<script type="text/javascript">
			(function($) {
				var customer_role = $('[data-name="wpd_client_site_permission"]');
				if (customer_role.length) {
					var key = customer_role.data('key');

					$('[name="acf[' + key + ']"]').on('change', function() {
						alert('IMPORTANT! Changing the clients permission will change the permission for ALL the websites of ALL your clients. Changing to Editor will cause all your clients to have only editor role accounts on their websites. Please note that doesn\'t affect the websites launched by administrators.');
					})
				}
			})(jQuery);
		</script>
		<?php
	}

	/**
	 * Blueprint notice
	 *
	 * @return void
	 */
	public function site_demo_notice() {
		$container = dollie()->get_container();

		if ( is_wp_error( $container ) || ! $container->is_blueprint() || ! is_singular( 'container' ) ) {
			return;
		}

		?>
		<div class="dol-fixed dol-w-full dol-bg-accent dol-p-3 dol-text-white dol-bottom-0 dol-left-0 dol-z-50 dol-text-center">
			<a class="dol-text-white hover:dol-text-white" href="<?php echo $container->get_permalink() . '/blueprints'; ?>">
				<?php echo dollie()->icon()->site(); ?><strong> <?php printf( __( 'Demo %1$s', 'dollie' ), $container->get_type_string() ); ?></strong> -
				<?php printf( __( 'This %1$s is not part of your platform but serves as an example so you can view your awesome dashboard. Pretty neat huh?', 'dollie' ), $container->get_type_string() ); ?>
			</a>
		</div>
		<?php
	}
}
