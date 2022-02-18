<?php

use Dollie\Core\Utils\Api;

?>

<div class="wrap">
	<h1><span class="dashicons dashicons-rest-api"></span> Dollie API</h1>

	<?php if ( isset( $_GET['err'] ) ) : ?>
		<div class="notice dollie-notice">
			<?php esc_html_e( 'Something went wrong. Please try again later or contact our support.', 'dollie' ); ?>
		</div>
	<?php endif; ?>

	<?php if ( isset( $_GET['status'] ) && $_GET['status'] === 'not_connected' ) : ?>
		<div class="notice dollie-notice">
			<?php esc_html_e( 'You are not connected with the Dollie API. Please follow the instruction at the top of the page regarding API authentication.', 'dollie' ); ?>
		</div>
	<?php endif; ?>

	<?php if ( Api::get_auth_token() ) : ?>

		<div class="notice dollie-notice">
			<h3><?php esc_html_e( 'Your site is connected to Dollie', 'dollie' ); ?></h3>
		</div>

		<h3>API TOKEN</h3>
		<p><?php esc_html_e( 'This is the token that links your site and partner account to the Dollie API.', 'dollie' ); ?></p>

		<input style="width: 400px" type="text" readonly
			   value="<?php echo esc_attr( Api::get_auth_token() ); ?>">
		<a href="<?php echo esc_url( add_query_arg( 'disconnect_dollie', 1 ) ); ?>" class="button button-large">
			<?php esc_html_e( 'Disconnect from Dollie', 'dollie' ); ?>
		</a>
		<p><?php esc_html_e( 'If you disconnect your site from Dollie, you won\'t be able to deploy sites or interact with our servers.', 'dollie' ); ?></p>

	<?php endif; ?>

</div>
