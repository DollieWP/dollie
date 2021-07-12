<?php if ( '' === $data['site_data']['Installed Themes'] ) : ?>
	<?php dollie()->could_not_connect_message(); ?>
<?php else : ?>

	<h2 class="dol-text-gray-500 text-s dol-font-small dol-uppercase dol-tracking-wide dol-mb-5 dol-text-xl">
		<?php esc_html_e( 'Active Theme', 'dollie' ); ?>
	</h2>

	<div class="dol-rounded dol-overflow-hidden dol-border <?php do_action( 'dol_add_widget_classes' ); ?> dol-mb-12">
		<div class="dol-p-4 lg:dol-px-8 lg:dol-py-4 dol-bg-primary-600 dol-border-0 dol-border-b">
			<div class="dol-flex dol-flex-wrap">
				<div class="dol-w-full lg:dol-w-3/6">
					<h4 class="dol-m-0 dol-p-0 dol-font-bold dol-text-base md:dol-text-xl dol-text-white">
						<?php echo esc_html( $data['site_data']['Theme Name'] ); ?>
						<span class="dol-text-sm">
							<?php echo esc_html( $data['site_data']['Theme Version'] ); ?>
						</span>
					</h4>
				</div>
				<div class="dol-w-full lg:dol-w-3/6 lg:dol-text-right dol-mt-1 lg:dol-mt-0">
					<a class="dol-text-sm dol-text-primary-200 hover:dol-text-primary-300" target="_blank" href="<?php echo esc_url( $data['site_data']['Theme AuthorURI'] ); ?>">
						<i class="fas fa-user dol-text-primary-200 dol-mr-2"></i>
						<?php esc_html_e( 'Developed by', 'dollie' ); ?>
						<?php echo esc_html( $data['site_data']['Theme Author'] ); ?>
					</a>
				</div>
			</div>
		</div>
		<div class="dol-p-4 lg:dol-px-8 lg:dol-py-6 ">
			<div class="dol-flex dol-flex-wrap dol--mx-4 dol-my-2">
				<div class="dol-w-full md:dol-w-2/5 lg:dol-w-1/3 dol-px-4">
					<div class="dol-border dol-border-solid dol-border-primary-50 dol-rounded dol-overflow-hidden">
						<img class="dol-block" src="<?php echo esc_url( get_post_meta( get_the_ID(), 'wpd_installation_site_theme_screenshot', true ) ); ?>" alt="<?php echo esc_attr( $data['container_details']['Name'] ); ?>">
					</div>
				</div>
				<div class="dol-w-full md:dol-w-3/5 lg:dol-w-2/3 dol-px-4 dol-mt-4 md:dol-mt-0 dol-text-sm md:dol-text-base">
					<span><?php echo esc_html( $data['site_data']['Theme Description'] ); ?></span>
				</div>
			</div>
		</div>
	</div>

	<h2 class="dol-text-gray-500 text-s dol-font-small dol-uppercase dol-tracking-wide dol-mb-5 dol-text-xl">
		<?php esc_html_e( 'Other Installed Themes', 'dollie' ); ?>
	</h2>

	<div class="dol-mt-5 dol-grid dol-grid-cols-1 dol-gap-5 sm:dol-grid-cols-1 lg:dol-grid-cols-1">
		<div class="dol-overflow-hidden <?php do_action( 'dol_add_widget_classes' ); ?>">
			<div class="dol-px-4 dol-py-5 sm:dol-p-6">
				<div class="dol-flex dol-items-center">
					<div class="dol-ml-5 dol-w-0 dol-flex-1">
						<ul class="dol-list-none dol-p-0 dol-m-0">
							<?php
							$installed_themes = preg_split( '/(\.\s?|,\s?)/', $data['site_data']['Installed Themes'], -1, PREG_SPLIT_NO_EMPTY );
							?>
							<?php foreach ( $installed_themes as $theme ) : ?>
								<li>
									<i class="fas fa-angle-right dol-mr-1"></i>
									<?php echo esc_html( $theme ); ?>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>



<?php endif; ?>
