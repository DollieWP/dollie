<div class="dol-border dol-border-solid dol-border-primary-100 dol-rounded dol-overflow-hidden dol-my-6">
	<div class="dol-flex dol-items-center dol-bg-green-500">
		<div class="dol-p-4 lg:dol-px-8 lg:dol-py-4 dol-bg-green-600 dol-flex dol-items-center dol-justify-center">
			<?php echo dollie()->icon()->updates( 'dol-text-white dol-text-2xl' ); ?>
		</div>
		<h4 class="dol-px-4 lg:dol-px-8 lg:dol-py-4 dol-m-0 dol-p-0 dol-text-white dol-text-base md:dol-text-xl">
			<?php esc_html_e( 'Everything is up to date', 'dollie' ); ?>
		</h4>
	</div>
	<div class="dol-px-4 dol-py-2 lg:dol-px-8 lg:dol-py-6 dol-bg-gray-100">
		<div>
			<?php esc_html_e( 'There are no plugins available to update. Good job!', 'dollie' ); ?>
		</div>
	</div>
</div>
