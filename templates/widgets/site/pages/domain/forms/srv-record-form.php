<form method="POST" action="">
	<div class="dol-flex dol-flex-wrap dol--mx-2">
		<div class="dol-w-5/12 dol-px-2">
			<label for="dol-srv-hostname" class="dol-text-xs dol-uppercase dol-mb-2"><?php esc_html_e( 'Hostname' ); ?></label>
			<input type="text" name="srv-hostname" id="dol-srv-hostname" placeholder="<?php esc_html_e( 'e.g. _service._protocol', 'dollie' ); ?>">
		</div>
		<div class="dol-w-5/12 dol-px-2">
			<label for="dol-svr-redirect" class="dol-text-xs dol-uppercase dol-mb-2"><?php esc_html_e( 'Will direct to' ); ?></label>
			<input type="text" name="svr-redirect" id="dol-svr-redirect" placeholder="<?php esc_html_e( 'Enter @ or hostname', 'dollie' ); ?>">
		</div>
		<div class="dol-w-2/12 dol-px-2">
			<label for="dol-svr-port" class="dol-text-xs dol-uppercase dol-mb-2"><?php esc_html_e( 'Port' ); ?></label>
			<input type="text" name="svr-port" id="dol-svr-port" placeholder="<?php esc_html_e( 'e.g. 5060', 'dollie' ); ?>">
		</div>
	</div>
	<div class="dol-flex dol-flex-wrap dol--mx-2 dol-mt-2">
		<div class="dol-w-2/12 dol-px-2">
			<label for="dol-srv-priority" class="dol-text-xs dol-uppercase dol-mb-2"><?php esc_html_e( 'Priority' ); ?></label>
			<input type="text" name="srv-priority" id="dol-srv-priority" placeholder="<?php esc_html_e( 'e.g. 10', 'dollie' ); ?>">
		</div>
		<div class="dol-w-2/12 dol-px-2">
			<label for="dol-srv-weight" class="dol-text-xs dol-uppercase dol-mb-2"><?php esc_html_e( 'Weight' ); ?></label>
			<input type="text" name="srv-weight" id="dol-srv-weight" value="" placeholder="<?php esc_html_e( 'e.g. 100', 'dollie' ); ?>">
		</div>
		<div class="dol-w-2/12 dol-px-2">
			<label for="dol-srv-ttl" class="dol-text-xs dol-uppercase dol-mb-2"><?php esc_html_e( 'TTL (seconds)' ); ?></label>
			<input type="text" name="srv-ttl" id="dol-srv-ttl" value="43200" placeholder="<?php esc_html_e( 'Enter TTL', 'dollie' ); ?>">
		</div>
		<div class="dol-w-2/12 dol-px-2 dol-flex dol-items-end">
			<button type="submit" class="dol-w-full dol-bg-primary-500 dol-border-0 dol-text-white">
				<?php esc_html_e( 'Create', 'dollie' ); ?>
			</button>
		</div>
	</div>
</form>
