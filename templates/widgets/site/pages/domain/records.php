<?php if ( ! $records || ! isset( $records['records'] ) || empty( $records['records'] ) ) : ?>
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
		<?php foreach ( $records as $record ) : ?>
			<div class="dol-flex dol-flex-wrap dol-border-0 dol-border-b dol-border-solid dol-border-gray-100 dol-text-gray-600">
				<div class="dol-w-1/12 dol-p-2"><?php echo esc_html( $record['type'] ); ?></div>
				<div class="dol-w-3/12 dol-p-2"><?php echo esc_html( $record['name'] ); ?></div>
				<div class="dol-w-5/12 dol-p-2">
					<?php if ( isset( $record['priority'] ) && $record['priority'] ) : ?>
						<span class="dol-bg-gray-400 dol-text-white dol-px-1 dol-text-xs dol-rounded dol-cursor-default"><?php echo esc_html( $record['priority'] ); ?></span>
					<?php endif; ?>

					<?php echo esc_html( $record['content'] ); ?>
				</div>
				<div class="dol-w-2/12 dol-p-2"><?php echo esc_html( $record['ttl'] ); ?></div>
				<div class="dol-w-1/12 dol-p-2 dol-text-center">
					<span class="dol-cursor-pointer dol-text-gray-500 hover:dol-text-secondary-600"><i class="far fa-times"></i></span>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
