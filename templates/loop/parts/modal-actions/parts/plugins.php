<div class="dol-plugins-list dol-mt-4">
	<?php if ( empty( $plugins_data ) ) : ?>
		<?php esc_html_e( 'There are no plugins available for the selected sites!', 'dollie' ); ?>
	<?php else : ?>
		<ul class="dol-list-none dol-p-0 dol-m-0 dol-mr-4">
			<?php foreach ( $plugins_data as $id => $data ) : ?>
				<li class="dol-mb-4">
					<div class="dol-border-0 dol-border-l-4 dol-border-solid dol-border-gray-100 dol-overflow-hidden">
						<div class="dol-font-bold dol-px-4 dol-py-2 dol-bg-gray-100">
							<?php echo esc_html( $data['site_title'] ); ?>
							<span class="dol-text-xs dol-font-normal">
								<?php
									printf(
										'(<a href="%s" target="_blank">%s</a>)',
										$data['url'],
										$data['url']
									)
								?>
							</span>
						</div>
						<div class="dol-grid dol-grid-cols-5 dol-gap-4 dol-p-4">
							<?php foreach ( $data['plugins'] as $plugin ) : ?>
								<label class="dol-flex dol-items-center dol-border dol-border-solid dol-border-gray-100 dol-rounded dol-px-4 dol-py-2">
									<input type="checkbox" value="<?php echo esc_attr( $plugin['name'] ); ?>" name="<?php echo esc_attr( $id ); ?>" checked="checked">
									<span class="dol-ml-2 dol-text-xs"><?php echo esc_html( $plugin['title'] ); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
