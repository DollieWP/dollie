<?php
if ( ! \Elementor\Plugin::instance()->editor->is_edit_mode() ) {
	wp_enqueue_script( 'dollie-layout-alpine' );
}

use Dollie\Core\Modules\AccessControl;

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
				<?php echo dollie()->get_sites_page_title(); ?>
			</span>
        </li>
		<?php

		$count = 0;

		while ( $containers->have_posts() ) :
			$containers->the_post();
			$domain         = get_post_meta( get_the_ID(), 'wpd_domains', true );
			$setup_complete = get_post_meta( get_the_ID(), 'wpd_setup_complete', true );
			$blueprint      = get_post_meta( get_the_ID(), 'wpd_blueprint_created', true );

			$menu = [
				''                => '<i class="fas fa-columns"></i> ' . __( 'Dashboard', 'dollie' ),
				'plugins'         => '<i class="fas fa-plug"></i> ' . __( 'Plugins', 'dollie' ),
				'themes'          => '<i class="fas fa-paint-roller"></i> ' . __( 'Themes', 'dollie' ),
				'domains'         => '<i class="fas fa-globe"></i> ' . __( 'Domains', 'dollie' ),
				'backups'         => '<i class="fas fa-history"></i> ' . __( 'Backups', 'dollie' ),
				'updates'         => '<i class="fas fa-box-open"></i> ' . __( 'Updates', 'dollie' ),
				'developer-tools' => '<i class="fas fa-code"></i> ' . __( 'Developer Tools', 'dollie' ),
				'blueprints'      => '<i class="fas fa-copy"></i> ' . __( 'Blueprints', 'dollie' ),
				'migrate'         => '<i class="fas fa-truck-moving"></i> ' . __( 'Migrate', 'dollie' ),
				'delete'          => '<i class="fas fa-trash-alt"></i> ' . __( 'Delete', 'dollie' ),
			];

			if ( dollie()->is_blueprint( get_the_ID() ) ) {
				unset( $menu['domains'] );
			}

			$sub_page = get_query_var( 'sub_page' );
			$count ++;

			if ( $count > 41 ) {
				continue;
			} ?>

            <div x-data="{ open: false }">
				<span @click="open = !open"
                      class="dol-flex dol-w-full dol-justify-between dol-items-center dol-nav-btn dol-nav-btn-secondary dol-cursor-pointer">
					<span class="dol-flex dol-items-center">
						<span class="dol-font-medium">
							<?php
							if ( ! empty( $domain ) ) {
								echo '<i class="fas fa-globe"></i>';
							} elseif ( ! empty( $blueprint ) ) {
								echo '<i class="fas fa-copy"></i>';
							} else {
								echo '<i class="fas fa-cog"></i>';
							}
							?>
							<?php
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
						if ( ! dollie()->in_array_r( $page, AccessControl::instance()->get_available_sections() ) ) {
							continue;
						}
						$active_class = $sub_page === $page ? ' dol-text-primary' : 'dol-font-normal dol-text-gray-400 dark:dol-text-gray-300';
						?>
                        <a class="<?php echo esc_attr( $active_class ); ?> dol-py-2 dol-px-3 dol-block dol-text-sm dol-text-gray-400 hover:dol-bg-primary hover:dol-text-white"
                           href="<?php echo dollie()->get_site_url( get_the_ID(), $page ); ?>">
                            <?php echo $title; ?>
                        </a>
					<?php endforeach; ?>
                </div>
            </div>
		<?php endwhile; ?>
    </ul>
<?php endif; ?>

<?php wp_reset_postdata(); ?>
