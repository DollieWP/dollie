<div class="notice dollie-notice dollie-notice-trial">
	<div class="dollie-inner-message">
		<div class="dollie-message-center">
		<h3><?php esc_html_e( 'You have not verified your Dollie Control HQ account yet.', 'dollie' ); ?></h3>
		<p><?php esc_html_e( 'To prevent abuse of the Dollie platform we need to verify your account before you get unrestricted access to all platform features. Verification is completely free, will take less than 5 minutes and will even earn you free Cloud Credits!', 'dollie' ); ?></p>

		</div>

		<?php
		printf(
			'<a href="https://control.getdollie.com/verify-my-account/" class="button button-primary" target="_blank">%s</a>',
			esc_html__( 'Verify Your Account', 'dollie' )
		);

		printf(
			'<a href="%s" class="button" style="margin-left: 10px;">%s</a>',
			esc_url( admin_url() . '?wpd_check_subscription' ),
			esc_html__( 'Re-Check My Verification Status', 'dollie' )
		);
		?>
	</div>
</div>
