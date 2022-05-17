<div class="notice dollie-notice dollie-notice-error">
	<div class="dollie-inner-message">
		<div class="dollie-message-center">
			<h3><?php esc_html_e( 'Dollie Subscription Limit Reached', 'dollie' ); ?> </h3>
			<p><?php esc_html_e( 'You have reached the maximum number of allowed deploys. Please note that you and your clients won\'t be able to deploy new sites.', 'dollie' ); ?></p>
		</div>

		<?php
		printf(
			'<a href="%s" class="button button-primary">%s</a>',
			esc_url( admin_url() . '?wpd_check_subscription' ),
			esc_html__( 'Check Subscription Status', 'dollie' )
		);

		printf(
			'<a href="%s" class="button" target="_blank" style="margin-left: 10px;">%s</a>',
			esc_url( 'https://support.getdollie.com/' ),
			esc_html__( 'Contact Support', 'dollie' )
		);
		?>
	</div>
</div>
