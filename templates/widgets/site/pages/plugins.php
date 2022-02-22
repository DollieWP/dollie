<?php if ($data['site_data']['Installed Themes'] === '') : ?>
	<?php dollie()->could_not_connect_message(); ?>
<?php else : ?>

	<?php
	$active_plugins    = $data['site_data']['Active Plugins'];
	$installed_plugins = $data['site_data']['Installed Plugins'];

	$all_plugins      = $data['site_data']['Plugin Details'];
	$inactive_plugins = array_merge(array_diff($active_plugins, $installed_plugins), array_diff($installed_plugins, $active_plugins));

	$normal_active   = array_flip($active_plugins);
	$normal_inactive = array_flip($inactive_plugins);

	$filtered_active_plugins = array_filter(
		$all_plugins,
		static function ($el) use ($normal_active) {
			return isset($normal_active[$el['Name']]);
		}
	);

	$filtered_inactive_plugins = array_filter(
		$all_plugins,
		static function ($el) use ($normal_inactive) {
			return isset($normal_inactive[$el['Name']]);
		}
	);

	$plugins_list = [];

	foreach ($filtered_active_plugins as &$active_plugin) {
		$active_plugin['Active'] = true;
		$plugins_list[]          = $active_plugin;
	}
	unset($active_plugin);

	foreach ($filtered_inactive_plugins as &$inactive_plugin) {
		$inactive_plugin['Active'] = false;
		$plugins_list[]            = $inactive_plugin;
	}
	unset($inactive_plugin);
	?>

	<h2 class="dol-text-gray-500 text-s dol-font-small dol-uppercase dol-tracking-wide dol-mb-5 dol-text-xl">
		<?php esc_html_e('Plugins', 'dollie'); ?>
	</h2>

	<div class="dol-flex dol-flex-wrap dol-border <?php do_action('dol_add_widget_classes'); ?> dol-overflow-hidden">
		<div class="dol-hidden dol-w-full md:dol-flex dol-flex-wrap dol-items-center dol-bg-primary-600 dol-p-4 lg:dol-px-8 dol-text-white">
			<div class="dol-w-5/12 lg:dol-w-6/12 dol-text-left">
				<?php _e('Plugin Name', 'dollie'); ?>
			</div>
			<div class="dol-w-2/12">
				<?php _e('Status', 'dollie'); ?>
			</div>
			<div class="dol-w-2/12 dol-text-center">
				<?php _e('Version', 'dollie'); ?>
			</div>
			<div class="dol-w-3/12 lg:dol-w-2/12 dol-text-right">
				<?php _e('Developer', 'dollie'); ?>
			</div>
		</div>
		<div class="dol-w-full dol-overflow-hidden">

			<?php
			foreach ($plugins_list as $plugin) :
			?>
				<div class="dol-w-full dol-flex dol-flex-wrap dol-items-center dol-p-4 lg:dol-px-8 dol-border-0 dol-border-b last:dol-border-0 dol-relative dol-group hover:dol-bg-ash-100">
					<div class="dol-absolute dol-w-2 dol-h-full dol-bg-gray-200 dol-left-0 dol-top-0 dol-hidden md:group-hover:dol-block"></div>
					<div class="dol-w-full dol-mb-4 md:dol-mb-0 md:dol-w-5/12 lg:dol-w-6/12">
						<span class="dol-block dol-text-ash-600 dol-text-xs dol-uppercase md:dol-hidden">
							<?php esc_html_e('Plugin', 'dollie'); ?>
						</span>
						<span class="dol-font-bold dol-block dol-truncate dol-pr-6">
							<?php echo esc_html($plugin['Name']); ?>
						</span>
					</div>
					<div class="dol-w-4/12 md:dol-w-2/12 dol-flex dol-flex-wrap dol-items-center dol-text-sm dol-text-ash-800">
						<?php
						$status_class = [
							'animated' => 'dol-bg-green-500',
							'dot'      => 'dol-bg-green-600',
						];

						if (!$plugin['Active']) {
							$status_class = [
								'animated' => 'dol-bg-red-500',
								'dot'      => 'dol-bg-red-600',
							];
						}
						?>
						<span class="dol-flex dol-h-2 dol-w-2 dol-relative dol-mr-2">
							<span class="dol-animate-ping dol-absolute dol-inline-flex dol-h-full dol-w-full dol-rounded-full dol-opacity-75 <?php echo esc_attr($status_class['animated']); ?>"></span>
							<span class="dol-relative dol-inline-flex dol-rounded-full dol-h-2 dol-w-2 <?php echo esc_attr($status_class['dot']); ?>"></span>
						</span>
						<?php
						if ($plugin['Active']) {
							esc_html_e('Active', 'dollie');
						} else {
							esc_html_e('Disabled', 'dollie');
						}
						?>
					</div>
					<div class="dol-w-4/12 md:dol-w-2/12 dol-text-center dol-text-sm dol-text-ash-700">
						<?php echo esc_html($plugin['Version']); ?>
					</div>
					<div class="dol-w-4/12 md:dol-w-3/12 lg:dol-w-2/12 dol-text-right">
						<a class="dol-inline-block md:dol-px-4 md:dol-py-2 md:dol-bg-secondary dol-text-white md:dol-text-white dol-text-sm md:hover:dol-text-white md:hover:dol-bg-secondary-600 dol-rounded" href="<?php echo esc_url($plugin['PluginURI']); ?>" target="_blank">
							<?php echo dollie()->get_icon_arrow_right(); ?>
							<span class="md:dol-hidden lg:dol-inline-block"><?php esc_html_e('Visit', 'dollie'); ?></span>
						</a>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

<?php endif; ?>
