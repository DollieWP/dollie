<div class="dol-my-6">
	<?php

	$container_details = \Dollie\Core\Modules\Container::instance()->get_remote_stats( get_the_ID(), true );
	$staging_on        = isset( $container_details['Staging'] ) && $container_details['Staging'];
	$status            = $staging_on ? __( 'Enabled', 'dollie' ) : __( 'Disabled', 'dollie' );

	$settings_url = 'wp-admin/admin.php?page=wpstagecoach';
	$settings_url = dollie()->get_customer_login_url( get_the_ID(), $settings_url );

	$container = get_post( get_the_ID() );
	$message   = sprintf( __( 'Here you can easily manage your staging site for <strong>%s</strong>. Staging allows you to conduct different tests before applying them to your live site.', 'dollie' ), $container->post_name );

	if ( $staging_on && isset( $container_details['Staging URL'] ) ) {

		$staging_url = 'https://' . $container_details['Staging URL'];
		$message     .= '<br>' .
		                sprintf(
			                '<strong>Staging URL</strong>: <a target="_blank" href="%s">%s</a>',
			                $staging_url,
			                $staging_url
		                );
	}

	\Dollie\Core\Utils\Tpl::load(
		'notice',
		[
			'type'    => 'info',
			'icon'    => 'fas fa-clone',
			'title'   => sprintf( __( 'Staging Site is %s', 'dollie' ), $status ),
			'message' => $message
		],
		true
	);
	?>
</div>

<?php
if ( isset( $_GET['staging_status'], $_GET['action'] ) ) {

	$response = sanitize_text_field( $_GET['staging_status'] );

	if ( $response === 'success' ) {
		$type        = 'success';
		$text_status = sanitize_text_field( $_GET['action'] ) === 'enabled' ? __( 'enabled', 'dollie' ) : __( 'disabled', 'dollie' );
		$text        = sprintf( __( 'Staging has been %s', 'dollie' ), $text_status );

	} else {
		$type = 'error';
		$text = __( 'An error occurred when changing the staging status. Please try again!', 'dollie' );

	}
	\Dollie\Core\Utils\Tpl::load(
		'notice',
		[
			'icon'  => 'fas fa-exclamation-circle',
			'type'  => $type,
			'title' => $text,
		],
		true
	);
}
?>

<?php if ( $staging_on ) : ?>

    <div class="dol-mt-6">
        <form action="" method="post">
            <button class="dol-bg-red-700" name="staging_change" value="0">
				<?php esc_html_e( 'Disable Staging', 'dollie' ); ?>
            </button>
            <a href="<?php echo esc_url( $settings_url ); ?>" class="button" target="_blank">
				<?php esc_html_e( 'Staging Settings', 'dollie' ); ?>
            </a>
			<?php wp_nonce_field( 'wpd_staging' ); ?>
        </form>
    </div>

<?php else : ?>

	<?php
	if ( dollie()->staging_sites_limit_reached() ): ?>
		<?php
		\Dollie\Core\Utils\Tpl::load(
			'notice',
			[
				'icon'  => 'fas fa-exclamation-circle',
				'type'  => 'error',
				'title' => esc_html__( 'You have reached your staging sites limit. Please upgrade your subscription!', 'dollie' ),
			],
			true
		);

		return;
		?>

	<?php endif; ?>

    <div class="dol-mt-6">
        <form action="" method="post">
            <button name="staging_change" value="1">
				<?php esc_html_e( 'Enable Staging', 'dollie' ); ?>
            </button>
			<?php wp_nonce_field( 'wpd_staging' ); ?>
        </form>
    </div>
<?php endif; ?>
