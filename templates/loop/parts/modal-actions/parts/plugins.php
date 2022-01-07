<div class="dol-plugins-list dol-mt-4">
	<?php if ( empty( $plugins_data ) ) : ?>
		<?php esc_html_e( 'There are no plugins available for the selected sites!', 'dollie' ); ?>
	<?php else : ?>
		<ul class="dol-list-none dol-p-0 dol-m-0 dol-mr-4 dol-divide-solid dol-divide-y dol-divide-gray-300 dol-border dol-border-solid dol-border-gray-300 dol-rounded">
			<?php foreach ( $plugins_data as $key => $plugin ) : ?>
				<li class="dol-plugin-entry dol-px-4">
					<div class="dol-flex dol-justify-between dol-items-center">
						<label class="dol-flex dol-items-center dol-py-4">
							<input type="checkbox" class="dol-plugin-item" value="<?php echo esc_attr( $plugin['name'] ); ?>" name="" checked="checked">
							<span class="dol-rounded-full dol-bg-gray-200 dol-text-gray-700 dol-px-2 dol-py-1 dol-ml-4 dol-text-xs dol-leading-none"><?php echo esc_html( count( $plugin['sites'] ) ); ?></span>
							<span class="dol-ml-2 dol-font-bold"><?php echo esc_html( $plugin['title'] ); ?></span>
						</label>
						<span class="dol-toggle-plugin-details hover:dol-cursor-pointer dol-text-gray-600" 
							data-item="dol-plugin-content-<?php echo esc_attr( $key ); ?>">
							<span class="dol-open"><i class="fas fa-angle-right"></i></span>
							<span class="dol-close dol-hidden"><i class="fas fa-angle-up"></i></span>
						</span>
					</div>
					<div id="dol-plugin-content-<?php echo esc_attr( $key ); ?>" class="dol-plugin-items dol-hidden dol-border-0 dol-border-t dol-border-solid dol-border-gray-100 dol-p-2">
						<ul class="dol-list-none dol-p-0 dol-m-0 dol-ml-2">
							<?php foreach ( $plugin['sites'] as $id => $site ) : ?>
								<li>
									<label class="dol-flex dol-items-center dol-px-4 dol-py-2">
										<input type="checkbox" class="dol-plugin-site" value="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $plugin['name'] ); ?>" checked="checked">
										<span class="dol-ml-2 dol-text-sm"><?php echo esc_html( $site['title'] ); ?></span>
										<span class="dol-text-xs dol-ml-2">
											<?php
												printf(
													'(<a href="%s" target="_blank">%s</a>)',
													$site['url'],
													$site['url']
												)
											?>
										</span>
									</label>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
