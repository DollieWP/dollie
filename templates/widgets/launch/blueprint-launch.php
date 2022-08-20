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
				<?php dollie()->load_template( 'widgets/launch/form', [ 'settings' => $settings ], true );?>
				</div>
				<div class="dol-w-full md:dol-w-2/5 lg:dol-w-1/3 dol-rounded dol-overflow-hidden dol-border <?php do_action( 'dol_add_widget_classes' ); ?> dol-p-6">
					<h4 class="dol-mb-3 dol-text-center dol-p-0 dol-text-base md:dol-text-xl">
						Design - <strong><?php echo $container->get_saved_title(); ?></strong>
					</h4>
					<div class="dol-border dol-border-solid dol-border-primary-50 dol-rounded dol-overflow-hidden">
						<img class="dol-block" src="<?php echo $image ?>" alt="<?php echo $container->get_saved_title(); ?>">
					</div>
					<p class="dol-mt-3 dol-text-center ">
					<?php echo $container->get_saved_description(); ?>
					</p>
				</div>
			</div>
		</div>
	</div>
