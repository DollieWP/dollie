<?php

namespace DOLLIE_SETUP\Admin\Upgrades;

use DOLLIE_SETUP\Upgrades\Upgrade_Registry;
use DOLLIE_SETUP\Admin\Upgrades\List_Table;

/**
 * Setup sub-menu page for Upgrades.
 *
 * @return void
 */
function setup_upgrades_page()
{
	$subpage = add_submenu_page(
		'dollie_setup',
		esc_html__('Upgrades', 'commons-in-a-box'),
		esc_html__('Upgrades', 'commons-in-a-box'),
		'install_plugins',
		'dollie_setup-upgrades',
		__NAMESPACE__ . '\\upgrades_page'
	);

	add_action("admin_print_scripts-{$subpage}", __NAMESPACE__ . '\\enqueue_assets');
}
add_action('dollie_setup_admin_menu', __NAMESPACE__ . '\\setup_upgrades_page');

/**
 * Load upgrade page assets.
 *
 * @return void
 */
function enqueue_assets()
{
	wp_enqueue_style(
		'dollie_setup-upgrade-styles',
		dollie_setup()->plugin_url('assets/css/upgrades.css'),
		[],
		dollie_setup()->version
	);

	wp_enqueue_script(
		'dollie_setup-upgrade-script',
		dollie_setup()->plugin_url('assets/js/upgrades.js'),
		['jquery'],
		dollie_setup()->version,
		true
	);

	wp_localize_script('dollie_setup-upgrade-script', 'DOLLIE_SETUPUpgrades', [
		'ajaxUrl'  => admin_url('admin-ajax.php'),
		'nonce'    => wp_create_nonce('dollie_setup-upgrades'),
		'upgrade'  => isset($_GET['id']) ? sanitize_key($_GET['id']) : null,
		'delay'    => 0,
		'text'     => [
			'processing' => esc_html__('Processing...', 'commons-in-a-box'),
			'start'      => esc_html__('Start', 'commons-in-a-box'),
		]
	]);
}

/**
 * Render "Upgrades" page.
 *
 * @return void
 */
function upgrades_page()
{
	$action = isset($_GET['action']) ? $_GET['action'] : 'list';
	dollie_setup_get_template_part('wrapper-header');
?>
	<div class="wrap">
		<h2><?php esc_html_e('Upgrades', 'commons-in-a-box'); ?></a></h2>
		<?php if ($action === 'view') : ?>
			<?php upgrades_view(); ?>
		<?php else : ?>
			<?php upgrades_list_table(); ?>
		<?php endif; ?>
	</div>
<?php
	dollie_setup_get_template_part('wrapper-footer');
}

/**
 * Render "Upgrades" list table.
 *
 * @return void
 */
function upgrades_list_table()
{
	require DOLLIE_SETUP_PLUGIN_DIR . 'admin/upgrades/list-table.php';

	$list_table = new List_Table();
?>
	<form method="get">
		<?php $list_table->prepare_items(); ?>
		<?php $list_table->display(); ?>
	</form>
<?php
}

/**
 * Render "Upgrades" singular view.
 *
 * @return void
 */
function upgrades_view()
{
	$id       = isset($_GET['id']) ? sanitize_key($_GET['id']) : null;
	$registry = Upgrade_Registry::get_instance();
	$is_bulk  = $id === 'all';

	if ($is_bulk) {
		$upgrades = $registry->get_all_registered();

		/** @var \DOLLIE_SETUP\Upgrades\Upgrade */
		$upgrade = !empty($upgrades) ? reset($upgrades) : null;
	} else {
		/** @var \DOLLIE_SETUP\Upgrades\Upgrade */
		$upgrade = $registry->get_registered($id);
	}

	if (!$upgrade) {
		esc_html_e('Upgrade doesn\'t exists!', 'commons-in-a-box');
		return;
	}

	$name       = $is_bulk ? __('Bulk upgrade', 'commons-in-a-box') : $upgrade->name;
	$percentage = $upgrade->get_percentage();
	$style      = $percentage > 0 ? 'style="width: ' . $percentage . '%"' : '';
	$go_back    = dollie_setup_admin_prop('url', 'admin.php?page=dollie_setup-upgrades');
?>
	<div class="dollie_setup-upgrade">
		<h3><?php echo esc_html($name); ?></h3>
		<div class="dollie_setup-upgrade-main">
			<ul class="dollie_setup-upgrade-stats">
				<?php if ($is_bulk) : ?>
					<li>
						<strong><?php esc_html_e('Name:', 'commons-in-a-box'); ?></strong> <span id="dollie_setup-upgrade-name"><?php echo esc_html($upgrade->name); ?></span>
					</li>
				<?php endif; ?>
				<li>
					<strong><?php esc_html_e('Total:', 'commons-in-a-box'); ?></strong> <span id="dollie_setup-upgrade-total"><?php echo $upgrade->get_items_count(); ?></span>
				</li>
				<li>
					<strong><?php esc_html_e('Processed:', 'commons-in-a-box'); ?></strong> <span id="dollie_setup-upgrade-processed"><?php echo $upgrade->get_processed_count(); ?></span> <span id="dollie_setup-upgrade-percentage">(<?php echo $percentage; ?>%)</span>
				</li>
			</ul>
			<div class="dollie_setup-upgrade-progress-bar">
				<div class="dollie_setup-upgrade-progress-bar-inner" <?php echo $style; ?>></div>
			</div>
		</div>
		<div class="dollie_setup-upgrade-actions">
			<button class="button-primary" id="dollie_setup-upgrade-start"><?php esc_html_e('Start', 'commons-in-a-box'); ?></button>
			<button class="button" id="dollie_setup-upgrade-pause"><?php esc_html_e('Pause', 'commons-in-a-box'); ?></button>
		</div>
	</div>
	<p>
		<a href="<?php echo esc_url($go_back); ?>" class="button button-primary"><?php esc_html_e('Go back', 'commons-in-a-box'); ?></a>
	</p>
<?php
}
