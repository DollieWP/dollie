<?php

if ( get_field( 'wpd_allow_site_dashboard_access', 'options' ) === 0 && ! current_user_can( 'manage_options' ) ) {
	return false;
}

$containers = new WP_Query(
	[
		'author'         => get_current_user_id(),
		'post_type'      => 'container',
		'posts_per_page' => 9999,
		'meta_key'       => 'wpd_last_viewed',
		'orderby'        => 'meta_value_num',
		'order'          => 'DESC',
	]
);

?>

<?php if ( $containers->have_posts() ) : ?>
	<ul class="dol-list-none dol-p-0 dol-m-0 dol-widget-sites-nav">
		<li class="dol-m-0">
			<span class="dol-block dol-font-bold dol-text-primary-400 dol-uppercase dol-py-2">
				<?php _e( 'Sites', 'dollie' ); ?>
			</span>
		</li>
		<?php

		$count = 0;

		while ( $containers->have_posts() ) :
			$containers->the_post();
			$domain         = get_post_meta( get_the_ID(), 'wpd_domains', true );
			$setup_complete = get_post_meta( get_the_ID(), 'wpd_setup_complete', true );
			$blueprint      = get_post_meta( get_the_ID(), 'wpd_blueprint_created', true );

			$count ++;

			if ( $count > 41 ) {
				continue;
			}

			?>
			<li class="dol-m-0">
				<a class="dol-flex dol-w-full dol-justify-between dol-items-center dol-nav-btn dol-nav-btn-secondary"
				   data-toggle="nav-submenu" href="<?php echo dollie()->get_site_url( get_the_ID() ); ?>">
					<span class="dol-flex dol-items-center">
						<span class="dol-inline-block dol-text-center dol-w-8 dol-mr-1">
							<?php
							if ( ! empty( $domain ) ) {
								echo '<i class="fal fa-globe"></i>';
							} elseif ( ! empty( $blueprint ) ) {
								echo '<i class="fal fa-copy"></i>';
							} else {
								echo '<i class="fal fa-cog"></i>';
							}
							?>
						</span>

						<span class="dol-inline-block dol-truncate dol-w-48">
							<?php
							if ( ! empty( $domain ) ) {
								echo esc_html( $domain );
							} else {
								echo get_the_title();
							}
							?>
						</span>
					</span>

					<i class="fal fa-angle-right dol-mr-1"></i>
				</a>
			</li>
		<?php endwhile; ?>
	</ul>
<?php endif; ?>

<?php wp_reset_postdata(); ?>
