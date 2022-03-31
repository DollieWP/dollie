<?php if ( class_exists( 'WooCommerce' ) ) : ?>
	<ul class="dol-list-none dol-p-0 dol-m-0 dol-widget-woo-nav">
		<li class="dol-m-0">
			<span class="dol-block dol-text-primary-400 dol-uppercase dol-px-2 dol-py-3">
				<?php esc_html_e( 'Your Account', 'dollie' ); ?>
			</span>
		</li>
		<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
			<li class="dol-m-0">
				<a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>" class="dol-nav-btn dol-nav-btn-secondary">
					<?php
					$endpoint_label = strtolower( $label );
					$icon_name      = str_replace( ' ', '_', $endpoint_label );
					$icon_method    = 'get_icon_' . $icon_name;
					if ( method_exists( dollie(), $icon_method ) ) {
						echo dollie()->$icon_method();
					} else {
						echo dollie()->icon()->arrow_right();
					}
					?>
					<?php echo esc_html( $label ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>
