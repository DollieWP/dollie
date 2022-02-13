<form method="POST" class="dol-dns-record-form" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'dollie_create_record' ) ); ?>">
	<input type="hidden" name="type" value="TXT">
	<input type="hidden" name="container_id" value="<?php echo esc_attr( get_the_ID() ); ?>">
	<div class="dol-w-full">
		<label for="dol-txt-string" class="dol-text-xs dol-uppercase dol-mb-2"><?php esc_html_e( 'Value' ); ?></label>
		<input type="text" name="content" id="dol-txt-string" placeholder="<?php esc_html_e( 'Enter TXT string', 'dollie' ); ?>" required>
	</div>
	<div class="dol-flex dol-flex-wrap dol--mx-2 dol-mt-2">
		<div class="dol-w-4/12 dol-px-2">
			<label for="dol-txt-hostname" class="dol-text-xs dol-uppercase dol-mb-2"><?php esc_html_e( 'Hostname' ); ?></label>
			<input type="text" name="hostname" id="dol-txt-hostname" placeholder="<?php esc_html_e( 'Enter @ or hostname', 'dollie' ); ?>" required>
		</div>
		<div class="dol-w-2/12 dol-px-2">
			<label for="dol-txt-ttl" class="dol-text-xs dol-uppercase dol-mb-2"><?php esc_html_e( 'TTL (seconds)' ); ?></label>
			<input type="text" name="ttl" id="dol-txt-ttl" value="3600" placeholder="<?php esc_html_e( 'Enter TTL', 'dollie' ); ?>" required>
		</div>
		<div class="dol-w-2/12 dol-px-2 dol-flex dol-items-end">
			<button type="submit" class="dol-w-full dol-bg-primary-500 dol-border-0 dol-text-white">
				<?php esc_html_e( 'Create', 'dollie' ); ?>
			</button>
		</div>
	</div>
</form>
