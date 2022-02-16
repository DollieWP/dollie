
		<div class="wrap about-wrap">
			<h1><?php printf( __( 'Welcome to Dollie Setup OpenLab %s', 'dollie-setup' ), dollie_setup_get_version() ); ?></h1>

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

			<div class="changelog">
				<h3><?php _e( 'Under the Hood', 'dollie-setup' ); ?></h3>

				<div class="feature-section col two-col">
					<ul>
						<li><?php esc_html_e( 'Fixed some issues that may have caused localizations not to appear properly in certain cases.', 'dollie-setup' ); ?></li>
					</ul>
				</div>
			</div>

			<div class="changelog">
				<h3><?php esc_html_e( 'Plugin Updates', 'dollie-setup' ); ?></h3>

				<div class="feature-section">
					<ul>
						<li>BuddyPress 9.2.0</li>
						<li>bbPress 2.6.9</li>
						<li>Event Organiser 3.10.8</li>
					</ul>
				</div>
			</div>

			<div class="changelog">
				<h3><?php esc_html_e( 'Theme Updates', 'dollie-setup' ); ?></h3>

				<div class="feature-section">
					<ul>
						<li><?php esc_html_e( 'Fixed incorrect "Contact Us" URL.', 'dollie-setup' ); ?></li>
					</ul>
				</div>
			</div>

			<div class="return-to-dashboard">
				<?php printf( __( '<a href="%s">Return to the Dollie dashboard &rarr;</a>', 'dollie-setup' ), self_admin_url( 'admin.php?page=dollie_setup' ) ); ?>
			</div>

		</div>
