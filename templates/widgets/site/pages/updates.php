<?php

if ( ! isset( $container ) ) {
	$container = dollie()->get_container();
}

$plugins = $container->get_plugins( true );
$themes  = $container->get_themes( true );

?>

<h2 class="dol-text-gray-500 dol-uppercase dol-tracking-wide dol-mb-5 dol-text-xl">
	<?php esc_html_e( 'Updates', 'dollie' ); ?>
</h2>

<div class="dol-mb-6">
	<?php esc_html_e( 'Here are all your installed plugins and themes. You can update them straight from here. This list is updated every 15 minutes.', 'dollie' ); ?>
</div>

<div class="dol-notification-success dol-hidden dol-text-sm dol-text-white dol-bg-green-500 dol-px-4 dol-py-2 dol-rounded dol-mb-3">
	<?php esc_html_e( 'Your site\'s plugins and themes are being updated.', 'dollie' ); ?>
</div>

<div class="dol-notification-error dol-hidden dol-text-sm dol-text-white dol-bg-red-500 dol-px-4 dol-py-2 dol-rounded dol-mb-3">
	<?php esc_html_e( 'Sorry, there was an error while starting this task! Please try again later.', 'dollie' ); ?>
</div>

<form id="dollie-update-assets" method="POST" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
	<ul class="dol-list-none dol-p-0 dol-m-0 dol-mr-4 dol-divide-solid dol-divide-y dol-divide-gray-300 dol-border dol-border-solid dol-border-gray-300 dol-rounded">
		<li class="dol-resource-entry dol-flex dol-items-center dol-p-4">
			<input type="checkbox" class="dol-resource-site" value="" name="">
			<div class="dol-ml-4 dol-w-full dol-grid dol-grid-cols-4 dol-font-bold dol-text-sm">
				<div class=""><?php esc_html_e( 'Name', 'dollie' ); ?></div>
				<div>
					<?php esc_html_e( 'Type', 'dollie' ); ?>
				</div>
				<div>
					<?php esc_html_e( 'Version', 'dollie' ); ?>
				</div>
				<div class="dol-text-right">
					<?php esc_html_e( 'Needs update?', 'dollie' ); ?>
				</div>
			</div>
		</li>
		<?php foreach ( $plugins as $key => $plugin ) : ?>
			<li class="dol-resource-entry">
				<label class="dol-flex dol-items-center dol-p-4">
					<input type="checkbox" class="dol-resource-site" value="<?php echo esc_attr( $plugin['slug'] ); ?>" name="plugins[<?php echo esc_attr( $key ); ?>]" <?php checked( $plugin['update'] ); ?>>
					<div class="dol-ml-4 dol-w-full dol-grid dol-grid-cols-4">
						<div class="dol-text-sm dol-font-semibold"><?php echo esc_html( $plugin['name'] ); ?></div>
						<div>
							<span class="ml-2 dol-text-sm dol-px-2 dol-py-0.5 dol-rounded dol-bg-yellow-100 dol-text-yellow-700"><?php esc_html_e( 'Plugin', 'dollie' ); ?></span>
						</div>
						<div class="dol-text-sm">
							<?php printf( __( 'Version %s', 'dollie' ), $plugin['version'] ); ?>
						</div>
						<div class="dol-text-right">
							<?php if ( $plugin['update'] ) : ?>
								<span class="ml-2 dol-text-sm dol-px-2 dol-py-0.5 dol-rounded dol-bg-primary-500 dol-text-white"><?php esc_html_e( 'Update available', 'dollie' ); ?></span>
							<?php else : ?>
								<span class="ml-2 dol-text-sm dol-px-2 dol-py-0.5 dol-rounded dol-bg-gray-200"><?php esc_html_e( 'No update available', 'dollie' ); ?></span>
							<?php endif; ?>
						</div>
					</div>
				</label>
			</li>
		<?php endforeach; ?>
		<?php foreach ( $themes as $key => $theme ) : ?>
			<li class="dol-resource-entry">
				<label class="dol-flex dol-items-center dol-p-4">
					<input type="checkbox" class="dol-resource-site" value="<?php echo esc_attr( $theme['slug'] ); ?>" name="themes[<?php echo esc_attr( $key ); ?>]" <?php checked( $theme['update'] ); ?>>
					<div class="dol-ml-4 dol-w-full dol-grid dol-grid-cols-4">
						<div class="dol-text-sm dol-font-semibold"><?php echo esc_html( $theme['name'] ); ?></div>
						<div>
							<span class="ml-2 dol-text-sm dol-px-2 dol-py-0.5 dol-rounded dol-bg-yellow-100 dol-text-yellow-700"><?php esc_html_e( 'Theme', 'dollie' ); ?></span>
						</div>
						<div class="dol-text-sm">
							<?php printf( __( 'Version %s', 'dollie' ), $theme['version'] ); ?>
						</div>
						<div class="dol-text-right">
							<?php if ( $theme['update'] ) : ?>
								<span class="ml-2 dol-text-sm dol-px-2 dol-py-0.5 dol-rounded dol-bg-primary-500 dol-text-white"><?php esc_html_e( 'Update available', 'dollie' ); ?></span>
							<?php else : ?>
								<span class="ml-2 dol-text-sm dol-px-2 dol-py-0.5 dol-rounded dol-bg-gray-200"><?php esc_html_e( 'No update available', 'dollie' ); ?></span>
							<?php endif; ?>
						</div>
					</div>
				</label>
			</li>
		<?php endforeach; ?>
	</ul>

	<input type="hidden" name="container" value="<?php echo esc_attr( $container->get_id() ); ?>">
	<input type="hidden" name="action" value="dollie_update_assets">
	<input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'dollie_update_assets' ); ?>">

	<button type="submit" class="dol-mt-6 dol-px-4 dol-py-2 dol-bg-primary-500 hover:dol-bg-primary-600 dol-border-0 dol-rounded dol-text-white dol-text-sm focus:dol-outline-none focus:dol-bg-primary-600">
		<?php echo dollie()->icon()->task( 'dol-mr-2' ); ?><?php esc_html_e( 'Update', 'dollie' ); ?>
	</button>
</form>
