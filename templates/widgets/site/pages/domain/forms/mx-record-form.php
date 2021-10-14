<form method="POST" class="dol-dns-record-form" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'dollie_create_record' ) ); ?>">
	<input type="hidden" name="type" value="MX">
	<div class="dol-flex dol-flex-wrap dol--mx-2">
		<div class="dol-w-4/12 dol-px-2">
			<label for="dol-mx-hostname" class="dol-text-xs dol-font-bold dol-uppercase dol-mb-2"><?php esc_html_e( 'Hostname' ); ?></label>
			<input type="text" name="hostname" id="dol-mx-hostname" placeholder="<?php esc_html_e( 'Enter @ or hostname', 'dollie' ); ?>" required>
		</div>
		<div class="dol-w-4/12 dol-px-2">
			<label for="dol-mx-server" class="dol-text-xs dol-font-bold dol-uppercase dol-mb-2"><?php esc_html_e( 'Mail server' ); ?></label>
			<input type="text" name="content" id="dol-mx-server" placeholder="<?php esc_html_e( 'e.g. aspmx.l.google.com', 'dollie' ); ?>" required>
		</div>
		<div class="dol-w-2/12 dol-px-2">
			<label for="dol-mx-priority" class="dol-text-xs dol-font-bold dol-uppercase dol-mb-2"><?php esc_html_e( 'Priority' ); ?></label>
			<input type="text" name="priority" id="dol-mx-priority" value="" placeholder="<?php esc_html_e( 'e.g. 10', 'dollie' ); ?>" required>
		</div>
		<div class="dol-w-2/12 dol-px-2">
			<label for="dol-mx-ttl" class="dol-text-xs dol-font-bold dol-uppercase dol-mb-2"><?php esc_html_e( 'TTL (seconds)' ); ?></label>
			<input type="text" name="ttl" id="dol-mx-ttl" value="14400" placeholder="<?php esc_html_e( 'Enter TTL', 'dollie' ); ?>" required>
		</div>
	</div>
	<div class="dol-mt-4">
		<button type="submit" class="dol-bg-primary-500 dol-border-0 dol-text-white">
			<?php esc_html_e( 'Create', 'dollie' ); ?>
		</button>
	</div>
</form>
