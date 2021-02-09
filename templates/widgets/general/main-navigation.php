<?php

$active_class = [
	'launch-site' => dollie()->get_launch_page_id() === get_the_ID() ? 'dol-nav-active' : '',
	'dashboard'   => dollie()->get_dashboard_page_id() === get_the_ID() ? 'dol-nav-active' : '',
	'sites'       => dollie()->get_sites_page_id() === get_the_ID() ? 'dol-nav-active' : '',
];

?>

<ul class="dol-list-none dol-p-0 dol-m-0 dol-widget-main-nav">
	<li class="dol-mb-4">
		<a href="<?php echo dollie()->get_launch_page_url(); ?>"
		   class="dol-nav-btn dol-font-semibold dol-bg-secondary dol-text-white <?php echo esc_attr( $active_class['launch-site'] ); ?>">
			<span class="dol-inline-block dol-text-center dol-w-8"><i class="fas fa-rocket"></i></span>
			<?php _e( 'Launch New Site', 'dollie' ); ?>
		</a>
	</li>

	<li class="dol-mb-1">
		<a href="<?php echo dollie()->get_dashboard_page_url(); ?>"
		   class="dol-nav-btn dol-font-semibold <?php echo esc_attr( $active_class['dashboard'] ); ?>">
			<span class="dol-inline-block dol-text-center dol-w-8"><i class="fas fa-columns"></i></span>
			<?php _e( 'Dashboard', 'dollie' ); ?>
		</a>
	</li>

	<li class="dol-m-0">
		<a href="<?php echo dollie()->get_sites_page_url(); ?>"
		   class="dol-nav-btn dol-font-semibold <?php echo esc_attr( $active_class['sites'] ); ?>">
			<span class="dol-inline-block dol-text-center dol-w-8"><i class="fas fa-globe"></i></span>
			<?php _e( 'Sites', 'dollie' ); ?>
		</a>
	</li>
</ul>
