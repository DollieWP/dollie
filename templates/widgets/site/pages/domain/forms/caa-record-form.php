<form method="POST" action="">
	<div class="dol-flex dol-flex-wrap dol--mx-2">
		<div class="dol-w-5/12 dol-px-2">
			<label for="dol-caa-hostname" class="dol-text-xs dol-uppercase dol-mb-2"><?php esc_html_e( 'Hostname' ); ?></label>
			<input type="text" name="caa-hostname" id="dol-caa-hostname" placeholder="<?php esc_html_e( 'Enter @ or hostname', 'dollie' ); ?>">
		</div>
		<div class="dol-w-4/12 dol-px-2">
			<label for="dol-caa-domain" class="dol-text-xs dol-uppercase dol-mb-2"><?php esc_html_e( 'Authority granted for' ); ?></label>
			<input type="text" name="caa-domain" id="dol-caa-domain" placeholder="<?php esc_html_e( 'e.g. domain.com', 'dollie' ); ?>">
		</div>
		<div class="dol-w-3/12 dol-px-2">
			<label for="dol-caa-tag" class="dol-text-xs dol-uppercase dol-mb-2"><?php esc_html_e( 'Tag' ); ?></label>
			<select name="caa-tag" id="dol-ncaas-tag">
				<option value="" selected disabled><?php esc_attr_e( 'Choose a tag', 'dollie' ); ?></option>
				<?php foreach ( dollie()->get_domain_allowed_caa_tags() as $tag ) : ?>
					<option value="<?php echo esc_html( $tag ); ?>"><?php echo esc_html( $tag ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>
	<div class="dol-flex dol-flex-wrap dol--mx-2 dol-mt-2">
		<div class="dol-w-2/12 dol-px-2">
			<label for="dol-caa-flags" class="dol-text-xs dol-uppercase dol-mb-2"><?php esc_html_e( 'Flags' ); ?></label>
			<input type="text" name="caa-flags" id="dol-caa-flags" value="0" placeholder="<?php esc_html_e( 'e.g. 0', 'dollie' ); ?>">
		</div>
		<div class="dol-w-2/12 dol-px-2">
			<label for="dol-caa-ttl" class="dol-text-xs dol-uppercase dol-mb-2"><?php esc_html_e( 'TTL (seconds)' ); ?></label>
			<input type="text" name="caa-ttl" id="dol-caa-ttl" value="3600" placeholder="<?php esc_html_e( 'Enter TTL', 'dollie' ); ?>">
		</div>
		<div class="dol-w-2/12 dol-px-2 dol-flex dol-items-end">
			<button type="submit" class="dol-w-full dol-bg-primary-500 dol-border-0 dol-text-white">
				<?php esc_html_e( 'Create', 'dollie' ); ?>
			</button>
		</div>
	</div>
</form>
