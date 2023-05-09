<div class="notice dollie-notice dollie-notice-error">
	<div class="dollie-inner-message">
		<div class="dollie-message-center">
					<h3>
						<?php esc_html_e( 'Dollie Trial - Site Limit Reached', 'dollie' ); ?>
					</h3>
					<p>
						<?php esc_html_e( 'You have reached the maximum number of allowed sites to launch via your Hub during your Trial. Start your subscription to launch more sites! Do not worry about any upfront costs, you only pay for the active sites on your platform.', 'dollie' ); ?>
					</p>
		</div>

		<?php
		printf(
			'<a href="%s" class="button button-primary">%s</a>',
			esc_url( admin_url() . '?wpd_check_subscription' ),
			esc_html__( 'Check Subscription Status', 'dollie' )
		);

		printf(
			'<a href="%s" class="button" target="_blank" style="margin-left: 10px;">%s</a>',
			esc_url( 'https://control.getdollie.com/?redirect=support' ),
			esc_html__( 'Contact Support', 'dollie' )
		);
		?>
	</div>
</div>
