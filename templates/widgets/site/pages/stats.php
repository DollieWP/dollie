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
<?php
$shortcodes = [
	'cpu'        => [
		'name'      => __( 'CPU %', 'dollie' ),
		'shortcode' => '[dollie-site-stats type=cpu]',
	],
	'block'      => [
		'name'      => __( 'Disk I/O Data', 'dollie' ),
		'shortcode' => '[dollie-site-stats type=block]',
	],
	'disk_usage' => [
		'name'      => __( 'Disk Usage', 'dollie' ),
		'shortcode' => '[dollie-site-stats type=disk_usage]',
	],
	'memory'     => [
		'name'      => __( 'Memory Usage', 'dollie' ),
		'shortcode' => '[dollie-site-stats type=memory]',
	],
	'network'    => [
		'name'      => __( 'Network Usage', 'dollie' ),
		'shortcode' => '[dollie-site-stats type=network]',
	],
];
?>

<?php foreach ( $shortcodes as $shortcode ) :

	$shortcode_data = do_shortcode( $shortcode['shortcode'] );
	if ( empty( $shortcode_data ) ) {
		continue;
	}

	?>
    <div class="dol-mt-6">
        <h4 class="dol-text-center">
			<?php echo esc_html( $shortcode['name'] ); ?>
        </h4>
		<?php echo $shortcode_data; ?>
    </div>
<?php endforeach; ?>
