<!--Menu-->
<?php
$layout = $settings['layout'];
$colors = $settings['colors'];

use Dollie\Core\Modules\AccessControl;

$deploying = 'pending' === \Dollie\Core\Modules\Container::instance()->get_status($current_id); ?>

<nav class="" x-data="{ isOpen: false }" @keydown.escape="isOpen = false" :class="{ 'dol-shadow-lg' : isOpen , '' : !isOpen}">

	<!--Toggle button (hidden on large screens)-->
	<button @click="isOpen = !isOpen" type="button" class="dol-block lg:dol-hidden dol-px-2 dol-text-gray-500 hover: focus:dol-outline-none focus:" :class="{ 'dol-transition transform-180': isOpen }">
		<svg class="dol-h-6 dol-w-6 dol-fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
			<path x-show="isOpen" fill-rule="evenodd" clip-rule="evenodd" d="M18.278 16.864a1 1 0 0 1-1.414 1.414l-4.829-4.828-4.828 4.828a1 1 0 0 1-1.414-1.414l4.828-4.829-4.828-4.828a1 1 0 0 1 1.414-1.414l4.829 4.828 4.828-4.828a1 1 0 1 1 1.414 1.414l-4.828 4.829 4.828 4.828z" />
			<path x-show="!isOpen" fill-rule="evenodd" d="M4 5h16a1 1 0 0 1 0 2H4a1 1 0 1 1 0-2zm0 6h16a1 1 0 0 1 0 2H4a1 1 0 0 1 0-2zm0 6h16a1 1 0 0 1 0 2H4a1 1 0 0 1 0-2z" />
		</svg>
	</button>

	<div class="dol-w-full dol-flex-grow lg:dol-flex lg:dol-items-center lg:dol-w-auto" :class="{ 'dol-block shadow-3xl': isOpen, 'dol-hidden': !isOpen }" @click.away="isOpen = false" x-show.transition="true">
		<div class="dol-overflow-hidden dol-widget-site-sidebar dol-widget-<?php echo $layout; ?>">
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

				if (dollie()->is_blueprint($current_id)) {
					unset($menu['domains']);
				}

				$sub_page = get_query_var('sub_page');

				?>
				<!--Menu-->
				<div class="dol-w-full dol-flex-grow lg:dol-flex lg:dol-items-center lg:dol-w-auto" :class="{ 'dol-block shadow-3xl': isOpen, 'dol-hidden': !isOpen }" @click.away="isOpen = false" x-show.transition="true">
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
							<li class="dol-m-0 dol-p-2">
								<a @click="isOpen = false" class="<?php echo esc_attr($active_class); ?> dol-nav-btn dol-nav-btn-secondary dol-font-semibold dol-pt-1 dol-pb-1" href="<?php echo dollie()->get_site_url($current_id, $page); ?>">
									<?php echo $title; ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
				</div>
		</div>
	</div>
</nav>
