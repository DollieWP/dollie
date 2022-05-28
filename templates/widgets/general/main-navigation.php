<?php
$user = dollie()->get_user(get_current_user_id());
$active_class = [
	'launch-site'      => dollie()->page()->is_launch() ? 'dol-nav-active' : '',
	'customers'      => dollie()->page()->is_customers() ? 'dol-nav-active' : '',
	'dashboard'        => dollie()->page()->is_dashboard() ? 'dol-nav-active' : '',
	'sites'            => ! isset( $_GET['blueprints'] ) && dollie()->page()->is_sites() ? 'dol-nav-active' : '',
	'launch-blueprint' => dollie()->page()->is_launch_blueprint() ? 'dol-nav-active' : '',
	'view-blueprint'   => isset( $_GET['blueprints'] ) && dollie()->page()->is_sites() ? 'dol-nav-active' : '',
];

do_action( 'dollie/before/main-menu' );

?>

<ul class="dol-list-none dol-p-0 dol-m-0 dol-widget-main-nav">

	<?php if ( dollie()->page()->get_launch_url() ) : ?>
	<li class="dol-mb-0">
		<a href="<?php echo esc_html( dollie()->page()->get_launch_url() ); ?>"
			class="dol-nav-btn dol-bg-secondary dol-text-white <?php echo esc_attr( $active_class['launch-site'] ); ?>">
			<span class="dol-inline-block dol-text-center dol-w-8">
				<?php echo dollie()->icon()->launch(); ?>
			</span>
			<?php echo esc_html( dollie()->page()->get_launch_title() ); ?>
		</a>
	</li>
	<?php endif; ?>

	<?php if ( current_user_can( 'manage_options' ) ) : ?>
		<li class="dol-mb-0">
			<a href="<?php echo dollie()->page()->get_launch_blueprint_url(); ?>"
				class="dol-nav-btn <?php echo esc_attr( $active_class['launch-blueprint'] ); ?>">
				<span class="dol-inline-block dol-text-center dol-w-8">
					<?php echo dollie()->icon()->blueprint(); ?>
				</span>
				<?php echo dollie()->page()->get_launch_blueprint_title(); ?>
			</a>
		</li>
	<?php endif; ?>
	<?php if ( dollie()->page()->get_dashboard_url() ) : ?>
	<li class="dol-mt-5">
		<a href="<?php echo dollie()->page()->get_dashboard_url(); ?>"
			class="dol-nav-btn <?php echo esc_attr( $active_class['dashboard'] ); ?>">
			<span class="dol-inline-block dol-text-center dol-w-8">
				<?php echo dollie()->icon()->site_dashboard(); ?>
			</span>
			<?php echo dollie()->page()->get_dashboard_title(); ?>
		</a>
	</li>
	<?php endif; ?>


	<?php if ( dollie()->page()->get_customers_url() && $user->can_view_all_sites() ) : ?>
	<li class="dol-mb-0">
		<a href="<?php echo esc_html( dollie()->page()->get_customers_url() ); ?>"
			class="dol-nav-btn <?php echo esc_attr( $active_class['customers'] ); ?>">
			<span class="dol-inline-block dol-text-center dol-w-8">
				<?php echo dollie()->icon()->customers(); ?>
			</span>
			<?php echo esc_html( dollie()->page()->get_customers_title() ); ?>
		</a>
	</li>
	<?php endif; ?>

	<?php if ( dollie()->page()->get_sites_url() ) : ?>
	<li class="dol-m-0">
		<a href="<?php echo dollie()->page()->get_sites_url(); ?>"
			class="dol-nav-btn <?php echo esc_attr( $active_class['sites'] ); ?>">
			<span class="dol-inline-block dol-text-center dol-w-8">
				<?php echo dollie()->icon()->live_site(); ?>
			</span>
			<?php echo dollie()->page()->get_sites_title(); ?>
		</a>
	</li>
	<?php endif; ?>

	<?php if ( current_user_can( 'manage_options' ) ) : ?>
		<li class="dol-m-0">
			<a href="<?php echo dollie()->page()->get_sites_url( '', [ 'blueprints' => 'yes' ] ); ?>"
				class="dol-nav-btn <?php echo esc_attr( $active_class['view-blueprint'] ); ?>">
				<span class="dol-inline-block dol-text-center dol-w-8">
					<?php echo dollie()->icon()->blueprint(); ?>
				</span>
				<?php esc_html_e( 'Blueprints', 'dollie' ); ?>
			</a>
		</li>
	<?php endif; ?>
</ul>

<?php do_action( 'dollie/after/main-menu' ); ?>
