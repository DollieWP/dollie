<?php if ( class_exists( 'WooCommerce' ) ) : ?>
	<ul class="dol-list-none dol-p-0 dol-m-0 dol-widget-woo-nav">
		<li class="dol-m-0">
			<span class="dol-block dol-font-bold dol-text-primary-400 dol-uppercase dol-px-2 dol-py-3">
				<?php esc_html_e( 'Your Account', 'dollie' ); ?>
			</span>
		</li>
		<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
			<li class="dol-m-0">
				<a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>"
				   class="dol-nav-btn dol-nav-btn-secondary">
					<i class="fas fa-<?php echo esc_attr( $endpoint ); ?>"></i>
					<?php echo esc_html( $label ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>
