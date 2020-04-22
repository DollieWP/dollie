<?php

$token_active = (bool) get_option( 'dollie_auth_token_status', 0 );
$status       = __( 'Inactive', DOLLIE_SLUG );

if ( $token_active ) {
	$status = __( 'Active', DOLLIE_SLUG );
}

?>

<div class="dollie-notice">
    <h1><span class="dashicons dashicons-rest-api"></span> Dollie API</h1>
</div>

<br>
<br>

<div>
	<?php if ( isset( $_GET['err'] ) ) : ?>
		<?php _e( 'Something went wrong. Please try again later or contact our support.', DOLLIE_SLUG ); ?>
        <br>
        <br>
	<?php endif; ?>

	<?php if ( isset( $_GET['status'] ) && $_GET['status'] === 'not_connected' ) : ?>
		<?php _e( 'You are not connected with the Dollie API. Please follow the instruction at the top of the page regarding API authentication.', DOLLIE_SLUG ); ?>
        <br>
        <br>
	<?php endif; ?>

	<?php if ( isset( $_GET['status'] ) && $_GET['status'] === 'refresh' ) : ?>
		<?php _e( 'Your Dollie API token has expired. Please refresh it to prevent any discontinuation of your service to your clients.', DOLLIE_SLUG ); ?>
        <br>
        <br>
	<?php endif; ?>

    <div>
        <b>API Status:</b> <?php echo $status; ?>
    </div>

    <br>

    <form method="post"><input type="submit" name="synchronize" class="button" value="Refresh Token"></form>

</div>
