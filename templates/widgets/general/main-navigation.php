<?php

$active_class = [
	'launch-site'      => dollie()->is_launch_page() ? 'dol-nav-active' : '',
	'dashboard'        => dollie()->is_dashboard_page() ? 'dol-nav-active' : '',
	'sites'            => dollie()->is_sites_page() ? 'dol-nav-active' : '',
	'launch-blueprint' => dollie()->is_launch_blueprint_page() ? 'dol-nav-active' : '',
	'view-blueprint'   => dollie()->is_sites_page() ? 'dol-nav-active' : '',
];

do_action( 'dollie/before/main-menu' );

?>

<ul class="dol-list-none dol-p-0 dol-m-0 dol-widget-main-nav">
	<li class="dol-mb-4">
		<a href="<?php echo esc_html( dollie()->get_launch_page_url() ); ?>" class="dol-nav-btn dol-bg-secondary dol-text-white <?php echo esc_attr( $active_class['launch-site'] ); ?>">
			<span class="dol-inline-block dol-text-center dol-w-8"><?php echo dollie()->icon()->launch(); ?></span>
			<?php echo esc_html( dollie()->get_launch_page_title() ); ?>
		</a>
	</li>

	<?php if ( current_user_can( 'manage_options' ) ) : ?>
		<li class="dol-mb-4">
			<a href="<?php echo dollie()->get_launch_blueprint_page_url(); ?>" class="dol-nav-btn <?php echo esc_attr( $active_class['launch-blueprint'] ); ?>">
				<span class="dol-inline-block dol-text-center dol-w-8">
					<?php echo dollie()->icon()->blueprint(); ?>
				</span>
				<?php echo dollie()->get_launch_blueprint_page_title(); ?>
			</a>
		</li>
	<?php endif; ?>

	<li class="dol-mb-1">
		<a href="<?php echo dollie()->get_dashboard_page_url(); ?>" class="dol-nav-btn <?php echo esc_attr( $active_class['dashboard'] ); ?>">
			<span class="dol-inline-block dol-text-center dol-w-8"><?php echo dollie()->icon()->site_dashboard(); ?></span>
			<?php echo dollie()->get_dashboard_page_title(); ?>
		</a>
	</li>

	<li class="dol-m-0">
		<a href="<?php echo dollie()->get_sites_page_url(); ?>" class="dol-nav-btn
		   <?php
			if ( ! isset( $_GET['blueprints'] ) || ! $_GET['blueprints'] ) {
				?>
				<?php echo esc_attr( $active_class['sites'] ); ?><?php } ?>">
			<span class="dol-inline-block dol-text-center dol-w-8">
				<?php echo dollie()->icon()->live_site(); ?>
			</span>
			<?php echo dollie()->get_sites_page_title(); ?>
		</a>
	</li>

	<?php if ( current_user_can( 'manage_options' ) ) : ?>
		<li class="dol-m-0">
			<a href="<?php echo dollie()->get_sites_page_url( [ 'blueprint' => 'yes' ] ); ?>" class="dol-nav-btn
			   <?php
				if ( isset( $_GET['blueprints'] ) && $_GET['blueprints'] ) {
					?>
					<?php echo esc_attr( $active_class['view-blueprint'] ); ?><?php } ?>">
				<span class="dol-inline-block dol-text-center dol-w-8">
					<?php echo dollie()->icon()->blueprint(); ?>
				</span>
				<?php esc_html_e( 'Blueprints', 'dollie' ); ?>
			</a>
		</li>
	<?php endif; ?>
</ul>

<?php do_action( 'dollie/after/main-menu' ); ?>
