<h2 class="dol-text-gray-500 text-s dol-font-small dol-uppercase dol-tracking-wide dol-mb-5 dol-text-xl">
	<?php esc_html_e( 'Database Manager', 'dollie' ); ?>
</h2>

<div class="dol-border <?php do_action( 'dol_add_widget_classes' ); ?> dol-overflow-hidden dol-my-6">
	<div class="dol-p-4 lg:dol-px-8 lg:dol-py-4 dol-bg-primary-600 dol-border-0 dol-border-b">
		<h4 class="dol-m-0 dol-p-0 dol-font-bold dol-text-white dol-text-base md:dol-text-xl">
			<?php esc_html_e( 'Connection Details', 'dollie' ); ?>
		</h4>
	</div>
	<div class="dol-p-4 lg:dol-px-8 lg:dol-py-6 ">
		<div class="dol-font-bold dol-mb-2">
			<?php esc_html_e( 'You can securely access your database by using the login details below.', 'dollie' ); ?>
		</div>
		<div>
			<div class="dol-mb-1 dol-font-semibold">
				<?php _e( '<strong>Tip:</strong> Run \'<code>wp db export</code>\' in your WP-CLI terminal to quickly create a database backup!', 'dollie' ); ?>
			</div>

			<div>
				<div>
					<?php printf( __( 'Username: %s', 'dollie' ), $customer_details->id ); ?>
				</div>
				<div>
					<?php printf( __( 'Password: %s', 'dollie' ), get_post_meta( get_the_ID(), 'wpd_container_secret', true ) ); ?>
				</div>
			</div>

			<div class="dol-mt-4">
				<a class="dol-inline-block dol-text-white dol-bg-secondary-500 hover:dol-text-white hover:dol-bg-secondary-600 dol-px-6 dol-py-3 dol-rounded"
				   href="<?php echo esc_url( dollie()->get_container_url( get_the_ID() ) ) . Dollie\Core\Modules\Sites\WP::PLATFORM_PATH; ?>container/adminer/" target="_blank">
					<?php esc_html_e( 'Open Database Manager', 'dollie' ); ?>
				</a>
			</div>
		</div>
	</div>
</div>
