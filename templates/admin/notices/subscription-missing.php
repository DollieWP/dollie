<div class="notice dollie-notice dollie-notice-trial">
	<div class="dollie-inner-message">
		<div class="dollie-message-center">
		<h3><?php printf( __( 'Dollie Trial - Active. Launch %d more sites for free</strong>.', 'dollie' ), dollie()->subscription()->get_partner_deploy_limit() ); ?></h3>
		<p><?php esc_html_e( 'Like to launch more sites or are you ready to start selling your services to your customers?', 'dollie' ); ?></p>

		</div>

		<?php
		printf(
			'<a href="https://control.getdollie.com/?redirect=start-your-subscription" class="dol-text-sm dol-text-white hover:dol-text-white dol-inline-block dol-px-4 dol-py-2 dol-bg-secondary-500 hover:dol-bg-secondary-700 dol-rounded" target="_blank">%s</a>',
			esc_html__( 'Start Your Subscription', 'dollie' )
		);

		printf(
			'<a href="%s" class="dol-text-sm dol-text-white hover:dol-text-white dol-inline-block dol-px-4 dol-py-2 dol-bg-gray-500 hover:dol-bg-gray-700 dol-rounded" style="margin-left: 10px;">%s</a>',
			esc_url( admin_url() . '?wpd_check_subscription' ),
			esc_html__( 'Check Again', 'dollie' )
		);

		printf(
			'<a href="%s" class="dol-text-sm dol-text-white hover:dol-text-white dol-inline-block dol-px-4 dol-py-2 dol-bg-gray-500 hover:dol-bg-gray-700 dol-rounded" target="_blank" style="margin-left: 10px;">%s</a>',
			esc_url( 'https://control.getdollie.com/?redirect=support' ),
			esc_html__( 'Questions? Talk to Us!', 'dollie' )
		);
		?>
	</div>
</div>
