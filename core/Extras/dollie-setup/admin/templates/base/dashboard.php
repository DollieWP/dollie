<?php //dollie_setup_get_template_part('wrapper-header');
?>

<!-- <li><a class="confirm" href="<?php echo esc_url(wp_nonce_url(self_admin_url('admin.php?page=dollie_setup&amp;dollie_setup-package=0'), 'dollie_setup_select_package')); ?>"><?php esc_html_e('Change packages', 'dollie-setup'); ?></a></li> -->
<div class="dol-min-h-full dol-mt-8 dol-pt-[100px]">
	<main class="dol-pb-8 dol--mt-24">
		<div class="dol-max-w-3xl dol-px-4 dol-mx-auto sm:dol-px-6 lg:dol-max-w-7xl lg:dol-px-8">
			<h1 class="dol-sr-only">Profile</h1>
			<!-- Main 3 column dol-grid -->
			<div class="dol-grid dol-items-start dol-grid-cols-1 dol-gap-4 lg:dol-grid-cols-3 lg:dol-gap-8">
				<!-- Left column -->
				<div class="dol-grid dol-grid-cols-1 dol-gap-4 lg:dol-col-span-2">
					<?php //dollie_setup_get_template_part( 'onboarding-form' ); ?>
					<?php dollie_setup_get_template_part('welcome-header'); ?>
					<?php dollie_setup_get_template_part('setup-complete'); ?>
					<?php dollie_setup_get_template_part('recent-sites'); ?>
					<?php dollie_setup_get_template_part('dollie-news'); ?>
				</div>

				<!-- Right column -->
				<div class="dol-grid dol-grid-cols-1 dol-gap-4">
					<?php dollie_setup_get_template_part('recent-customers'); ?>
					<?php dollie_setup_get_template_part('dollie-plugins'); ?>
				</div>
			</div>
		</div>
	</main>
</div>
<?php // dollie_setup_get_template_part('wrapper-footer');
?>
