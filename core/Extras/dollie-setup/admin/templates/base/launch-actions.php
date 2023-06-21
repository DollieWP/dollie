<h2 class="dol-text-gray-500 dol-uppercase dol-tracking-wide dol-mb-1 dol-text-xl">
	<?php esc_html_e( 'Ready for Action?', 'dollie' ); ?>
</h2>

<section class="dol-grid dol-grid-cols-1 md:dol-grid-cols-2 dol-gap-4">
	<div class="dol-p-6 dol-bg-white dol-shadow dol-overflow-hidden sm:dol-rounded-md">
		<h4 class="dol-mt-0 dol-mb-2 dol-font-bold dol-text-xl"><?php esc_html_e( 'Launch Site', 'dollie' ); ?></h4>
		<div class="dol-text-gray-600 dol-mb-4 dol-text-sm">
			<?php esc_html_e( 'Launch your next site in minutes. Go to the launch form by pressing the button below.', 'dollie' ); ?>
		</div>
		<a href="<?php echo esc_url( dollie()->page()->get_launch_site_url() ); ?>"
			class="dol-inline-block dol-bg-primary dol-text-white dol-px-4 dol-py-2 dol-rounded dol-font-medium hover:dol-text-white">
			<?php esc_html_e( 'Launch New Site', 'dollie' ); ?>
		</a>
	</div>
	<div class="dol-p-6 dol-bg-white dol-shadow dol-overflow-hidden sm:dol-rounded-md">
	<h4 class="dol-mt-0 dol-mb-2 dol-font-bold dol-text-xl"><?php esc_html_e( 'Launch Blueprint', 'dollie' ); ?></h4>
		<div class="dol-text-gray-600 dol-mb-4 dol-text-sm">
			<?php esc_html_e( 'Create beautiful blueprints for your clients. Launch your blueprint, set it up and make it available for your clients.', 'dollie' ); ?>
		</div>
		<a href="<?php echo esc_url( dollie()->page()->get_launch_blueprint_url() ); ?>"
			class="dol-inline-block dol-bg-primary dol-text-white dol-px-4 dol-py-2 dol-rounded dol-font-medium hover:dol-text-white">
			<?php esc_html_e( 'Launch New Blueprint', 'dollie' ); ?>
		</a>
	</div>

</section>
