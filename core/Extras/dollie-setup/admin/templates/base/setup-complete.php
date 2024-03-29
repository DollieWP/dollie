<?php
$migration = get_option( 'wpd_onboarding_migrate_site_url' );
$url       = preg_replace( '(^https?://)', '', $migration );
$charge    = get_option( 'options_wpd_charge_for_deployments' );
$temp      = explode( '.', $url );
unset( $temp[ count( $temp ) - 1 ] );
$migration = implode( '.', $temp );

$launch_url = get_permalink( dollie()->page()->get_launch_site_id() ) . '?default_site_url=' . sanitize_title( $migration ) . '&greenirect=migrate';

$blueprint            = get_option( 'wpd_onboarding_blueprint_name' );
$launch_blueprint_url = get_permalink( dollie()->page()->get_launch_blueprint_id() ) . '?default_site_url=' . sanitize_title( $blueprint );
?>

<div class="dol-bg-white dol-shadow dol-overflow-hidden sm:dol-rounded-md">
    <ul role="list" class="dol-divide-y dol-divide-gray-200">
        <h4 class="dol-text-gray-500 dol-uppercase dol-tracking-wide dol-mb-1 dol-text-md dol-px-4 sm:dol-px-6">
            Your Remaining Setup Steps
        </h4>
		<?php if ( ! dollie()->auth()->is_connected() ) : ?>
            <li>
                <a href="<?php echo esc_url( dollie()->auth()->get_auth_link() ); ?>"
                   class="dol-block hover:dol-bg-gray-50">
                    <div class="dol-flex dol-items-center dol-px-4 dol-py-4 sm:dol-px-6">
                        <div class="dol-min-w-0 dol-flex-1 dol-flex dol-items-center">
                            <div class="dol-flex-shrink-0">
                                <img class="dol-h-6 dol-w-6 dol-rounded-full dol-bg-green-400 dol-p-2"
                                     src="<?php echo DOLLIE_ASSETS_URL; ?>/img/active.png" alt="">
                            </div>
                            <div class="dol-min-w-0 dol-flex-1 dol-px-4">
                                <div>
                                    <p class="dol-text-sm dol-font-medium dol-text-green-600 dol-truncate"><?php esc_html_e( 'Not connected to Dollie API', 'dollie' ); ?></p>
                                    <p class="dol-mt-2 dol-flex dol-items-center dol-text-sm dol-text-gray-500">
                                        <span class="dol-truncate"><?php esc_html_e( 'Please connect to the API', 'dollie' ); ?></span>
                                    </p>
                                </div>

                            </div>
                        </div>

                    </div>
                </a>
            </li>

		<?php endif; ?>

		<?php if ( $charge && dollie()->has_access_groups() ) : ?>
            <li>
                <a href="<?php echo get_admin_url(); ?>post-new.php?post_type=dollie-access-groups"
                   class="dol-block hover:dol-bg-gray-50">
                    <div class="dol-flex dol-items-center dol-px-4 dol-py-4 sm:dol-px-6">
                        <div class="dol-min-w-0 dol-flex-1 dol-flex dol-items-center">
                            <div class="dol-flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="dol-h-6 dol-w-6 dol-rounded-full dol-bg-green-400 dol-p-2 dol-text-green-100"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <div class="dol-min-w-0 dol-flex-1 dol-px-4">
                                <div>
                                    <p class="dol-text-sm dol-font-medium dol-text-green-600 dol-truncate"><?php esc_html_e( 'Create Your First Hub Access Group', 'dollie' ); ?></p>
                                    <p class="dol-mt-2 dol-flex dol-items-center dol-text-sm dol-text-gray-500">
										<span class="dol-truncate">
                                            <?php esc_html_e( 'Access Groups control what your customers/client are allowed to do inside your Hub.', 'dollie' ); ?>
                                            <br>
                                            <?php esc_html_e( 'You then link an Access Group to a eCommerce or Membership solution like WooCommerce or Paid Memberships Pro', 'dollie' ); ?>
										</span>
                                    </p>
                                </div>

                            </div>
                        </div>

                    </div>
                </a>
            </li>

		<?php endif; ?>

		<?php if ( ! dollie()->workspace()->has_custom_deployment_domain() ) : ?>
            <li>
                <a href="https://control.getdollie.com/setup/deployment-domain" class="dol-block hover:dol-bg-gray-50">
                    <div class="dol-flex dol-items-center dol-px-4 dol-py-4 sm:dol-px-6">
                        <div class="dol-min-w-0 dol-flex-1 dol-flex dol-items-center">
                            <div class="dol-flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="dol-h-6 dol-w-6 dol-rounded-full dol-bg-green-400 dol-p-2 dol-text-green-100"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                </svg>
                            </div>
                            <div class="dol-min-w-0 dol-flex-1 dol-px-4">
                                <div>
                                    <p class="dol-text-sm dol-font-medium dol-text-green-600 dol-truncate"><?php esc_html_e( 'Connect Your Own Domain', 'dollie' ); ?></p>
                                    <p class="dol-mt-2 dol-flex dol-items-center dol-text-sm dol-text-gray-500">
										<span class="dol-truncate"><?php esc_html_e( 'Visit Control HQ and add a Deployment Domain to be used in your Hub', 'dollie' ); ?><br>
										<?php esc_html_e( 'Once your domain is added your sites will be launched under your own domain.', 'dollie' ); ?>
										</span>


                                    </p>
                                </div>

                            </div>
                        </div>

                    </div>
                </a>
            </li>

		<?php endif; ?>
    </ul>
</div>
