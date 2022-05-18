<?php

use Dollie\Core\Services\AuthService;

$token = AuthService::instance()->get_token();

?>

<?php if ( $token ) : ?>
	<p><?php esc_html_e( 'This is the token that links this site and your partner account to the Dollie API.', 'dollie' ); ?></p>

	<div style="margin-bottom: 10px;">
		<pre style="background: #EFEFEF; padding: 5px; display: inline;"><?php echo esc_attr( $token ); ?></pre>
	</div>

	<a href="<?php echo esc_url( add_query_arg( 'disconnect_dollie', 1 ) ); ?>" class="button button-large">
		<?php esc_html_e( 'Disconnect from Dollie', 'dollie' ); ?>
	</a>

	<p><?php esc_html_e( 'If you disconnect your Hub from Dollie, you won\'t be able to deploy sites for your platform.', 'dollie' ); ?></p>
<?php else : ?>
	<div class="notice dollie-notice">
		<?php esc_html_e( 'This site is not connected to the Dollie API. Please follow the instruction at the top of the page to set up your API authentication.', 'dollie' ); ?>
	</div>
<?php endif; ?>
