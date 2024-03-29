<!--Menu-->
<?php

if ( ! isset( $container ) ) {
	$container = dollie()->get_container( dollie()->get_current_post_id() );
}

if ( is_wp_error( $container ) ) {
	return;
}

$layout = $settings['layout'];
$colors = $settings['colors'];

$menu = [
	'blueprints'      => dollie()->icon()->blueprint() . __( 'Blueprint Setup', 'dollie' ),
	''                => dollie()->icon()->site_dashboard() . __( 'Dashboard', 'dollie' ),
	'stats'           => dollie()->icon()->stats() . __( 'Resource Usage', 'dollie' ),
	'plugins'         => dollie()->icon()->plugins() . __( 'Plugins', 'dollie' ),
	'themes'          => dollie()->icon()->themes() . __( 'Themes', 'dollie' ),
	'updates'         => dollie()->icon()->updates() . __( 'Updates', 'dollie' ),
	'domains'         => dollie()->icon()->domains() . __( 'Domains', 'dollie' ),
	'backups'         => dollie()->icon()->backups() . __( 'Backups', 'dollie' ),
	'developer-tools' => dollie()->icon()->dev_tools() . __( 'Developer Tools', 'dollie' ),
	'migrate'         => dollie()->icon()->migration() . __( 'Migrate', 'dollie' ),
];

if ( $container->is_blueprint() ) {
	unset( $menu['domains'] );
} else {
	unset( $menu['blueprints'] );
}

if ( $container->has_staging() ) {
	$staging_url = get_post_meta( $container->get_id(), '_wpd_staging_url', true );
	if ( $staging_url ) {
		$menu['staging'] = dollie()->icon()->staging() . esc_html__( 'Staging', 'dollie' ) . '<span style="display: inline-block; margin-left: 2px;"; class="dol-flex dol-h-3 dol-w-3 dol-relative">
                        <span style="
top: 7px"; class="dol-animate-ping dol-absolute dol-inline-flex dol-h-full dol-w-full dol-rounded-full dol-bg-green-500 dol-opacity-75"></span>
                        <span class="dol-relative dol-inline-flex dol-rounded-full dol-h-3 dol-w-3 dol-bg-green-600"></span>
                    </span>';
	} else {
		$menu['staging'] = dollie()->icon()->staging() . esc_html__( 'Staging', 'dollie' );
	}
}

$menu['delete'] = dollie()->icon()->delete() . esc_html__( 'Delete', 'dollie' );

if ( dollie()->get_user()->can_manage_all_sites() ) {
	$menu['admin-settings'] = dollie()->icon()->manage() . esc_html__( 'Admin Settings', 'dollie' );
}

$sub_page = get_query_var( 'sub_page' );
?>

<nav class="dol-w-full dol-flex-grow lg:dol-flex lg:dol-items-center lg:dol-w-auto">
    <div class="dol-overflow-hidden dol-widget-site-sidebar dol-widget-<?php echo $layout; ?>">
		<?php if ( $container->is_running() ) : ?>
            <div class="dol-w-full dol-flex-grow lg:dol-flex lg:dol-items-center lg:dol-w-auto">
                <ul class="dol-list-none dol-p-0 dol-m-0">
					<?php foreach ( $menu as $page => $title ) : ?>
						<?php

						if ( '' === $page ) {
							$page = 'dashboard';
						}

						if ( ! dollie()->get_user()->can_manage_all_sites() && ! dollie()->in_array_r( $page, dollie()->access()->get_available_sections() ) ) {
							continue;
						}

						$active_class = ! $sub_page || $sub_page === $page ? 'dol-text-primary' : 'dol-text-gray-400';

						?>
                        <li class="dol-my-2">
                            <a class="dol-flex dol-items-center dol-nav-btn-secondary <?php echo esc_attr( $active_class ); ?>"
                               href="<?php echo $container->get_permalink( $page ); ?>">
								<?php echo $title; ?>
                            </a>
                        </li>
					<?php endforeach; ?>
                </ul>
            </div>
		<?php endif; ?>
    </div>
</nav>
