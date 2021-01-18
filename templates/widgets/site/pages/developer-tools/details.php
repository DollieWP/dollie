<?php $hostname = preg_replace( '#^https?://#', '', $customer_details->uri ); ?>

<h2 class="dol-title">
	<?php esc_html_e( 'Developer Details', 'dollie' ); ?>
</h2>

<?php
\Dollie\Core\Utils\Tpl::load(
	'notice',
	[
		'type'         => 'info',
		'icon'         => 'fal fa-bookmark',
		'title'        => esc_html__( 'Your SFTP Details', 'dollie' ),
		'message'      => '<a class="dol-text-brand-500 hover:dol-text-brand-600" href="sftp://' . $customer_details->containerSshUsername . ':' . $customer_details->containerSshPassword . '@' . $hostname . ':' . $customer_details->containerSshPort . '">' .
						  '<i class="fal fa-plus dol-mr-2"></i>' . esc_html__( 'Quick Connect!', 'dollie' ) . '</a>',
		'bottom_space' => true,
	],
	true
);
?>

<div class="dol-border dol-border-solid dol-border-gray-200 dol-rounded dol-overflow-hidden dol-my-6">
	<div class="dol-p-4 lg:dol-px-8 lg:dol-py-4 dol-bg-gray-200 dol-border-0 dol-border-b dol-border-solid dol-border-ash-300">
		<h4 class="dol-m-0 dol-p-0 dol-font-bold dol-text-ash-800 dol-text-base md:dol-text-xl">
			<?php esc_html_e( 'SFTP Details', 'dollie' ); ?>
		</h4>
	</div>
	<div class="dol-p-4 lg:dol-px-8 lg:dol-py-6 dol-bg-white">
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
			<?php if ( isset( $_COOKIE['wordpress_user_sw_olduser_wefoster-cookie-hash'] ) || current_user_can( 'administrator' ) ) : ?>
				<li class="dol-mt-4">
					<div class="dol-mb-2 dol-font-bold"><?php esc_html_e( 'SSH Details', 'dollie' ); ?></div>
					<pre class="dol-p-0 dol-m-0">ssh -l <?php echo esc_html( $customer_details->containerSshUsername ); ?> -p <?php echo $customer_details->containerSshPort; ?> <?php echo $hostname; ?><br>password: <?php echo $customer_details->containerSshPassword; ?></pre>
				</li>
			<?php endif; ?>
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
			'icon'    => 'fal fa-tachometer',
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
			'icon'    => 'fal fa-database',
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
			'icon'    => 'fal fa-database',
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
