<?php

if ( ! isset( $container ) ) {
	$container = dollie()->get_container();
}

?>

<div class="dol-my-6">
	<?php

	dollie()->load_template(
		'notice',
		[
			'type'    => 'info',
			'icon'    => 'fas fa-chart-line',
			'title'   => __( 'Resource Usage', 'dollie' ),
			'message' => sprintf( __( 'Here you can see resource usage statistics for your site.', 'dollie' ), $container->get_url() ),
		],
		true
	);

	?>
</div>

<div class="dol-mt-6">
    <h4 class="dol-text-center"><?php esc_html_e( 'CPU %', 'dollie' ); ?></h4>
	<?php echo do_shortcode( '[dollie-site-stats type=cpu]' ); ?>
</div>

<div class="dol-mt-6">
    <h4 class="dol-text-center"><?php esc_html_e( 'Disk I/O Data', 'dollie' ); ?></h4>
	<?php echo do_shortcode( '[dollie-site-stats type=block]' ); ?>
</div>

<div class="dol-mt-6">
    <h4 class="dol-text-center"><?php esc_html_e( 'Disk Usage', 'dollie' ); ?></h4>
	<?php echo do_shortcode( '[dollie-site-stats type=disk_usage]' ); ?>
</div>

<div class="dol-mt-6">
    <h4 class="dol-text-center"><?php esc_html_e( 'Memory Usage', 'dollie' ); ?></h4>
	<?php echo do_shortcode( '[dollie-site-stats type=memory]' ); ?>
</div>

<div class="dol-mt-6">
    <h4 class="dol-text-center"><?php esc_html_e( 'Network Usage', 'dollie' ); ?></h4>
	<?php echo do_shortcode( '[dollie-site-stats type=network]' ); ?>
</div>
