<?php

if ( ! isset( $container ) ) {
	$container = dollie()->get_container();
}

$backups = $container->get_backups( true );

if ( is_wp_error( $backups ) ) {
	$backups = [];
}

?>

<div class="dol-border <?php do_action( 'dol_add_widget_classes' ); ?> dol-overflow-hidden dol-mb-6 dol-widget-create-backup">
	<div class="dol-hidden dol-w-full md:dol-flex dol-flex-wrap dol-items-center dol-bg-primary-600 dol-p-4 lg:dol-px-8">
		<h4 class="dol-m-0 dol-p-0 dol-text-white md:dol-text-xl">
			<?php esc_html_e( 'Create a backup', 'dollie' ); ?>
		</h4>
	</div>
	<div class="dol-p-4 lg:dol-px-8 lg:dol-py-6">
		<span class="dol-block dol-mb-4">
			<?php esc_html_e( 'Create a backup of your site so you can restore it anytime in the future.', 'dollie' ); ?>
		</span>
		<?php echo do_shortcode( '[dollie_form form="form_dollie_create_backup" ]' ); ?>
	</div>
</div>

<div class="dol-border <?php do_action( 'dol_add_widget_classes' ); ?> dol-overflow-hidden dol-mb-6 dol-widget-backup-list">
	<div class="dol-hidden dol-w-full md:dol-flex dol-flex-wrap dol-items-center dol-bg-primary-600 dol-p-4 lg:dol-px-8">
		<h4 class="dol-m-0 dol-p-0 md:dol-text-xl dol-text-white">
			<?php esc_html_e( 'Backups', 'dollie' ); ?>
		</h4>
	</div>
	<div class="dol-p-4 lg:dol-px-8 lg:dol-py-6">
		<?php if ( ! empty( $backups ) ) : ?>
			<span class="dol-text-lg dol-block dol-mb-4">
				<?php printf( _n( 'You have %s backup available!', 'You have %s backups available!', count( $backups ), 'dollie' ), count( $backups ) ); ?>
			</span>

			<?php echo do_shortcode( '[dollie_form form="form_dollie_list_backups"]' ); ?>
		<?php else : ?>
			<span><?php esc_html_e( 'No backups available.', 'dollie' ); ?></span>
		<?php endif; ?>
	</div>
</div>
