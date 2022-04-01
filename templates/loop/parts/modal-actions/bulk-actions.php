<div class="dol-mb-2 dol-text-2xl">
	<?php esc_html_e( 'Bulk Actions', 'dollie' ); ?>
</div>

<div class="dol-text dol-text-base dol-text-gray-500 dol-mb-3">
	<?php printf( esc_html__( 'Quickly run tasks across multiple %s with the click of a button! Please select the action you would like to run using the form below.', 'dollie-setup' ), dollie()->string_variants()->get_site_type_plural_string() ); ?>
</div>

<div class="dol-modal-success dol-hidden dol-text-sm dol-text-white dol-bg-green-500 dol-px-4 dol-py-2 dol-rounded dol-mb-3">
	<?php esc_html_e( 'Success, the task has started!', 'dollie' ); ?>
</div>

<div class="dol-modal-error dol-hidden dol-text-sm dol-text-white dol-bg-red-500 dol-px-4 dol-py-2 dol-rounded dol-mb-3">
	<?php esc_html_e( 'Sorry, there was an error while starting this task! Please try again later.', 'dollie' ); ?>
</div>

<select name="action" id="dol-bulk-action-type" class="w-full dol-mb-4 dol-block dol-action-list dol-bg-white dol-rounded dol-px-4 dol-py-2 dol-border-solid dol-border-gray-300 dol-text-base dol-text-gray-700 focus:dol-border-gray-400 focus:dol-outline-none">
	<option value="" disabled selected><?php esc_html_e( 'Select Bulk Action', 'dollie' ); ?></option>

	<?php foreach ( dollie()->bulk_actions()->get_allowed_commands() as $type => $label ) : ?>
		<option value="<?php echo esc_attr( $type ); ?>"><?php echo $label; ?></option>
	<?php endforeach; ?>
</select>

<div id="dol-resources-list" class="dol-hidden" data-ajax-url="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'dollie_get_bulk_action_data' ) ); ?>">
	<?php dollie()->load_template( 'parts/spinner', [ 'text' => esc_html__( 'Running task...', 'dollie' ) ], true ); ?>
</div>

<div class="dol-mt-4">
	<button type="button" class="dol-send-bulk-action dol-px-4 dol-py-2 dol-bg-primary-500 hover:dol-bg-primary-600 dol-border-0 dol-rounded dol-text-white dol-text-sm focus:dol-outline-none focus:dol-bg-primary-600" data-ajax-url="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'dollie_do_bulk_action' ) ); ?>">
		<?php echo dollie()->icon()->task( 'dol-mr-2' ); ?><?php esc_html_e( 'Run Task Now', 'dollie' ); ?>
	</button>
</div>
