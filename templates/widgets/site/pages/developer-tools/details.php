<?php

if ( ! isset( $container ) ) {
	$container = dollie()->get_container();
}

$credentials = $container->get_credentials();

if ( empty( $credentials ) ) {
	return;
}

$sftp_url = "sftp://{$credentials['username']}:{$credentials['password']}@{$credentials['ip']}:{$credentials['port']}";

?>

<h2 class="dol-text-gray-500 dol-uppercase dol-tracking-wide dol-mb-5 dol-text-xl">
	<?php esc_html_e( 'Developer Details', 'dollie' ); ?>
</h2>

<div class="dol-border <?php do_action( 'dol_add_widget_classes' ); ?> dol-overflow-hidden dol-my-6">
	<div class="dol-p-4 lg:dol-px-8 lg:dol-py-4 dol-bg-primary-600 dol-border-0 dol-border-b">
		<h4 class="dol-m-0 dol-p-0 dol-text-white dol-text-base md:dol-text-xl">
			<?php esc_html_e( 'SFTP Details', 'dollie' ); ?>
		</h4>
	</div>
	<div class="dol-p-4 lg:dol-px-8 lg:dol-py-6 ">
		<div class="dol-font-bold dol-mb-3">
			<?php esc_html_e( 'Use the details below in your SFTP client to connect to the site and manage your files.', 'dollie' ); ?>
		</div>

		<code>
			<ul class="dol-list-none dol-font-sm dol-bg-base-200 dol-mt-2 dol-p-3 dol-text-sm">
				<li>
					<div>
						<?php printf( __( 'IP: %s', 'dollie' ), $credentials['ip'] ); ?>
					</div>
					<div>
						<?php printf( __( 'Port: %s', 'dollie' ), $credentials['port'] ); ?>
					</div>
				</li>
				<li>
					<div>
						<?php printf( __( 'Username: %s', 'dollie' ), $credentials['username'] ); ?>
					</div>
					<div>
						<?php printf( __( 'Password: %s', 'dollie' ), $credentials['password'] ); ?>
					</div>
				</li>
			</ul>
		</code>
		<a href="<?php echo $sftp_url; ?>" class="dol-btn dol-btn-secondary dol-btn-sm dol-my-3 hover:dol-text-brand-600">
			<?php echo dollie()->icon()->arrow_right( 'dol-mr-1' ) . esc_html__( 'Quick Connect!', 'dollie' ); ?>
		</a>
	</div>
</div>

<div class="dol-my-6">
	<?php

	dollie()->load_template(
		'notice',
		[
			'type'    => 'info',
			'icon'    => 'fas fa-tachometer',
			'title'   => sprintf( __( 'Your site is running PHP version %s', 'dollie' ), $container->get_php_version() ),
			'message' => sprintf( __( 'Your site is running PHP %s.', 'dollie' ), $container->get_php_version() ),
		],
		true
	);

	?>
</div>
