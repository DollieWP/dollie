<div class="notice dollie-notice dollie-notice-error">
	<div class="dollie-inner-message">
		<div class="dollie-message-center">
			<h3><?php esc_html_e( 'Dollie Error - No active subscription', 'dollie' ); ?> </h3>
			<p><?php esc_html_e( 'It seems like your Dollie Subscription is expired or inactive. Please note that your clients won\'t be able to deploy until you activate your subscription.', 'dollie' ); ?></p>
		</div>

		<?php
		printf(
			'<a href="https://partners.getdollie.com/?redirect=my-account/subscriptions/" class="button button-primary" target="_blank">%s</a>',
			esc_html__( 'Update Subscription', 'dollie' )
		);

		printf(
			'<a href="%s" class="button" style="margin-left: 10px;">%s</a>',
			esc_url( admin_url() . '?wpd_check_subscription' ),
			esc_html__( 'Re-Check Subscription Status', 'dollie' )
		);
		?>
	</div>
</div>
