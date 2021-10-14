<?php if ( ! $records || ! isset( $records['records'] ) || ! is_array( $records['records'] ) || empty( $records['records'] ) ) : ?>
	<div class="dol-text-sm"><?php esc_html_e( 'There are no DNS records or we failed to fetch them.', 'dollie' ); ?></div>
<?php else : ?>
	<div class="dol-rounded dol-overflow-hidden">
		<div class="dol-flex dol-flex-wrap dol-font-bold dol-border-0 dol-border-b dol-border-solid dol-border-gray-200 dol-bg-gray-100">
			<div class="dol-w-1/12 dol-p-2"><?php esc_html_e( 'Type', 'dollie' ); ?></div>
			<div class="dol-w-3/12 dol-p-2"><?php esc_html_e( 'Hostname', 'dollie' ); ?></div>
			<div class="dol-w-5/12 dol-p-2"><?php esc_html_e( 'Value', 'dollie' ); ?></div>
			<div class="dol-w-2/12 dol-p-2"><?php esc_html_e( 'TTL (seconds)', 'dollie' ); ?></div>
			<div class="dol-w-1/12 dol-p-2"></div>
		</div>
		<?php foreach ( $records['records'] as $record ) : ?>
			<div class="dol-dns-record-item dol-flex dol-flex-wrap dol-border-0 dol-border-b dol-border-solid dol-border-gray-100 dol-text-gray-600 dol-relative">
				<div class="dol-loader dol-mt-0" data-for="remove-dns-records">
					<div class="dol-flex dol-items-center dol-justify-center dol-h-full">
						<svg class="dol-animate-spin dol-h-10 dol-w-10 dol-text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
							<circle class="dol-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
							<path class="dol-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
						</svg>
					</div>
				</div>
				<div class="dol-w-1/12 dol-p-2"><?php echo esc_html( $record['type'] ); ?></div>
				<div class="dol-w-3/12 dol-p-2 dol-break-words"><?php echo esc_html( $record['name'] ); ?></div>
				<div class="dol-w-5/12 dol-p-2 dol-break-words">
					<?php if ( isset( $record['priority'] ) && $record['priority'] ) : ?>
						<span class="dol-bg-gray-400 dol-text-white dol-px-1 dol-text-xs dol-rounded dol-cursor-default"><?php echo esc_html( $record['priority'] ); ?></span>
					<?php endif; ?>

					<?php echo esc_html( $record['content'] ); ?>
				</div>
				<div class="dol-w-2/12 dol-p-2"><?php echo esc_html( $record['ttl'] ); ?></div>
				<div class="dol-w-1/12 dol-p-2 dol-text-center">
					<span class="dol-dns-record-remove dol-cursor-pointer dol-text-gray-500 hover:dol-text-secondary-600"
						data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" 
						data-nonce="<?php echo esc_attr( wp_create_nonce( 'dollie_remove_record' ) ); ?>"
						data-record-id="<?php echo esc_attr( $record['id'] ); ?>"
						data-container-id="<?php echo esc_attr( $container_id ); ?>">
						<i class="far fa-times"></i>
					</span>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
