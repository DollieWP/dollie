<form method="POST" class="dol-dns-record-form" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'dollie_create_record' ) ); ?>">
	<input type="hidden" name="type" value="AAAA">
	<input type="hidden" name="container_id" value="<?php echo esc_attr( get_the_ID() ); ?>">
	<div class="dol-flex dol-flex-wrap dol--mx-2">
		<div class="dol-w-4/12 dol-px-2">
			<label for="dol-aaaa-hostname" class="dol-text-xs dol-uppercase dol-mb-2"><?php esc_html_e( 'Hostname' ); ?></label>
			<input type="text" name="hostname" id="dol-aaaa-hostname" placeholder="<?php esc_html_e( 'Enter @ or hostname', 'dollie' ); ?>" required>
		</div>
		<div class="dol-w-4/12 dol-px-2">
			<label for="dol-aaaa-ip" class="dol-text-xs dol-uppercase dol-mb-2"><?php esc_html_e( 'IPv6' ); ?></label>
			<input type="text" name="content" id="dol-aaaa-ip" placeholder="<?php esc_html_e( 'Enter IPv6 address', 'dollie' ); ?>" required>
		</div>
		<div class="dol-w-2/12 dol-px-2">
			<label for="dol-aaaa-ttl" class="dol-text-xs dol-uppercase dol-mb-2"><?php esc_html_e( 'TTL (seconds)' ); ?></label>
			<input type="text" name="ttl" id="dol-aaaa-ttl" value="14400" placeholder="<?php esc_html_e( 'Enter TTL', 'dollie' ); ?>" required>
		</div>
		<div class="dol-w-2/12 dol-px-2 dol-flex dol-items-end">
			<button type="submit" class="dol-w-full dol-bg-primary-500 dol-border-0 dol-text-white">
				<?php esc_html_e( 'Create', 'dollie' ); ?>
			</button>
		</div>
	</div>
</form>
