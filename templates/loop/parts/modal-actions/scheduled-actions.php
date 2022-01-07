<div class="dol-mb-2 dol-font-bold dol-text-2xl">
	<?php esc_html_e( 'Scheduled Actions', 'dollie' ); ?>
</div>

<div class="dol-text dol-text-base dol-text-gray-500 dol-mb-3">
	<?php esc_html_e( 'Here are all your scheduled actions.', 'dollie' ); ?>
</div>

<div id="dol-schedule-history" class="dol-relative"></div>

<div id="dol-loading-history" class="dol-hidden"
	data-ajax-url="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>"
	data-nonce="<?php echo esc_attr( wp_create_nonce( 'dollie_get_schedule_history' ) ); ?>">
	<?php dollie()->load_template( 'helpers/spinner', [ 'text' => esc_html__( 'Fetching schedules...', 'dollie' ) ], true ); ?>
</div>
