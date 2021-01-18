<h2 class="dol-title">
	<?php echo esc_html($title); ?>
</h2>

<?php
$i = 0;
if ($posts) :
?>
	<div class="dol-flex dol-flex-wrap dol--m-4">
		<?php foreach ($posts as $post) : ?>
			<?php
			$featured_image = '';

			if (isset($post->_embedded->{'wp:featuredmedia'})) {
				if (isset($post->_embedded->{'wp:featuredmedia'}[0]->code)) {
					$featured_image = '';
				} else {
					$featured_image = $post->_embedded->{'wp:featuredmedia'}[0]->media_details->sizes->full->source_url;
				}
			}
			?>
			<div class="dol-w-full md:dol-w-6/12 xl:dol-w-4/12 dol-px-4 dol-my-4">
				<div class="dol-rounded dol-overflow-hidden dol-border dol-border-solid dol-border-ash-300">
					<a target="_blank" href="<?php echo esc_url($post->link); ?>">
						<?php if ($featured_image) : ?>
							<span class="dol-block dol-overflow-hidden">
								<img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_html($post->title->rendered); ?>">
							</span>
						<?php else : ?>
							<span class="dol-flex dol-items-center dol-justify-center dol-h-40 dol-bg-gray-100">
								<i class="fal fa-newspaper fa-3x"></i>
							</span>
						<?php endif; ?>
					</a>
					<div class="dol-p-6">
						<h5 class="dol-p-0 dol-m-0 dol-mb-4">
							<a href="<?php echo esc_url($post->link); ?>" target="_blank">
								<?php echo esc_html($post->title->rendered); ?>
							</a>
						</h5>
						<div class="dol-mb-4">
							<?php echo esc_html(strip_tags($post->excerpt->rendered)); ?>
						</div>
						<div>
							<a target="_blank" class="dol-btn dol-btn-secondary dol-font-bold dol-nav-active" href="<?php echo esc_html($post->link); ?>">
								<span class="dol-flex dol-items-center">
									<?php esc_html_e('Read article', 'dollie'); ?>
									<i class="fal fa-angle-right dol-ml-2"></i>
								</span>
							</a>
						</div>
					</div>
				</div>
			</div>
		<?php
			$i++;
			if ($i == get_option('options_wpd_newsfeed_amount_of_posts', '6')) break;
		endforeach; ?>
	</div>

<?php else : ?>

	<p class="dol-m-0 dol-p-0 dol-ash-700">
		<?php esc_html_e('There are no posts available', 'dollie'); ?>
	</p>

<?php endif; ?>
