<form method="POST" action="">
	<div class="dol-flex dol-flex-wrap dol--mx-2">
		<div class="dol-w-4/12 dol-px-2">
			<label for="dol-ns-hostname" class="dol-text-xs dol-uppercase dol-mb-2"><?php esc_html_e( 'Hostname' ); ?></label>
			<input type="text" name="ns-hostname" id="dol-ns-hostname" placeholder="<?php esc_html_e( 'Enter @ or hostname', 'dollie' ); ?>">
		</div>
		<div class="dol-w-4/12 dol-px-2">
			<label for="dol-ns-alias" class="dol-text-xs dol-uppercase dol-mb-2"><?php esc_html_e( 'Will redirect to' ); ?></label>
			<input type="text" name="ns-alias" id="dol-ns-alias" placeholder="<?php esc_html_e( 'Enter nameserver', 'dollie' ); ?>">
		</div>
		<div class="dol-w-2/12 dol-px-2">
			<label for="dol-ns-ttl" class="dol-text-xs dol-uppercase dol-mb-2"><?php esc_html_e( 'TTL (seconds)' ); ?></label>
			<input type="text" name="ns-ttl" id="dol-ns-ttl" value="86400" placeholder="<?php esc_html_e( 'Enter TTL', 'dollie' ); ?>">
		</div>
		<div class="dol-w-2/12 dol-px-2 dol-flex dol-items-end">
			<button type="submit" class="dol-w-full dol-bg-primary-500 dol-border-0 dol-text-white">
				<?php esc_html_e( 'Create', 'dollie' ); ?>
			</button>
		</div>
	</div>
</form>
