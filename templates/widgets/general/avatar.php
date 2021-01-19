<?php

$user_info  = get_userdata( get_current_user_id() );
$user_name  = $user_info->display_name;
$user_parts = explode( ' ', $user_name, 2 );
$user_name  = count( $user_parts ) > 1 ? $user_parts[0] . ' ' . $user_parts[1][0] . '.' : $user_name;
?>

<div class="dol-flex dol-flex-wrap dol-justify-center dol-items-center">
	<div class="dol-w-full dol-text-center">
		<a href=" <?php echo get_site_url(); ?>/my-account"
		   class="dol-w-24 dol-h-24 dol-inline-block dol-rounded-full dol-overflow-hidden dol-border-4 dol-border-solid dol-border-primary-200 dol-leading-none">
			<img class="dol-object-cover"
				 src="<?php echo get_avatar_url( get_current_user_id(), $size = '80' ); ?>"
				 alt="<?php echo esc_attr( $user_name ); ?>">
		</a>
	</div>
	<a class="dol-inline-block dol-mt-2 dol-font-bold dol-uppercase  hover:dol-text-primary-700"
	   href="<?php echo get_site_url(); ?>/my-account">
		<?php echo esc_html( $user_name ); ?>
	</a>
</div>
