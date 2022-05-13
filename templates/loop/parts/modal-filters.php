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

			<div class="dol-g">
				<div class="">
					<label for="per-page" class="dol-font-bold dol-uppercase dol-mb-1 dol-text-xs dol-text-gray-600"><?php esc_html_e( 'Per page', 'dollie' ); ?></label>
					<select id="per-page" class="dol-w-20 dol-bg-white dol-rounded dol-px-3 dol-py-1 dol-border-solid dol-border-gray-300 dol-text-sm dol-text-gray-700 focus:dol-border-gray-400 focus:dol-outline-none">
						<option value="" disabled><?php esc_html_e( 'View', 'dollie' ); ?></option>
						<?php
						$per_page = 10;
						if ( isset( $_GET['per_page'] ) ) {
							$per_page = sanitize_text_field( $_GET['per_page'] );
						}

						for ( $i = 10; $i <= 50; $i += 10 ) :
							?>
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
