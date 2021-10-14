<div class="dol-rounded dol-overflow-hidden">
	<div class="dol-bg-gray-100 dol-p-4">
		<h5 class="dol-text-base dol-font-bold dol-mt-0 dol-mb-2">
			<?php esc_html_e( 'Point your domain to our server', 'dollie' ); ?>
		</h5>
		<ul class="dol-text-sm dol-list-inside dol-p-0">
			<li>
				<?php esc_html_e( 'Login to Your Domain Management account. This can be CloudFlare or your domain registrar.', 'dollie' ); ?>
			</li>
			<li>
				<?php esc_html_e( 'Go to your Domain name and go to the DNS Manager.', 'dollie' ); ?>
			</li>
			<li>
				<?php esc_html_e( 'Replace the IP address for the "@" and "www" like the following:', 'dollie' ); ?>
			</li>
		</ul>
	</div>

	<div class="dol-border-0 dol-border-b dol-border-t dol-border-solid dol-border-gray-200 dol-py-4 dol-px-10">
		<div class="dol-flex dol-flex-wrap dol-font-bold">
			<div class="dol-w-4/12"><?php esc_html_e( 'TYPE', 'dollie' ); ?></div>
			<div class="dol-w-4/12"><?php esc_html_e( 'CONTENT', 'dollie' ); ?></div>
			<div class="dol-w-4/12"><?php esc_html_e( 'IP ADDRESS', 'dollie' ); ?></div>
		</div>
		<div class="dol-flex dol-flex-wrap">
			<div class="dol-w-4/12">A</div>
			<div class="dol-w-4/12">@</div>
			<div class="dol-w-4/12"><?php echo esc_html( $ip ); ?></div>
		</div>
		<div class="dol-flex dol-flex-wrap">
			<div class="dol-w-4/12">A</div>
			<div class="dol-w-4/12">www</div>
			<div class="dol-w-4/12"><?php echo esc_html( $ip ); ?></div>
		</div>
	</div>

	<div class="dol-bg-gray-100 dol-p-4 dol-text-sm">
		<div class="dol-font-bold"><?php _e( 'Important Note: You only need to change the @ and WWW records', 'dollie' ); ?></div>
		<div>
			<?php esc_html_e( 'Your domain might have multiple DNS records set up. Make sure to change only the "@" and "www" records.', 'dollie' ); ?>
			<?php esc_html_e( 'If you don\'t have the records you can create them.', 'dollie' ); ?>
		</div>
	</div>
</div>





