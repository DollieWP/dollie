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

	<?php

	dollie()->load_template(
		'widgets/site/pages/domain/connect/dns-ip-table',
		[
			'ip' => $ip,
		],
        true
	);
	?>

    <div class="dol-bg-gray-100 dol-p-4 dol-text-sm">
        <div class="dol-font-bold"><?php _e( 'Important Note: You only need to change the @ and WWW records', 'dollie' ); ?></div>
        <div>
			<?php esc_html_e( 'Your domain might have multiple DNS records set up. Make sure to change only the "@" and "www" records.', 'dollie' ); ?>
			<?php esc_html_e( 'If you don\'t have the records you can create them.', 'dollie' ); ?>
        </div>
    </div>
</div>




