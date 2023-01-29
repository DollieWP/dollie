<div class="notice dollie-notice dollie-custom-domain-notice">
	<div class="dollie-inner-message">
		<div class="dollie-message-center">
			<h3><?php esc_html_e( 'Dollie - Custom Domain Activated', 'dollie' ); ?> </h3>
			<p><?php esc_html_e( 'Yey! Your custom deployment domain is ready to use. From now on, all your sites launched via your Dollie Hub will use your custom domain.', 'dollie' ); ?></p>
		</div>
		<div>
			<a href="#" data-nonce="<?php echo esc_js( wp_create_nonce( 'dollie_notice' ) ); ?>" class="dollie-notice-dismiss">
			<?php esc_html_e( 'Dismiss', 'dollie' ); ?>
			</a>
		</div>
	</div>
</div>

<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('.dollie-notice-dismiss').on('click', function(e) {
			e.preventDefault();

			$.post(
				ajaxurl, {
					action: 'dollie_hide_domain_notice',
					_dollie_nonce: $(this).data('nonce')
				},
				function(response) {
					response = response.trim();

					if (response === "1") {
						$('.dollie-custom-domain-notice').remove();
					}
				}
			);
		});
	});
</script>
