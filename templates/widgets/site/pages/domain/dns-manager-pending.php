<div class="dol-my-6">
	<?php
	dollie()->load_template(
		'notice',
		[
			'type'    => 'info',
			'icon'    => 'fas fa-exclamation-circle',
			'title'   => sprintf( __( 'Please hold on whilst "%s" is getting ready to be used', 'dollie' ), $domain ),
			'message' => __( 'Your domain\'s nameservers are being checked. Once we confirm all your nameservers are set correctly, we will automatically replace your website\'s URL and enable the DNS manager.', 'dollie' ),
		],
		true
	);
	?>

	<div class="dol-rounded dol-overflow-hidden dol-shadow dol-mt-6">
		<div class="dol-p-4 lg:dol-px-8 lg:dol-py-6">
			<h4 class="dol-text-lg"><?php esc_html_e( 'Your domain will get validated once we detect the following nameservers attached to it.', 'dollie' ); ?></h4>
			<ul class="dol-m-0">
				<li>pdns1.stratus5.com</li>
				<li>pdns2.stratus5.com</li>
				<li>pdns3.stratus5.com</li>
			</ul>
			<span class="dol-block dol-mb-4 dol-mt-6"><?php esc_html_e( 'Want to change the domain or have you typed in the wrong domain name? You can cancel at any time and try again!', 'dollie' ); ?></span>
			<form action="<?php echo $container->get_permalink( 'domains', [ 'remove-domain' => 'yes' ] ); ?>" method="post">
				<button name="remove_customer_dns" id="remove_customer_dns" type="submit" class="dol-px-4 dol-py-2 dol-bg-red-600 dol-text-white dol-rounded">
					<?php echo dollie()->icon()->close( 'dol-mr-1' ); ?>
					<?php esc_html_e( 'Cancel', 'dollie' ); ?>
				</button>
			</form>
		</div>
	</div>
</div>
