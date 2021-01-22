<h2 class="dol-text-gray-500 text-s dol-font-small dol-uppercase dol-tracking-wide dol-mb-5 dol-text-xl">
	<?php

	$user_info = get_userdata( get_current_user_id() );
	printf( __( 'Welcome back %s', 'dollie' ), $user_info->display_name );

	?>
</h2>
<?php if ( $settings['description'] ) : ?>
	<span class="dol-block dol-text-lg">
		<?php echo $settings['description']; ?>
	</span>
<?php endif; ?>
