<h2 class="dol-text-gray-500 dol-uppercase dol-tracking-wide dol-mb-5 dol-text-xl">
	<?php echo esc_html( $title ); ?>
</h2>

<?php
$i = 0;
if ( $posts ) :
	?>
	<div class="dol-flex dol-flex-wrap dol--m-4 dol-widget-posts">
		<?php foreach ( $posts as $post ) : ?>
			<?php
			$featured_image = '';

			if ( isset( $post->_embedded->{'wp:featuredmedia'} ) ) {
				if ( isset( $post->_embedded->{'wp:featuredmedia'}[0]->code ) ) {
					$featured_image = '';
				} else {
					$featured_image = $post->_embedded->{'wp:featuredmedia'}[0]->media_details->sizes->full->source_url;
				}
			}
			?>
			<div class="dol-w-full md:dol-w-6/12 xl:dol-w-4/12 dol-px-4 dol-my-4 dol-widget-post">
				<div class="dol-overflow-hidden <?php do_action( 'dol_add_widget_classes' ); ?> dol-divide-y dol-divide-gray-200 dol-p-0">
					<a target="_blank" href="<?php echo esc_url( $post->link ); ?>">
						<?php if ( $featured_image ) : ?>
							<span class="dol-block dol-overflow-hidden">
								<img class="dol-aspect-video dol-object-cover" src="<?php echo esc_url( $featured_image ); ?>" alt="<?php echo esc_html( $post->title->rendered ); ?>">
							</span>
						<?php else : ?>
							<span class="dol-flex dol-items-center dol-justify-center dol-aspect-video dol-bg-gray-100">
								<?php echo dollie()->icon()->blog_post( 'fa-3x' ); ?>
							</span>
						<?php endif; ?>
					</a>
					<div class="dol-p-6">
						<h5 class="dol-p-0 dol-m-0 dol-mb-4">
							<a href="<?php echo esc_url( $post->link ); ?>" target="_blank">
								<?php echo esc_html( $post->title->rendered ); ?>
							</a>
						</h5>
						<div class="dol-mb-4 dol-text-sm dol-text-gray-500">
							<?php echo wp_trim_words( esc_html( strip_tags( $post->excerpt->rendered ) ), 20 ); ?>
						</div>
						<div>
							<a target="_blank" class="dol-btn dol-btn-secondary dol-nav-active" href="<?php echo esc_html( $post->link ); ?>">
								<span class="dol-flex dol-items-center">
									<?php esc_html_e( 'Read article', 'dollie' ); ?>
									<?php echo dollie()->icon()->arrow_right( 'dol-ml-2' ); ?>
								</span>
							</a>
						</div>
					</div>
				</div>
			</div>
			<?php
			$i++;
			if ( $i == get_option( 'options_wpd_newsfeed_amount_of_posts', '6' ) ) {
				break;
			}
		endforeach;
		?>
	</div>

<?php else : ?>

	<p class="dol-m-0 dol-p-0 dol-ash-700">
		<?php esc_html_e( 'There are no posts available', 'dollie' ); ?>
	</p>

<?php endif; ?>
