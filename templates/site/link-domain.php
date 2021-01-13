<div><strong>Point your domain to our server</strong></div>
<ul>
	<li>
		<?php esc_html_e( 'Login to Your Domain Management account. This can be CloudFlare or your domain registrar.', 'dollie' ); ?>
	</li>
	<li>
		<?php esc_html_e( 'Go to your Domain name and go to the DNS Manager.', 'dollie' ); ?>
	</li>
	<li>
		<?php
		esc_html_e( 'Replace the IP address for the "@" and "www" like the following:', 'dollie' );
		?>
	</li>
</ul>

<div class="wpd-ip-dns-block dol-bg-gray-200 dol-p-5">
	<div class="dol-grid dol-grid-cols-3 dol-gap-2">
		<div>
			<strong>TYPE</strong>
			A
			A
		</div>
		<div>
			<strong>CONTENT</strong>
			@
			www
		</div>
		<div>
			<strong>IP ADDRESS</strong>
			<?php echo esc_html( $ip ); ?><br>
			<?php echo esc_html( $ip ); ?></div></div></div>

<br>
<br>

<div class="alert alert-info col-sm-9">
	<strong><?php _e( 'Important Note: You only need to change the @ and WWW records', 'dollie' ); ?></strong><br>
	<?php
	esc_html_e( 'Your domain might have multiple DNS records set up. Make sure to change only the "@" and "www" records.', 'dollie' );
	?>
	<?php
	esc_html_e( 'If you don\'t have the records you can create them.', 'dollie' );
	?>
</div>
