<?php

use Dollie\Core\Modules\AccessControl;

$deploying = 'pending' === \Dollie\Core\Modules\Container::instance()->get_status($current_id);

?>
<div class="">
	<div class="<?php do_action('dol_add_widget_classes'); ?> dol-overflow-hidden dol-p-4 dol-widget-site-sidebar">
		<?php if (!$deploying) : ?>
			<?php

			$menu = [
				''                => '<i class="fas fa-columns"></i>' . __('Dashboard', 'dollie'),
				'plugins'         => '<i class="fas fa-plug"></i>' . __('Plugins', 'dollie'),
				'themes'          => '<i class="fas fa-paint-roller"></i>' . __('Themes', 'dollie'),
				'domains'         => '<i class="fas fa-globe"></i>' . __('Domains', 'dollie'),
				'backups'         => '<i class="fas fa-history"></i>' . __('Backups', 'dollie'),
				'updates'         => '<i class="fas fa-box-open"></i>' . __('Updates', 'dollie'),
				'developer-tools' => '<i class="fas fa-code"></i>' . __('Developer Tools', 'dollie'),
				'blueprints'      => '<i class="fas fa-copy"></i>' . __('Blueprints', 'dollie'),
				'migrate'         => '<i class="fas fa-truck-moving"></i>' . __('Migrate', 'dollie'),
				'delete'          => '<i class="fas fa-trash-alt"></i>' . __('Delete', 'dollie'),
			];

			$sub_page = get_query_var('sub_page');

			?>

			<div class="dol-px-3 dol-py-3 dol-mt-2">
				<ul class="dol-list-none dol-p-0 dol-m-0">
					<?php foreach ($menu as $page => $title) : ?>
						<?php

						if ('' === $page) {
							$page = 'dashboard';
						}

						if (!dollie()->in_array_r($page, AccessControl::instance()->get_available_sections())) {
							continue;
						}

						$active_class = $sub_page === $page ? ' dol-text-primary' : 'dol-font-normal dol-text-gray-400 dark:dol-text-gray-300';
						?>
						<li class="dol-m-0">
							<a class="<?php echo esc_attr($active_class); ?> dol-nav-btn dol-nav-btn-secondary dol-font-semibold dol-pt-1 dol-pb-1" href="<?php echo dollie()->get_site_url($current_id, $page); ?>">
								<?php echo $title; ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
	</div>
</div>
