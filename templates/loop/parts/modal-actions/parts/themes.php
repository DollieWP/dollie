<div class="dol-resources-list dol-mt-4">
	<?php if ( empty( $themes_data ) ) : ?>
		<?php sprintf( esc_html__( 'There is no theme information available for this %s', 'dollie-setup' ), dollie()->string_variants()->get_site_type_string() ); ?>
	<?php else : ?>
		<ul class="dol-list-none dol-p-0 dol-m-0 dol-mr-4 dol-divide-solid dol-divide-y dol-divide-gray-300 dol-border dol-border-solid dol-border-gray-300 dol-rounded">
			<?php foreach ( $themes_data as $key => $theme ) : ?>
				<li class="dol-resource-entry dol-px-4">
					<div class="dol-flex dol-justify-between dol-items-center">
						<label class="dol-flex dol-items-center dol-py-4">
							<input type="checkbox" class="dol-resource-item" value="<?php echo esc_attr( $theme['name'] ); ?>" name="" checked="checked">
							<span class="dol-rounded-full dol-bg-gray-200 dol-text-gray-700 dol-px-2 dol-py-1 dol-ml-4 dol-text-xs dol-leading-none"><?php echo esc_html( count( $theme['sites'] ) ); ?></span>
							<span class="dol-ml-2"><?php echo esc_html( $theme['title'] ); ?></span>
						</label>
						<span class="dol-toggle-resource-details hover:dol-cursor-pointer dol-text-gray-600" data-item="dol-resource-content-<?php echo esc_attr( $key ); ?>">
							<span class="dol-open"><?php echo dollie()->icon()->arrow_up(); ?></span>
							<span class="dol-close dol-hidden"><?php echo dollie()->icon()->arrow_down(); ?></span>
						</span>
					</div>
					<div id="dol-resource-content-<?php echo esc_attr( $key ); ?>" class="dol-resource-items dol-hidden dol-border-0 dol-border-t dol-border-solid dol-border-gray-100 dol-p-2">
						<ul class="dol-list-none dol-p-0 dol-m-0 dol-ml-2">
							<?php foreach ( $theme['sites'] as $id => $site ) : ?>
								<li>
									<label class="dol-flex dol-items-center dol-px-4 dol-py-2">
										<input type="checkbox" class="dol-resource-site" value="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $theme['name'] ); ?>" checked="checked">
										<span class="dol-ml-2 dol-text-sm"><?php echo esc_html( $site['title'] ); ?></span>
										<span class="dol-text-xs dol-ml-2">
											<?php
											printf(
												'(<a href="%s" target="_blank">%s</a>)',
												$site['url'],
												$site['url']
											);
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
