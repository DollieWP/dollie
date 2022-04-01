<div class="dol-w-full dol-items-center dol-px-4 dol-py-2 dol-text-base dol-leading-6 dol-rounded dol-text-white dol-bg-red-500">
	<div class="dol-font-bold"><?php esc_html_e( 'The following fields were not found in the blueprint:', 'dollie' ); ?></div>

	<ul>
		<?php foreach ( $fields as $placeholder => $status ) : ?>
			<li><?php echo $placeholder; ?></li>
		<?php endforeach; ?>
	</ul>

	<div class="dol-mt-4 dol-text-sm dol-medium">
		<?php esc_html_e( 'Make sure you add the missing fields into your blueprint or completly remove them from the customizer\'s fields and then update it.', 'dollie' ); ?>
	</div>
</div>
