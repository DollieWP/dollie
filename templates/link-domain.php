<div class="blockquote-box blockquote-success clearfix">
    <div class="square pull-left">
        <i class="fa fa-globe"></i>
    </div>
    <h4>
		<?php echo wp_kses_post( sprintf(
			__( 'Your domain <strong>%s</strong> was successfully connected!', 'dollie' ),
			$has_domain ) );
		?>
    </h4>
    <p>
		<?php esc_html_e( 'Let\'s continue with our domain setup by making your DNS change.', 'dollie' ); ?>
    </p>
</div>

<ul>
    <li>
        <?php _e( 'Login to Your Domain Management account. This can be CloudFlare or your domain registrar.', 'dollie' ); ?>
    </li>
    <li>
		<?php
		printf(
			__( 'Go to your Domain name <strong>%s</strong> and go to the DNS Manager.', 'dollie' ),
			$has_domain
		);
		?>
    </li>
    <li>
		<?php
		printf(
			__( 'Replace the IP address for the <strong>"A"</strong> <strong>%s</strong> record with the following IP address: <strong>%s</strong>', 'dollie' ),
			$has_domain,
			$ip
		);
		?>
    </li>
</ul>
<img src="https://s3.amazonaws.com/helpscout.net/docs/assets/5742f232c697917290ddb900/images/574ef6b89033604d43daab48/file-D4wzErhF62.jpg"
     alt="" width="60%" height="auto"/>

<br><br>

<div class="alert alert-info col-sm-9">
    <strong><?php _e( 'Important Note: You only need to change the A and WWW records', 'dollie' ); ?></strong><br>
	<?php
	printf(
		__( 'Your domain might have multiple DNS records set up. For example if you also have your own email address
    (yourname@%s) or subdomains (anothersite.%s). For the migration to work you only
    need to make a change to the A and (if existing) WWW record. Do not change the other records unless
    specified by our support team or in unique domain setups configured by you or your developer.', 'dollie' ),
		$has_domain,
		$has_domain
	);
	?>
</div>
