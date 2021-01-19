<?php if ( '' === $data['site_data']['Installed Themes'] ) : ?>
	<?php dollie()->could_not_connect_message(); ?>
<?php else : ?>

	<h2 class="dol-title">
		<?php esc_html_e( 'Active Theme', 'dollie' ); ?>
	</h2>

	<div class="dol-rounded dol-overflow-hidden dol-border dol-border-solid dol-border-gray-200 dol-mb-12">
		<div class="dol-p-4 lg:dol-px-8 lg:dol-py-4 dol-bg-gray-200 dol-border-0 dol-border-b dol-border-solid dol-border-ash-300">
			<div class="dol-flex dol-flex-wrap">
				<div class="dol-w-full lg:dol-w-3/6">
					<h4 class="dol-m-0 dol-p-0 dol-font-bold dol-text-ash-800 dol-text-base md:dol-text-xl">
						<?php echo esc_html( $data['site_data']['Theme Name'] ); ?>
						<span class="dol-text-sm">
							<?php echo esc_html( $data['site_data']['Theme Version'] ); ?>
						</span>
					</h4>
				</div>
				<div class="dol-w-full lg:dol-w-3/6 lg:dol-text-right dol-mt-1 lg:dol-mt-0">
					<a class="dol-text-sm dol-text-ash-800" target="_blank"
					   href="<?php echo esc_url( $data['site_data']['Theme AuthorURI'] ); ?>">
						<i class="fal fa-user text-primary dol-mr-2"></i>
						<?php esc_html_e( 'Developed by', 'dollie' ); ?>
						<?php echo esc_html( $data['site_data']['Theme Author'] ); ?>
					</a>
				</div>
			</div>
		</div>
		<div class="dol-p-4 lg:dol-px-8 lg:dol-py-6 dol-bg-white">
			<div class="dol-flex dol-flex-wrap dol--mx-4 dol-my-2">
				<div class="dol-w-full md:dol-w-2/5 lg:dol-w-1/3 dol-px-4">
					<div class="dol-border dol-border-solid dol-border-primary-50 dol-rounded">
						<img class="dol-block"
							 src="<?php echo esc_url( get_post_meta( get_the_ID(), 'wpd_installation_site_theme_screenshot', true ) ); ?>"
							 alt="<?php echo esc_attr( $data['container_details']['Name'] ); ?>">
					</div>
				</div>
				<div class="dol-w-full md:dol-w-3/5 lg:dol-w-2/3 dol-px-4 dol-mt-4 md:dol-mt-0 dol-text-sm md:dol-text-base">
					<span class="dol-text-primary-400"><?php echo esc_html( $data['site_data']['Theme Description'] ); ?></span>
				</div>
			</div>
		</div>
	</div>

	<h2 class="dol-title">
		<?php esc_html_e( 'Installed Themes', 'dollie' ); ?>
	</h2>

	<?php

	$installed_themes = preg_split( '/(\.\s?|,\s?)/', $data['site_data']['Installed Themes'], - 1, PREG_SPLIT_NO_EMPTY );

	?>

	<ul class="dol-list-none dol-p-0 dol-m-0">
		<?php foreach ( $installed_themes as $theme ) : ?>
			<li>
				<i class="fal fa-angle-right dol-mr-1"></i>
				<?php echo esc_html( $theme ); ?>
			</li>
		<?php endforeach; ?>
	</ul>

<?php endif; ?>
