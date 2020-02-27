<h3><?php _e( 'Linking Your Custom Domain to your Site!', DOLLIE_SLUG ); ?></h3>
<p>
	<?php _e( 'In order to get your custom domain to work we need to make a change to your DNS configuration, so please
    make sure you have accesss to the control panel of where you registered your domain. Usually making a
    DNS change is very easy to do and your registrar will have documentation available on how to do this (or
    simply ask support to do this for you.) Here are the instructions on the changes you need to make.', DOLLIE_SLUG ); ?>
</p>
<ul>
    <li>
        <a href="https://www.cloudflare.com/a/login" target="_blank">
			<?php _e( 'Login to Your Domain Management account', DOLLIE_SLUG ); ?>
        </a>
    </li>
    <li>
		<?php
		printf(
			__( 'Go to your Domain name <strong>%s</strong> and go to the DNS Manager', DOLLIE_SLUG ),
			$has_domain
		);
		?>
    </li>
    <li>
		<?php
		printf(
			__( 'Replace the IP address for the <strong>"A"</strong> <strong>%s</strong> record with the following IP address: <strong>%s</strong>', DOLLIE_SLUG ),
			$has_domain,
			$ip
		);
		?>
    </li>
</ul>
<img src="https://s3.amazonaws.com/helpscout.net/docs/assets/5742f232c697917290ddb900/images/574ef6b89033604d43daab48/file-D4wzErhF62.jpg"
     alt="" width="60%" height="auto"/>
<div class="alert alert-info col-sm-9">
    <strong><?php _e( 'Important Note: You only need to change the A and WWW records', DOLLIE_SLUG ); ?></strong><br>
	<?php _e( 'Your domain might have multiple DNS records set up. For example if you also have your own email address
    (yourname@yourdomain.com) or subdomains (anothersite.yourdomain.com). For the migration to work you only
    need to make a change to the A and (if existing) WWW record. Do not change the other records unless
    specified by our support team or in unique domain setups configured by you or your developer.', DOLLIE_SLUG ); ?>
</div>
