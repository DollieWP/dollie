<?php $hostname = preg_replace( '#^https?://#', '', $customer_details->uri ); ?>

<h2 class="dol-text-gray-500 text-s dol-font-small dol-uppercase dol-tracking-wide dol-mb-5 dol-text-xl">
	<?php esc_html_e( 'Developer Details', 'dollie' ); ?>
</h2>

<?php
\Dollie\Core\Utils\Tpl::load(
	'notice',
	[
		'type'         => 'info',
		'icon'         => 'fas fa-bookmark',
		'title'        => esc_html__( 'Your SFTP Details', 'dollie' ),
		'message'      => '<a class="dol-text-brand-500 hover:dol-text-brand-600" href="sftp://' . $customer_details->containerSshUsername . ':' . $customer_details->containerSshPassword . '@' . $hostname . ':' . $customer_details->containerSshPort . '">' .
						  '<i class="fas fa-plus dol-mr-2"></i>' . esc_html__( 'Quick Connect!', 'dollie' ) . '</a>',
		'bottom_space' => true,
	],
	true
);
?>

<div class="dol-border <?php do_action( 'dol_add_widget_classes' ); ?> dol-overflow-hidden dol-my-6">
	<div class="dol-p-4 lg:dol-px-8 lg:dol-py-4 dol-bg-primary-600 dol-border-0 dol-border-b">
		<h4 class="dol-m-0 dol-p-0 dol-font-bold dol-text-white dol-text-base md:dol-text-xl">
			<?php esc_html_e( 'SFTP Details', 'dollie' ); ?>
		</h4>
	</div>
	<div class="dol-p-4 lg:dol-px-8 lg:dol-py-6 ">
		<div class="dol-font-bold dol-mb-2">
			<?php esc_html_e( 'Use the details below in your SFTP client to connect to the site and manage your files.', 'dollie' ); ?>
		</div>
		<ul class="dol-list-none dol-m-0 dol-p-0">
			<li>
				<div>
					<?php printf( __( 'URL: %s', 'dollie' ), $hostname ); ?>
				</div>
				<div>
					<?php printf( __( 'Port: %s', 'dollie' ), $customer_details->containerSshPort ); ?>
				</div>
			</li>
			<li>
				<div>
					<?php printf( __( 'Username: %s', 'dollie' ), $customer_details->containerSshUsername ); ?>
				</div>
				<div>
					<?php printf( __( 'Password: %s', 'dollie' ), $customer_details->containerSshPassword ); ?>
				</div>
			</li>
		</ul>
	</div>
</div>

<div class="dol-my-6">
	<?php
	$php     = explode( '.', $data['container_details']['PHP Version'] );
	$message = $php[0] === '7' ? esc_html__( 'If your site is compatible with the latest version of PHP7 we recommend upgrading to the latest PHP7 version because it provides superior performance.', 'dollie' ) : esc_html__( 'PHP 5.6 is not as fast as PHP7, but virtually all plugins and themes support it.', 'dollie' );

	\Dollie\Core\Utils\Tpl::load(
		'notice',
		[
			'type'    => 'info',
			'icon'    => 'fas fa-tachometer',
			'title'   => sprintf( __( 'Your site is running PHP version %s', 'dollie' ), $php[0] . '.' . $php[1] ),
			'message' => $message,
		],
		true
	);
	?>
</div>

<?php if ( $data['container_details']['Object Cache'] !== 'disabled' ) : ?>
	<?php
	\Dollie\Core\Utils\Tpl::load(
		'notice',
		[
			'type'    => 'warning',
			'icon'    => 'fas fa-database',
			'title'   => esc_html__( 'Redis Object Caching is Disabled', 'dollie' ),
			'message' => esc_html__( 'Object Caching can further improve performance for logged-in users or when running heavy database queries. You can search for any Redis Object Cache Plugin on Wordpress repository.' ),
			'links'   => [
				[
					'title'   => __( 'Get Redis Cache', 'dollie' ),
					'url'     => 'https://wordpress.org/plugins/redis-cache/',
					'new_tab' => true,
				],
			],
		],
		true
	);
	?>
<?php else : ?>
	<?php
	\Dollie\Core\Utils\Tpl::load(
		'notice',
		[
			'type'    => 'info',
			'icon'    => 'fas fa-database',
			'title'   => esc_html__( 'Redis Object Caching is Enabled', 'dollie' ),
			'message' => esc_html__( 'Object Caching can further improve performance for logged-in users or when running heavy database queries. Make sure to test it properly to ensure compatibility with your themes and plugins.' ),
			'links'   => [
				[
					'title' => __( 'Disable Caching', 'dollie' ),
					'url'   => dollie()->get_customer_admin_url() . 'options-general.php?page=redis-cache',
				],
			],
		],
		true
	);
	?>
<?php endif; ?>
