<div class="dol-mb-2 dol-text-2xl">
	<?php esc_html_e( 'Scheduled Tasks', 'dollie' ); ?>
</div>

<div class="dol-text dol-text-base dol-text-gray-500 dol-mb-3">
	<?php esc_html_e( 'Here are all your scheduled tasks.', 'dollie' ); ?>
</div>

<div id="dol-schedule-history" class="dol-relative"></div>

<div id="dol-loading-history" class="dol-hidden"
	data-ajax-url="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>"
	data-nonce="<?php echo esc_attr( wp_create_nonce( 'dollie_get_schedule_history' ) ); ?>">
	<?php dollie()->load_template( 'parts/spinner', [ 'text' => esc_html__( 'Fetching scheduled tasks...', 'dollie' ) ], true ); ?>
</div>
