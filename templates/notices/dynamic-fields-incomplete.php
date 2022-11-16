<div class="dol-w-full dol-items-center dol-px-4 dol-py-2 dol-text-base dol-leading-6 dol-rounded dol-text-white dol-bg-red-500">
	<div class="dol-font-bold"><?php esc_html_e( 'The following placeholders were not found in your blueprint:', 'dollie' ); ?></div>

	<ul>
		<?php foreach ( $fields as $placeholder => $status ) : ?>
			<li><?php echo $placeholder; ?></li>
		<?php endforeach; ?>
	</ul>

	<div class="dol-mt-4 dol-text-sm dol-medium">
		<?php esc_html_e( 'Please verify that all the Placeholders you have added are actually used inside your Blueprint. You can also remove them completely and then update your Blueprint.', 'dollie' ); ?>
	</div>
</div>
