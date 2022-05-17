<div class="notice dollie-notice dollie-notice-error">
	<div class="dollie-inner-message">
		<div class="dollie-message-center">
			<h3><?php esc_html_e( 'No active Dollie Subscription', 'dollie' ); ?> </h3>
			<p><?php printf( __( 'It seems like you don\'t have an active Dollie Subscription. You can deploy %d more sites.', 'dollie' ), dollie()->subscription()->get_partner_deploy_limit() ); ?></p>
		</div>

		<?php
		printf(
			'<a href="https://partners.getdollie.com/?redirect=my-account/subscriptions/" class="button button-primary" target="_blank">%s</a>',
			esc_html__( 'Create Subscription', 'dollie' )
		);

		printf(
			'<a href="%s" class="button" style="margin-left: 10px;">%s</a>',
			esc_url( admin_url() . '?wpd_check_subscription' ),
			esc_html__( 'Check Again', 'dollie' )
		);

		printf(
			'<a href="%s" class="button" target="_blank" style="margin-left: 10px;">%s</a>',
			esc_url( 'https://support.getdollie.com/' ),
			esc_html__( 'Contact Support', 'dollie' )
		);
		?>
	</div>
</div>
