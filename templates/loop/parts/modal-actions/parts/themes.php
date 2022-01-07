<div class="dol-themes-list dol-mt-4">
	<?php if ( empty( $themes_data ) ) : ?>
		<?php esc_html_e( 'There are no themes available for the selected sites!', 'dollie' ); ?>
	<?php else : ?>
		<ul class="dol-list-none dol-p-0 dol-m-0 dol-mr-4">
			<?php foreach ( $themes_data as $id => $data ) : ?>
				<li class="dol-mb-4">
					<div class="dol-rounded dol-overflow-hidden">
						<div class="dol-font-bold dol-px-4 dol-py-2 dol-bg-gray-200"><?php echo esc_html( $data['site_title'] ); ?></div>
						<div class="dol-grid dol-grid-cols-5 dol-gap-4 dol-p-4">
							<?php foreach ( $data['themes'] as $theme ) : ?>
								<label class="dol-truncate dol-flex dol-items-center">
									<input type="checkbox" value="<?php echo esc_attr( $theme['name'] ); ?>" name="themes[<?php echo esc_attr( $id ); ?>][]" checked="checked">
									<span class="dol-ml-2 dol-text-sm"><?php echo esc_html( $theme['title'] ); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
