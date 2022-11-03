<div id="dol-modal-id-filters" class="dol-custom-modal">
	<div class="dol-flex dol-justify-center dol-items-center dol-w-full dol-h-full">
		<div class="dol-bg-white dol-rounded dol-w-2/6 dol-p-6">
			<div class="dol-mb-4">
				<div class="dol-flex dol-justify-between dol-items-center">
					<div class="dol-font-bold dol-text-2xl">
						<?php esc_html_e( 'Filters', 'dollie' ); ?>
					</div>
					<div class="">
						<span class="dol-modal-close dol-cursor-pointer">
							<?php echo dollie()->icon()->close(); ?>
						</span>
					</div>
				</div>
			</div>

			<div class="dol-grid dol-grid-cols-3 dol-gap-4">
				<div>
					<label for="customer" class="dol-font-bold dol-uppercase dol-mb-1 dol-text-xs dol-text-gray-600"><?php esc_html_e( 'Customer', 'dollie' ); ?></label>
					<select id="customer" class="dol-w-full dol-bg-white dol-rounded dol-px-3 dol-py-1 dol-border-solid dol-border-gray-300 dol-text-sm dol-text-gray-700 focus:dol-border-gray-400 focus:dol-outline-none">						
						<?php
						$active_customer = isset( $_GET['customer_id'] ) ? sanitize_text_field( $_GET['customer_id'] ) : '';

						if ( ! $active_customer || ! is_numeric( $active_customer ) ) {
							$active_customer = '';
						}
						?>

						<option value="" <?php selected( '', $active_customer ); ?>><?php esc_html_e( 'None', 'dollie' ); ?></option>

						<?php foreach ( $filters['available']['customers'] as $customer ) : ?>
							<option value="<?php echo esc_attr( $customer->ID ); ?>" <?php selected( $customer->ID, (int) $active_customer ); ?>>
								<?php echo esc_html( $customer->display_name ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div>
					<label for="status" class="dol-font-bold dol-uppercase dol-mb-1 dol-text-xs dol-text-gray-600"><?php esc_html_e( 'Status', 'dollie' ); ?></label>
					<select id="status" class="dol-w-full dol-bg-white dol-rounded dol-px-3 dol-py-1 dol-border-solid dol-border-gray-300 dol-text-sm dol-text-gray-700 focus:dol-border-gray-400 focus:dol-outline-none">
						<?php
						$active_status = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';

						if ( ! in_array( $active_status, $filters['available']['statuses'], true ) ) {
							$active_status = '';
						}
						?>
						<option value="" <?php selected( '', $active_status ); ?>><?php esc_html_e( 'All', 'dollie' ); ?></option>
						
						<?php foreach ( $filters['available']['statuses'] as $status ) : ?>
							<option value="<?php echo esc_attr( $status ); ?>" <?php selected( $active_status, $status ); ?>>
								<?php echo esc_html( $status ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div>
					<label for="per-page" class="dol-font-bold dol-uppercase dol-mb-1 dol-text-xs dol-text-gray-600"><?php esc_html_e( 'Per page', 'dollie' ); ?></label>
					<select id="per-page" class="dol-w-full dol-bg-white dol-rounded dol-px-3 dol-py-1 dol-border-solid dol-border-gray-300 dol-text-sm dol-text-gray-700 focus:dol-border-gray-400 focus:dol-outline-none">
						
						<?php
						$per_page = isset( $_GET['per_page'] ) ? sanitize_text_field( $_GET['per_page'] ) : 10;

						if ( ! is_numeric( $per_page ) || $per_page < 1 ) {
							$per_page = 10;
						}
						?>

						<?php for ( $i = 10; $i <= $filters['available']['pages']; $i += 10 ) : ?>
							<option value="<?php echo esc_attr( $i ); ?>" <?php selected( $per_page, $i ); ?>>
								<?php echo esc_html( $i ); ?>
							</option>
						<?php endfor; ?>
					</select>
				</div>
			</div>

			<div class="dol-mt-4">
				<button type="button" class="dol-modal-submit dol-apply-filters dol-px-4 dol-py-2 dol-bg-primary-500 hover:dol-bg-primary-600 dol-border-0 dol-rounded dol-text-white dol-text-sm focus:dol-outline-none focus:dol-bg-primary-600">
					<?php esc_html_e( 'Apply', 'dollie' ); ?>
				</button>
			</div>
		</div>
	</div>
</div>
