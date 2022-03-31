<?php
$i = 0;
if ( $posts ) :
	?>
	<ul role="list" class="dol--my-5 dol-divide-y dol-divide-gray-200">
		<?php foreach ( $posts as $post ) : ?>
			<li class="dol-py-1">
				<div class=" dol-flex dol-items-center dol-space-x-4">
					<div class="dol-flex-1 dol-min-w-0">
						<a class="dol-text-sm dol-font-medium dol-text-gray-400 dol-no-underline" target=" _blank" href="<?php echo esc_url( $post->link ); ?>"><?php echo esc_html( $post->title->rendered ); ?></a>
						<<?php echo wp_trim_words( esc_html( strip_tags( $post->excerpt->rendered ) ), 20 ); ?>
					</div>
				</div>
			</li>
			<?php
			$i++;
			if ( $i == '10' ) {
				break;
			}
		endforeach;
		?>
	</ul>

<?php else : ?>

	<p class="dol-m-0 dol-p-0 dol-ash-700">
		<?php esc_html_e( 'There are no posts available', 'dollie' ); ?>
	</p>

<?php endif; ?>
