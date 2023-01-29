<?php

use Dollie\Core\Services\AuthService;

$token = AuthService::instance()->get_token();

?>

<?php if ( $token ) : ?>
	<p><?php esc_html_e( 'This is the token that links this site and your account to the Dollie Cloud.', 'dollie' ); ?></p>

	<div style="margin-bottom: 10px;">
		<pre style="background: #EFEFEF; padding: 5px; display: inline;"><?php echo esc_attr( $token ); ?></pre>
	</div>

	<a href="<?php echo esc_url( add_query_arg( 'disconnect_dollie', 1 ) ); ?>" class="button button-large">
		<?php esc_html_e( 'Disconnect from Dollie Cloud', 'dollie' ); ?>
	</a>

	<p><?php esc_html_e( 'If you disconnect your Hub from Dollie, you won\'t be able to launch sites via this Hub.', 'dollie' ); ?></p>
<?php else : ?>
	<div class="notice dollie-notice">
		<?php esc_html_e( 'This Hub is not connected to the Dollie Cloud. Please follow the instructions at the top of the page to securely connect to Dollie Cloud.', 'dollie' ); ?>
	</div>
<?php endif; ?>
