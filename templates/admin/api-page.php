<div class="wrap">
	<?php if ( isset( $_GET['err'] ) ) : ?>
		<div class="notice dollie-notice dollie-error">
			<h4>Sorry, We could not connect this site to our API</h4>
			<?php esc_html_e( 'Could you please email us directly at support@getdollie.com and we will get this fixed immediately.', 'dollie' ); ?>
		</div>
	<?php endif; ?>


	<?php if ( dollie()->is_api_connected() ) : ?>
		<p><?php esc_html_e( 'Here is the token that links your site and partner account to the Dollie API.', 'dollie' ); ?></p>

		<pre class="dol-p-2 dol-bg-gray-200 dol-block"><?php echo esc_attr( \Dollie\Core\Utils\Api::get_auth_token() ); ?></pre>

		<a href="<?php echo esc_url( add_query_arg( 'disconnect_dollie', 1 ) ); ?>" class="button">
			<?php esc_html_e( 'Disconnect from Dollie', 'dollie' ); ?>
		</a>
		<p class="dol-text-xs dol-font-bold"><?php esc_html_e( 'If you disconnect your site from Dollie, it would mean that your platform would stop working.', 'dollie' ); ?></p>
	<?php endif; ?>


	<?php if ( ! dollie()->is_api_connected() ) : ?>
		<?php esc_html_e( 'You are not connected with the Dollie API. Please follow the instruction at the top of the page regarding API authentication.', 'dollie' ); ?>
	<?php endif; ?>
</div>
