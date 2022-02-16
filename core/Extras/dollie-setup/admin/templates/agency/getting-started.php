<div class="metabox-holder postbox getting-started-dollie_setup-agency">
	<div class="stuffbox">
		<h3><?php esc_html_e( 'Getting Started with Commons in A Box Agency', 'dollie-setup' ); ?></h3>
	</div>

	<div class="inside">
		<div class="welcome-panel-column-container">

			<!-- SETTINGS -->
			<div class="welcome-panel-column">
				<h4><span class="icon16 icon-settings"></span> <?php _e( 'Settings', 'dollie-setup' ); ?></h4>
				<p><?php _e( "Dollie Setup works by pulling together a number of independent WordPress and BuddyPress plugins. Customize your site by exploring the settings pages for these plugins below.", 'dollie-setup' ); ?></p>
				<ul>

				<?php
					$dollie_setup_plugins = CBox_Plugins::get_plugins();
					foreach ( CBox_Admin_Plugins::get_settings() as $plugin => $settings_url ) {
						echo '<li><a title="' . __( "Click here to view this plugin's settings page", 'dollie-setup' ) . '" href="' . $settings_url .'">' . $plugin . '</a> - ' . $dollie_setup_plugins[$plugin]['dollie_setup_description'];

						if ( ! empty( $dollie_setup_plugins[$plugin]['documentation_url'] ) )
							echo ' [<a title="' . __( "Click here for plugin documentation at commonsinabox.org", 'dollie-setup' ) . '" href="' . esc_url( $dollie_setup_plugins[$plugin]['documentation_url'] ) . '" target="_blank">' . __( 'Info...', 'dollie-setup' ) . '</a>]';

						echo '</li>';
					}
				?>
				</ul>

				<div class="login postbox">
					<div class="message" style="text-align:center;">
						<strong><?php printf( __( '<a href="%s">Manage all your Dollie plugins here</a>', 'dollie-setup' ), esc_url( self_admin_url( 'admin.php?page=dollie_setup-plugins' ) ) ); ?></strong>
					</div>
				</div>
			</div>

			<!-- THEME -->
			<div class="welcome-panel-column welcome-panel-last">
				<h4><span class="icon16 icon-appearance"></span> <?php _e( 'Theme', 'dollie-setup' ); ?></h4>
				<?php
					$theme = dollie_setup_get_theme();

					if ( $theme->errors() ) :
						echo '<p>';
						printf( __( '<a href="%1$s">Install the %2$s theme to get started</a>.', 'dollie-setup' ), wp_nonce_url( self_admin_url( 'admin.php?page=dollie_setup&amp;dollie_setup-action=install-theme' ), 'dollie_setup_install_theme' ), esc_attr( dollie_setup_get_theme_prop( 'name' ) ) );
						echo '</p>';
					else:

						// current theme is not the Dollie default theme
						if ( $theme->get_template() != dollie_setup_get_theme_prop( 'directory_name' ) ) {
							$is_bp_compatible = dollie_setup_is_theme_bp_compatible();

						?>
							<p><?php printf( __( 'Your current theme is %s.', 'dollie-setup' ), '<strong>' . $theme->display( 'Name' ) . '</strong>' ); ?></p>

							<?php
								if ( ! $is_bp_compatible ) {
									echo '<p>';
									_e( 'It looks like this theme is not compatible with BuddyPress.', 'dollie-setup' );
									echo '</p>';
								}
							?>

							<?php if ( dollie_setup_get_theme_prop( 'directory_name' ) && dollie_setup_get_theme_prop( 'screenshot_url' ) ) : ?>

								<p><?php printf( __( 'Did you know that <strong>%s</strong> comes with a cool theme? Check it out below!', 'dollie-setup' ), esc_html( dollie_setup_get_package_prop( 'name' ) ) ); ?></p>

								<a class="thickbox" title="<?php printf( esc_attr__( 'Screenshot of the %s theme', 'dollie-setup' ), dollie_setup_get_theme_prop( 'name' ) ); ?>" href="<?php echo esc_url( dollie_setup_get_theme_prop( 'screenshot_url' ) ); ?>"><img width="200" src="<?php echo esc_url( dollie_setup_get_theme_prop( 'screenshot_url' ) ); ?>" alt="" /></a>

								<div class="login postbox">
									<div class="message" style="text-align:center;">
										<strong><?php printf( '<a href="%1$s" data-confirm="%2$s" onclick="return confirm( this.getAttribute( \'data-confirm\' ) );">%3$s</a>',
											wp_nonce_url( self_admin_url( 'admin.php?page=dollie_setup&amp;dollie_setup-action=install-theme' ), 'dollie_setup_install_theme' ),
											sprintf( esc_html__( "This will activate the %s theme on your site.\n\nAre you sure you want to continue?", 'dollie-setup' ), esc_attr( dollie_setup_get_theme_prop( 'name' ) ) ),
											sprintf( esc_html__( 'Like the %s theme? Install it!', 'dollie-setup' ), esc_attr( dollie_setup_get_theme_prop( 'name' ) ) ) ); ?></strong>
									</div>
								</div>

							<?php endif; ?>

							<?php
								if ( ! $is_bp_compatible ) {
									echo '<p>';
									printf( __( "You can also make your theme compatible with the <a href='%s'>BuddyPress Template Pack</a>.", 'buddypress' ), network_admin_url( 'plugin-install.php?type=term&tab=search&s=%22bp-template-pack%22' ) );
									echo '</p>';
								}
							?>

						<?php
						// current theme is the Dollie default theme
						} else {
						?>

							<?php if ( $theme->get_stylesheet() != dollie_setup_get_theme_prop( 'directory_name' ) ) : ?>
								<p><?php printf( __( 'You\'re using a child theme of the <strong>%1$s</strong> theme.', 'dollie-setup' ), esc_attr( dollie_setup_get_theme_prop( 'name' ) ) ); ?></p>
							<?php else : ?>
								<p><?php printf( __( 'You\'re using the <strong>%1$s</strong> theme.', 'dollie-setup' ), esc_attr( dollie_setup_get_theme_prop( 'name' ) ) ); ?></p>
							<?php endif; ?>

							<div class="login postbox">
								<div class="message">
									<strong><?php printf( __( '<a href="%1$s">Configure the %2$s theme here</a>', 'dollie-setup' ), esc_url( get_admin_url( dollie_setup_get_main_site_id(), dollie_setup_get_theme_prop( 'admin_settings' ) ) ), esc_attr( dollie_setup_get_theme_prop( 'name' ) ) ); ?></strong>
								</div>
							</div>

						<?php
						}

					endif;
				?>
			</div>

		</div><!-- .welcome-panel-column-container -->
	</div>
</div>
