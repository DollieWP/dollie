<?php
if (defined('S5_APP_TOKEN') ) {  ?>
<div class="notice dollie-notice dollie-setup dollie-connect-message">
	<div class="dollie-inner-message">
		<div class="dollie-message-center">
			<h3><span>Setup -</span> <?php esc_html_e( 'Welcome to your Dollie Hub. Powered by WordPress!', 'dollie' ); ?> </h3>
			<p><?php esc_html_e( 'This lightning-fast WordPress site is hosted on the Dollie Cloud. We host your Hub completely for free, for as long as you are with us. Let\'s continue setting it up your HUB together, so you can start selling your services in no time!', 'dollie' ); ?></p>
		</div>

		<div class="dollie-msg-button-right">
			<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="#33D399" viewBox="0 0 24 24" stroke="currentColor">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
			</svg><?php echo $url; ?>
		</div>
	</div>
</div>
<?php } else { ?>

<div class="notice dollie-notice dollie-setup dollie-connect-message">
	<div class="dollie-inner-message">
		<div class="dollie-message-center">
			<h3><span>Setup -</span> <?php esc_html_e( 'Welcome to your Dollie Hub, let\'s get you started!', 'dollie' ); ?> </h3>
			<p><?php esc_html_e( 'To start building your platform we first need to securely authenticate this site with the Dollie Cloud. Simply click on the button below to continue...', 'dollie' ); ?></p>
		</div>

		<div class="dollie-msg-button-right">
			<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="#33D399" viewBox="0 0 24 24" stroke="currentColor">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
			</svg><?php echo $url; ?>
		</div>
	</div>
</div>

<?php }
?>

