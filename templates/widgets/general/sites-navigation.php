<?php
if ( ! dollie()->is_elementor_editor() ) {
	wp_enqueue_script( 'dollie-layout-alpine' );
}

if ( get_field( 'wpd_allow_site_dashboard_access', 'options' ) === 0 && ! dollie()->get_user()->can_manage_all_sites() || is_user_logged_in()  ) {
	return false;
}

$containers = new WP_Query(
	array(
		'author'         => get_current_user_id(),
		'post_type'      => 'container',
		'posts_per_page' => -1,
		'order'          => 'DESC',
	)
);

?>

<?php if ( $containers->have_posts() ) : ?>
	<ul class="dol-list-none dol-p-0 dol-m-0 dol-widget-sites-nav">
		<li class="dol-m-0">
			<span class="dol-block dol-text-primary-400 dol-uppercase dol-py-2">
				<?php
				if ( isset( $settings['icon'] ) ) {
					\Elementor\Icons_Manager::render_icon( $settings['icon'], array( 'aria-hidden' => 'true' ) );
				}

				echo dollie()->page()->get_sites_title();
				?>
			</span>
		</li>
		<?php

		$count = 0;

		while ( $containers->have_posts() ) :
			$containers->the_post();

			$container = dollie()->get_container( get_the_ID() );

			if ( is_wp_error( $container ) ) {
				continue;
			}

			$domain         = $container->get_custom_domain();
			$setup_complete = get_post_meta( get_the_ID(), 'wpd_setup_complete', true );
			$blueprint      = get_post_meta( get_the_ID(), 'wpd_blueprint_created', true );
			$icon_class     = 'dol-mr-2';

			$menu = array(
				'blueprints'      => dollie()->icon()->blueprint( $icon_class ) . __( 'Blueprint Setup', 'dollie' ),
				''                => dollie()->icon()->site_dashboard( $icon_class ) . __( 'Dashboard', 'dollie' ),
				'plugins'         => dollie()->icon()->plugins( $icon_class ) . __( 'Plugins', 'dollie' ),
				'themes'          => dollie()->icon()->themes( $icon_class ) . __( 'Themes', 'dollie' ),
				'updates'         => dollie()->icon()->updates( $icon_class ) . __( 'Updates', 'dollie' ),
				'domains'         => dollie()->icon()->domains( $icon_class ) . __( 'Domains', 'dollie' ),
				'backups'         => dollie()->icon()->backups( $icon_class ) . __( 'Backups', 'dollie' ),
				'developer-tools' => dollie()->icon()->dev_tools( $icon_class ) . __( 'Developer Tools', 'dollie' ),
				'migrate'         => dollie()->icon()->migration( $icon_class ) . __( 'Migrate', 'dollie' ),
			);

			if ( $container->has_staging() ) {
				$menu['staging'] = dollie()->icon()->staging( $icon_class ) . esc_html__( 'Staging', 'dollie' );
			}

			$menu['delete'] = dollie()->icon()->delete( $icon_class ) . esc_html__( 'Delete', 'dollie' );

			if ( $container->is_blueprint() ) {
				unset( $menu['domains'] );
			}

			if ( ! $container->is_blueprint() ) {
				unset( $menu['blueprints'] );
			}

			$sub_page = get_query_var( 'sub_page' );
			++$count;

			if ( $count > 41 ) {
				continue;
			}
			?>

			<div x-data="{ open: false }">
				<span @click="open = !open"
						class="dol-flex dol-w-full dol-justify-between dol-items-center dol-nav-btn dol-nav-btn-secondary dol-cursor-pointer">
					<span class="dol-flex dol-items-center">
						<span class="dol-font-medium dol-mr-1">
							<?php

							if ( ! empty( $domain ) ) {
								echo dollie()->icon()->live_site( $icon_class );
							} elseif ( ! empty( $blueprint ) ) {
								echo dollie()->icon()->blueprint( $icon_class );
							} else {
								echo dollie()->icon()->dev_site( $icon_class );
							}

							if ( ! empty( $domain ) ) {
								echo esc_html( $domain );
							} else {
								echo get_the_title();
							}

							?>
						</span>
					</span>
					<span>
						<svg class="dol-h-4 dol-w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path x-show="! open" d="M9 5L16 12L9 19" stroke="currentColor" stroke-width="2"
									stroke-linecap="round" stroke-linejoin="round"></path>
							<path x-show="open" d="M19 9L12 16L5 9" stroke="currentColor" stroke-width="2"
									stroke-linecap="round" stroke-linejoin="round" style="display: none;"></path>
						</svg>
					</span>
				</span>
				<div x-show="open" class="dol-p-2" style="display: none;">
					<?php foreach ( $menu as $page => $title ) : ?>
						<?php

						if ( '' === $page ) {
							$page = 'dashboard';
						}

						if ( ! dollie()->in_array_r( $page, dollie()->access()->get_available_sections() ) ) {
							continue;
						}

						$active_class = ! $sub_page || $sub_page === $page ? 'dol-text-primary' : 'dol-font-normal dol-text-gray-400';

						?>
						<a class="<?php echo esc_attr( $active_class ); ?> dol-py-2 dol-px-3 dol-block dol-text-sm"
							href="<?php echo $container->get_permalink( $page ); ?>">
							<?php echo $title; ?>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endwhile; ?>
	</ul>
<?php endif; ?>

<?php wp_reset_postdata(); ?>
