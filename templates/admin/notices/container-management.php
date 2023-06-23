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
							__( '<a href="%s">Manage this Blueprint using the front-end of your HUB.</a> Use this page to take advanced actions, like stopping/removing the blueprint completely or assigning it to another user.', 'dollie' ),
							esc_url( $container->get_permalink( 'blueprints' ) )
						)
					);
					?>
			</div>
		<?php else : ?>
			<div class="dollie-message-center">
				<h3>
				<?php
					echo wp_kses_post(
						sprintf(
							__( 'Important - Please use <a href="%s">Control HQ</a> to delete or stop this site', 'dollie' ),
							esc_url( 'https://control.getdollie.com/container/' . $container->get_original_url() . '/dashboard' )
						)
					);
				?>
					</h3>
				<p>
					<?php
					echo wp_kses_post(
						sprintf(
							__( 'You can also manage this site the front-end of your Hub using the <a href="%s">Site Dashboard</a>. <br><br><strong>Pro Tip: </strong><br>If you are building a custom Hub design you can use the fields displayed below in your Pagebuilder templates using Dynamic Fields.', 'dollie' ),
							esc_url( $container->get_permalink() )
						)
					);
					?>
			</div>
		<?php endif; ?>
	</div>
</div>
