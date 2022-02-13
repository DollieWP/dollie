<div class="dol-mb-2 dol-text-2xl">
	<?php esc_html_e( 'Create New Schedule', 'dollie' ); ?>
</div>

<div class="dol-text dol-text-base dol-text-gray-500 dol-mb-3">
	<?php esc_html_e( 'Setup scheduled actions for the selected sites.', 'dollie' ); ?>
</div>

<div id="dol-schedules" class="dol-relative"></div>

<div id="dol-loading-schedules" class="dol-hidden"
		data-ajax-url="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>"
		data-nonce="<?php echo esc_attr( wp_create_nonce( 'dollie_get_selected_sites' ) ); ?>">
	<?php dollie()->load_template( 'helpers/spinner', [ 'text' => esc_html__( 'Fetching template...', 'dollie' ) ], true ); ?>
</div>
