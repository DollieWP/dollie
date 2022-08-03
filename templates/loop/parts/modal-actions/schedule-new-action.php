<div class="dol-mb-2 dol-text-2xl">
	<?php esc_html_e( 'Create New Scheduled Task', 'dollie' ); ?>
</div>

<div class="dol-text dol-text-base dol-text-gray-500 dol-mb-3">
	<?php sprintf( esc_html__( 'Setup sceduled tasks for the selected %s.', 'dollie' ), dollie()->string_variants()->get_site_type_plural_string() ); ?>
</div>

<div id="dol-schedules" class="dol-relative"></div>

<div id="dol-loading-schedules" class="dol-hidden" data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'dollie_get_selected_sites' ) ); ?>">
	<?php dollie()->load_template( 'parts/spinner', [ 'text' => esc_html__( 'Getting Schedule...', 'dollie' ) ], true ); ?>
</div>
