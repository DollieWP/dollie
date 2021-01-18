<div class="dol-border dol-border-solid dol-border-gray-200 dol-rounded dol-overflow-hidden dol-mb-6">
	<div class="dol-p-4 lg:dol-px-8 lg:dol-py-4 dol-bg-gray-200 dol-border-0 dol-border-b dol-border-solid dol-border-ash-300">
		<h4 class="dol-m-0 dol-p-0 dol-font-bold dol-text-ash-800 dol-text-base md:dol-text-xl">
			<?php esc_html_e( 'Create a backup', 'dollie' ); ?>
		</h4>
	</div>
	<div class="dol-p-4 lg:dol-px-8 lg:dol-py-6 dol-bg-white">
		<span class="dol-block dol-mb-4">
			<?php esc_html_e( 'Create a backup of your site so you can restore it anytime in the future.', 'dollie' ); ?>
		</span>
		<?php echo do_shortcode( '[dollie_form form="form_dollie_create_backup" ]' ); ?>
	</div>
</div>

<div class="dol-border dol-border-solid dol-border-gray-200 dol-rounded dol-overflow-hidden dol-mb-6">
	<div class="dol-p-4 lg:dol-px-8 lg:dol-py-4 dol-bg-gray-200 dol-border-0 dol-border-b dol-border-solid dol-border-ash-300">
		<h4 class="dol-m-0 dol-p-0 dol-font-bold dol-text-ash-800 dol-text-base md:dol-text-xl">
			<?php esc_html_e( 'Backups', 'dollie' ); ?>
		</h4>
	</div>
	<div class="dol-p-4 lg:dol-px-8 lg:dol-py-6 dol-bg-white">
		<?php
		$available_backups = dollie()->get_site_total_backups();
		?>
		<?php if ( $available_backups ) : ?>
			<span class="dol-text-lg dol-font-bold dol-block dol-mb-4">
				<?php
				printf( _n( 'You have %s backup available!', 'You have %s backups available!', $available_backups, 'dollie' ), $available_backups );
				?>
			</span>
			<?php
			$backups_list = do_shortcode( '[dollie_form form="form_dollie_list_backups"]' );
			echo $backups_list;
			?>
		<?php else : ?>
			<span><?php esc_html_e( 'No backups available.', 'dollie' ); ?></span>
		<?php endif; ?>
	</div>
</div>

<div class="dol-border dol-border-solid dol-border-gray-200 dol-rounded dol-overflow-hidden">
	<div class="dol-p-4 lg:dol-px-8 lg:dol-py-4 dol-bg-gray-200 dol-border-0 dol-border-b dol-border-solid dol-border-ash-300">
		<h4 class="dol-m-0 dol-p-0 dol-font-bold dol-text-ash-800 dol-text-base md:dol-text-xl">
			<?php esc_html_e( 'Restore history', 'dollie' ); ?>
		</h4>
	</div>
	<div class="dol-p-4 lg:dol-px-8 lg:dol-py-6 dol-bg-white">
		<?php $restores = dollie()->get_site_restores(); ?>
		<?php if ( ! empty( $restores ) ) : ?>
			<ul class="dol-list-none dol-m-0 dol-p-0">
				<?php foreach ( $restores as $restore ) : ?>
					<li>
						<?php echo $restore; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php else : ?>
			<span><?php esc_html_e( 'You have never restored your site.', 'dollie' ); ?></span>
		<?php endif; ?>
	</div>
</div>
