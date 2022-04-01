<?php

if ( ! isset( $container ) ) {
	return;
}

?>

<br>
<div style="margin-left: 0; z-index: 0" class="dollie-notice dollie-notice-error">
	<div class="dollie-inner-message">
		<?php if ( $container->is_blueprint() ) : ?>
			<div class="dollie-message-center">
				<h3><?php esc_html_e( 'Notice - How To Manage & Update This Blueprint', 'dollie' ); ?> </h3>
				<p>
					<?php
					echo wp_kses_post(
						sprintf(
							__( '<a href="%s">Manage this Blueprint using the front-end of your dashboard.</a> Use this page to take advanced actions, like stopping/removing the blueprint completely or assigning it to another user.', 'dollie' ),
							esc_url( $container->get_permalink() . '/blueprints' )
						)
					);
					?>
			</div>
		<?php else : ?>
			<div class="dollie-message-center">
				<h3><?php esc_html_e( 'Notice - How To Manage This Site', 'dollie' ); ?> </h3>
				<p>
					<?php
					echo wp_kses_post(
						sprintf(
							__( 'We recommend managing this site on the front-end of your installation using the <a href="%s">Site Dashboard</a>. Use this page to take advanced actions, like stopping or restarting your site.', 'dollie' ),
							esc_url( $container->get_permalink() )
						)
					);
					?>
			</div>
		<?php endif; ?>
	</div>
</div>
