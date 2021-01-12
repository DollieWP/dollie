<div class="row">
	<div class="col-sm-12">
		<?php if ( isset( $scan ) && $scan ) { ?>
			<?php
			if ( 'failed' === $security ) {
				$message = get_transient( 'dollie_security_check_message_' . $install );
				?>
				<div class="blockquote-box blockquote-danger clearfix">
					<div class="square pull-left">
						<i class="fal fa-exclamation-triangle"></i>
					</div>
					<h4>
						<?php esc_html_e( 'Critical security issues were found', 'dollie' ); ?>
					</h4>
					<p>
						<?php esc_html_e( 'Please', 'dollie' ); ?>
						<a href="<?php echo dollie()->get_customer_login_url(); ?>&redirect_to=<?php echo dollie()->get_container_url( $post_id ); ?>%2Fwp-admin%2Fupdate-core.php">
							<?php esc_html_e( 'visit your dashboard', 'dollie' ); ?>  </a>
						<?php esc_html_e( 'and update or delete the following plugin(s):', 'dollie' ); ?>
						<br>
						<br>
						<small><strong><?php echo esc_html( $message ); ?></strong></small>
						<br>
						<small>
							<a href="<?php echo get_permalink(); ?>?run-security-check">
								<?php esc_html_e( ' Run the security check again.', 'dollie' ); ?>
							</a>
						</small>
					</p>
				</div>
			<?php } else { ?>
				<div class="blockquote-box blockquote-success clearfix">
					<div class="square pull-left">
						<i class="fal fa-shield"></i>
					</div>
					<h4>
						<?php esc_html_e( 'Our SiteGuard has found no issues.', 'dollie' ); ?>
					</h4>
					<p>
						<strong> <?php esc_html_e( 'There are no insecure plugins or themes found on your site, Good job!', 'dollie' ); ?>    </strong>
						<small>
							<a href="<?php echo get_permalink(); ?>?run-security-check">
								<?php esc_html_e( 'Run a security check!', 'dollie' ); ?>
							</a>
						</small>
					</p>
				</div>
				<?php
			}
		}
		?>
	</div>
</div>
