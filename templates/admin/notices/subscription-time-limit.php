<div class="notice dollie-notice dollie-notice-trial">
	<div class="dollie-inner-message">
		<div class="dollie-message-center">
			<h3><?php esc_html_e( 'Dollie Trial - Expired', 'dollie' ); ?></h3>
			<p><?php esc_html_e( 'Your 21 days trial has ended. Like to launch more sites or are you ready to start selling your services to your customers?', 'dollie' ); ?></p>
		</div>

		<?php
		printf(
			'<a href="https://control.getdollie.com/?redirect=start-your-subscription" class="button button-primary" target="_blank">%s</a>',
			esc_html__( 'Start Your Access', 'dollie' )
		);

		printf(
			'<a href="%s" class="button" style="margin-left: 10px;">%s</a>',
			esc_url( admin_url() . '?wpd_check_subscription' ),
			esc_html__( 'Check Again', 'dollie' )
		);

		printf(
			'<a href="%s" class="button" target="_blank" style="margin-left: 10px;">%s</a>',
			esc_url( 'https://control.getdollie.com/?redirect=support' ),
			esc_html__( 'Questions? Talk to Us!', 'dollie' )
		);
		?>
	</div>
</div>
