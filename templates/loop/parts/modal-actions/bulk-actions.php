<div class="dol-mb-2 dol-font-bold dol-text-2xl">
	<?php esc_html_e( 'Bulk Actions', 'dollie' ); ?>
</div>

<div class="dol-text dol-text-base dol-text-gray-500 dol-mb-3">
	<?php esc_html_e( 'Quickly run tasks across multiple websites with the click of a button! Please select the action you would like to run using the form below.', 'dollie' ); ?>
</div>

<div class="dol-modal-success dol-hidden dol-text-sm dol-text-white dol-bg-green-500 dol-px-4 dol-py-2 dol-rounded dol-mb-3">
	<?php esc_html_e( 'Success, the bulk action has started!', 'dollie' ); ?>
</div>

<div class="dol-modal-error dol-hidden dol-text-sm dol-text-white dol-bg-red-500 dol-px-4 dol-py-2 dol-rounded dol-mb-3">
	<?php esc_html_e( 'Sorry, there was an error while starting the bulk action! Please try again later.', 'dollie' ); ?>
</div>

<select name="action" id="dol-bulk-action-type"
		class="dol-mb-4 dol-block w-full dol-action-list dol-bg-white dol-rounded dol-px-4 dol-py-2 dol-border-solid dol-border-gray-300 dol-text-base dol-text-gray-700 focus:dol-border-gray-400 focus:dol-outline-none">
	<option value="" disabled
			selected><?php esc_html_e( 'Select Bulk Action', 'dollie' ); ?></option>

	<?php foreach ( dollie()->get_allowed_bulk_commands() as $type => $label ) : ?>
		<option value="<?php echo esc_attr( $type ); ?>"><?php echo $label; ?></option>
	<?php endforeach; ?>
</select>

<div id="dol-resources-list" class="dol-hidden"
	data-ajax-url="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>"
	data-nonce="<?php echo esc_attr( wp_create_nonce( 'dollie_get_bulk_action_data' ) ); ?>">
	<?php dollie()->load_template( 'helpers/spinner', [ 'text' => esc_html__( 'Fetching data...', 'dollie' ) ], true ); ?>
</div>

<div class="dol-mt-4">
	<button type="button"
			class="dol-send-bulk-action dol-px-4 dol-py-2 dol-bg-primary-500 hover:dol-bg-primary-600 dol-border-0 dol-rounded dol-text-white dol-text-sm focus:dol-outline-none focus:dol-bg-primary-600"
			data-ajax-url="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>"
			data-nonce="<?php echo esc_attr( wp_create_nonce( 'dollie_do_bulk_action' ) ); ?>">
		<i class="fas fa-tools dol-mr-2"></i> <?php esc_html_e( 'Run Bulk Action', 'dollie' ); ?>
	</button>
</div>
