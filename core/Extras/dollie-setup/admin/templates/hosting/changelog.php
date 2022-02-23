
		<div class="wrap about-wrap">
			<h1><?php printf( __( 'Welcome to Dollie Setup %s', 'dollie-setup' ), dollie_setup_get_version() ); ?></h1>

			<div class="about-text" style="min-height:90px;"><?php printf( __( 'Thank you for updating to the latest version!', 'dollie-setup' ), dollie_setup_get_version() ); ?></div>

			<div class="dollie_setup-badge">
				<img src="<?php echo esc_url( dollie_setup_get_package_prop( 'badge_url' ) ); ?>" srcset="<?php echo esc_url( dollie_setup_get_package_prop( 'badge_url_2x' ) ); ?> 2x" alt="<?php esc_attr_e( 'Badge', 'dollie-setup' ); ?>" />
			</div>

			<h2 class="nav-tab-wrapper">
				<a href="<?php echo self_admin_url( 'admin.php?page=dollie_setup&whatsnew=1' ); ?>" class="nav-tab nav-tab-active">
					<?php _e( 'What&#8217;s New', 'dollie-setup' ); ?>
				</a>
				<a href="http://commonsinabox.org/project-team" class="nav-tab" target="_blank">
					<?php _e( 'Credits', 'dollie-setup' ); ?>
				</a>
			</h2>

			<!--
			<div class="changelog">
				<h3><?php _e( 'Under the Hood', 'dollie-setup' ); ?></h3>

				<div class="feature-section col two-col">

					<div class="last-feature">
						<li><?php esc_html_e( 'Improve compatibility with PHP 8.0+.', 'dollie-setup' ); ?></li>
					</div>
				</div>
			</div>
			-->

			<div class="changelog">
				<h3><?php esc_html_e( 'Plugin Updates', 'dollie-setup' ); ?></h3>

				<div class="feature-section">
					<ul>
						<li>BuddyPress 9.2.0</li>
						<li>bbPress 2.6.9</li>
					</ul>
				</div>
			</div>

			<!--
			<div class="changelog">
				<h3><?php esc_html_e( 'Theme Updates', 'dollie-setup' ); ?></h3>

				<div class="feature-section">
					<ul>
						<li><?php esc_html_e( 'Compatibility with BuddyPress 8.0.0 "selectable signu profile fields" feature.', 'dollie-setup' ); ?></li>
						<li><?php esc_html_e( 'Add highlighting to unread group forum threads.', 'dollie-setup' ); ?></li>
						<li><?php esc_html_e( 'Compatibility with BP bulk notifications management.', 'dollie-setup' ); ?></li>
					</ul>
				</div>
			</div>
			-->

			<div class="return-to-dashboard">
				<?php printf( __( '<a href="%s">Return to the Dollie dashboard &rarr;</a>', 'dollie-setup' ), self_admin_url( 'admin.php?page=dollie_setup' ) ); ?>
			</div>

		</div>
