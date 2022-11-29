<?php

if ( ! dollie()->get_user()->can_manage_all_sites() ) {
	return;
}

if ( ! isset( $container ) ) {
	$container = dollie()->get_container();
}

?>

<h2 class="dol-text-gray-500 dol-uppercase dol-tracking-wide dol-mb-5 dol-text-xl">
	<?php esc_html_e( 'Admin Settings', 'dollie' ); ?>
</h2>

<?php if ( $container->is_running() ) : ?>

	<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
		class="dol-p-6 dol-bg-white dol-shadow dol-overflow-hidden sm:dol-rounded-md">
		<h3 class="dol-text-gray-700 dol-uppercase dol-mt-0 dol-mb-2 dol-text-lg">
			<?php if ( $container->is_site() ) : ?>
				<?php esc_html_e( 'Site is RUNNING', 'dollie' ); ?>
			<?php else : ?>
				<?php esc_html_e( 'Blueprint is RUNNING', 'dollie' ); ?>
			<?php endif; ?>
		</h3>

		<div class="dol-mb-4 dol-text-sm">
			<?php esc_html_e( 'This instance is running. You can stop it using the button bellow. Please note that stopped instances are still being billed.', 'dollie' ); ?>
		</div>

		<input type="hidden" name="container" value="<?php echo esc_attr( $container->get_id() ); ?>">
		<input type="hidden" name="action" value="dollie_action_stop_container">
		<input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'dollie_action_stop_container' ); ?>">
		
		<button type="submit" class="dol-px-4 dol-py-2 dol-bg-red-500 hover:dol-bg-red-600 dol-border-0 dol-rounded dol-text-white dol-text-sm focus:dol-outline-none focus:dol-bg-primary-600">
			<?php esc_html_e( 'Stop Site', 'dollie' ); ?>
		</button>
	</form>

<?php else : ?>

	<div class="dol-p-6 dol-bg-white dol-shadow dol-overflow-hidden sm:dol-rounded-md">
		<h3 class="dol-text-gray-700 dol-uppercase dol-mt-0 dol-mb-2 dol-text-lg">
			<?php if ( $container->is_site() ) : ?>
				<?php esc_html_e( 'This Site has an unmanageable status', 'dollie' ); ?>
			<?php else : ?>
				<?php esc_html_e( 'This Blueprint has an unmanageable status', 'dollie' ); ?>
			<?php endif; ?>
		</h3>

		<div class="dol-text-sm">
			<?php printf( esc_html__( 'This instance\'s status is %s. You cannot manage this instance while having this particular status.', 'dollie' ), $container->get_status() ); ?>
		</div>
	</div>

<?php endif; ?>

<?php if ( $container->is_site() ) : ?>
	<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
		class="dol-mt-6 dol-p-6 dol-bg-white dol-shadow dol-overflow-hidden sm:dol-rounded-md">
		<h3 class="dol-text-gray-700 dol-uppercase dol-mt-0 dol-mb-2 dol-text-lg">
			<?php esc_html_e( 'Site Owner', 'dollie' ); ?>
		</h3>

		<div class="dol-mb-4 dol-text-sm">
			<?php esc_html_e( 'This is the owner of this instance. You can change the owner using the form below.', 'dollie' ); ?>
		</div>

		<select name="owner" class="dol-mb-4 dol-rounded dol-border dol-border-solid dol-border-gray-400">
			<option value="" disabled><?php esc_html_e( 'Select User', 'dollie' ); ?></option>
			<?php foreach ( get_users() as $user ) : ?>
				<option value="<?php echo esc_attr( $user->ID ); ?>" <?php selected( $container->get_author_id(), $user->ID ); ?>>
					<?php echo esc_html( $user->display_name ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		
		<input type="hidden" name="container" value="<?php echo esc_attr( $container->get_id() ); ?>">
		<input type="hidden" name="action" value="dollie_set_container_owner">
		<input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'dollie_set_container_owner' ); ?>">

		<button type="submit" class="dol-px-4 dol-py-2 dol-bg-primary-500 hover:dol-bg-primary-600 dol-border-0 dol-rounded dol-text-white dol-text-sm focus:dol-outline-none focus:dol-bg-primary-600">
			<?php esc_html_e( 'Update', 'dollie' ); ?>
		</button>
	</form>
<?php endif; ?>
