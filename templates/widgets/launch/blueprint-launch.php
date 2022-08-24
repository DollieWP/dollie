<?php
$container = dollie()->get_container( $_COOKIE[ DOLLIE_BLUEPRINTS_COOKIE ] );
if ( get_field( 'wpd_blueprint_image', $container->get_id() ) === 'custom' ) {
	$image = get_field( 'wpd_blueprint_custom_image', $container->get_id() );
} elseif ( get_field( 'wpd_blueprint_image', $container->get_id() ) === 'theme' ) {
	$image = get_post_meta( $container->get_id(), 'wpd_blueprint_active_theme_screenshot_url', true );
} else {
	$image = get_the_post_thumbnail_url( $container->get_id(), 'full' );
}
;?>

<div class="dol-blueprint-launch-form dol-mt-12">
    <div class="dol-blueprint-launch-inner">
        <div class="dol-flex dol-flex-wrap dol--mx-4 dol-my-2">
            <div class="dol-w-full md:dol-w-3/5 lg:dol-w-2/3 dol-px-4 dol-text-md ">

                <?php if ( isset( $_GET['payment-status'] ) ) { ?>

                <h2 class="dol-mb-3 dol-font-bold placeholder:dol-p-0 md:dol-text-xl">
                    <?php esc_html_e( 'Your purchase was successful!', 'dollie' ); ?>
                </h2>

                <p class="dol-text-gray-500 dol-text-base dol-text-md dol-my-5">
                    <?php
						printf(
							'You can find your payment details in your <a href="%s">account overview</a> and in your email inbox. Now let\'s continue setting up your site...',
							wc_get_account_endpoint_url('orders')
						);?>

                    <?php } ?>


                    <?php dollie()->load_template( 'widgets/launch/form', [ 'settings' => $settings ], true );?>
            </div>
            <div
                class="dol-w-full md:dol-w-2/5 lg:dol-w-1/3 dol-rounded dol-overflow-hidden dol-border <?php do_action( 'dol_add_widget_classes' ); ?> dol-p-6">
                <h4 class="dol-mb-3 dol-text-center dol-p-0 dol-text-base md:dol-text-xl">
                    Design - <strong><?php echo $container->get_saved_title(); ?></strong>
                </h4>
                <div class="dol-border dol-border-solid dol-border-primary-50 dol-rounded dol-overflow-hidden">
                    <img class="dol-block" src="<?php echo $image ?>"
                        alt="<?php echo $container->get_saved_title(); ?>">
                </div>
                <p class="dol-mt-3 dol-text-center ">
                    <?php echo $container->get_saved_description(); ?>
                </p>
                <a href="#"><?php esc_html_e( 'Choose another template..', 'dollie' ); ?></a>
            </div>
        </div>
    </div>
</div>