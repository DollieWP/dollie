<h2 class="dol-title">
	<?php

	$user_info = get_userdata( get_current_user_id() );
	printf( __( 'Welcome back %s', 'dollie' ), $user_info->display_name );

	?>
</h2>
<?php if ( $settings['description'] ) : ?>
	<span class="dol-block dol-text-cobalt-400 dol-text-lg">
		<?php echo $settings['description']; ?>
	</span>
<?php endif; ?>
