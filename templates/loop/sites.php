<?php

$user = dollie()->get_user();

$action_hashes = array();
foreach ( dollie()->bulk_actions()->get_saved_bulk_actions() as $action ) {
	if ( ! in_array( $action['container_hash'], $action_hashes ) ) {
		$action_hashes[] = $action['container_hash'];
	}
}

$bulk_actions = dollie()->bulk_actions()->get_bulk_actions( $action_hashes );

if ( is_wp_error( $bulk_actions ) ) {
	$bulk_actions = array();
}

$allowed_bulk_commands = dollie()->bulk_actions()->get_allowed_commands_in_progress();

$filters = array(
	'available' => array(
		'customers'  => dollie()->get_user()->can_manage_all_sites() ? get_users() : array(),
		'site_types' => array(
			'normal' => __( 'Normal', 'dollie' ),
			'vip'    => __( 'VIP', 'dollie' ),
		),
		'statuses'   => array(
			'Running',
			'Stopped',
			'Deploying',
			'Undeployed',
			'Deploy Failure',
		),
		'pages'      => 30,
	),
);

// dollie()->load_template( 'loop/parts/modal-actions', [], true );
dollie()->load_template( 'loop/parts/modal-filters', array( 'filters' => $filters ), true );

?>

<?php if ( isset( $_GET['blueprints'] ) && $_GET['blueprints'] ) : ?>
	<div class="dol-bg-info dol-p-3 dol-text-white dol-rounded dol-shadow dol-w-full dol-text-sm dol-mb-3">
		<?php echo dollie()->icon()->notice(); ?> <?php esc_html_e( 'You are now viewing the blueprints made by you and your team.', 'dollie' ); ?>
	</div>
<?php endif; ?>

<?php if ( ! isset( $_GET['blueprints'] ) && $user->can_view_all_sites() ) : ?>
	<div class="dol-bg-info dol-p-3 dol-text-white dol-rounded dol-shadow dol-w-full dol-text-sm dol-mb-3">
		<?php echo dollie()->icon()->notice(); ?> <?php esc_html_e( 'This sites listing shows all of the sites by you and your customers. Your customers will only see the sites that belong to them.', 'dollie' ); ?>
	</div>
<?php endif; ?>

<div class="dol-mb-6 dol-widget-site-search">
	<div class="dol-rounded dol-p-4 dol-bg-white dol-shadow-md dol-flex dol-gap-4 dol-w-full dol-justify-between">
		<div class="dol-flex dol-search">
			<div class="dol-relative dol-w-full lg:dol-w-[400px]">
				<div class="dol-absolute dol-h-full dol-flex dol-items-center dol-px-3"><?php echo dollie()->icon()->search( 'dol-text-gray-500' ); ?></div>
				<input type="text" name="site_search" class="dol-search-input dol-search-site dol-w-full"
					data-permalink="<?php echo esc_attr( $query_data['permalink'] ); ?>"
					data-per-page="<?php echo ( isset( $_GET['per_page'] ) ? esc_attr( $_GET['per_page'] ) : '' ); ?>"
					data-search-term=""
					placeholder="<?php esc_html_e( 'Search by url, plugin, version, etc...', 'dollie' ); ?>">
			</div>
		</div>



		<div class="dol-flex dol-justify-end"> <!-- Added parent div with justify-end -->
			<div class="dol-flex dol-flex-col-reverse lg:dol-flex-row dol-flex-wrap dol-justify-end dol-actions">
				<div class="dol-mt-4 lg:dol-mt-0">
					<div class="dol-hidden lg:dol-flex dol-flex-wrap dol-items-center dol-space-x-2">
						<span data-modal-id="dol-modal-id-filters"
							class="dol-open-modal dol-block dol-p-3 dol-m-0 dol-bg-gray-200 hover:dol-bg-gray-300 dol-text-gray-700 dol-rounded dol-text-sm dol-leading-none dol-cursor-pointer">
							<?php echo dollie()->icon()->filter(); ?>
						</span>

						<a href="<?php echo dollie()->get_preview_url(); ?>/?type=my-sites"
							class="dol-p-3 dol-rounded dol-text-sm dol-leading-none dol-bg-gray-200 hover:dol-bg-gray-300 dol-text-gray-700 dol-inline-block"
							data-tooltip="<?php printf( esc_html__( 'Show Your %s using the Live Preview Bar', 'dollie' ), dollie()->string_variants()->get_site_type_plural_string() ); ?>">
							<?php echo dollie()->icon()->preview(); ?>
						</a>

						<div class="dol-flex dol-items-center dol-rounded dol-overflow-hidden">
							<?php if ( isset( $_GET['blueprints'] ) && $_GET['blueprints'] ) : ?>
								<a href="<?php echo esc_html( dollie()->page()->get_launch_blueprint_url() ); ?>"
									class="dol-nav-btn dol-bg-secondary dol-text-white hover:!dol-text-white dol-radius-0 dol-px-3 dol-py-2 dol-space-x-1">
									<span class="dol-inline-block dol-text-center">
										<?php echo dollie()->icon()->launch(); ?>
									</span>
									<span class="dol-hidden lg:dol-inline-block">
										<?php echo esc_html( dollie()->page()->get_launch_blueprint_title() ); ?>
									</span>
								</a>
							<?php else : ?>
								<a href="<?php echo esc_html( dollie()->page()->get_launch_site_url() ); ?>"
									class="dol-nav-btn dol-bg-secondary dol-text-white hover:!dol-text-white dol-radius-0 dol-px-3 dol-py-2 dol-space-x-1">
									<span class="dol-inline-block dol-text-center">
										<?php echo dollie()->icon()->launch(); ?>
									</span>
									<span class="dol-hidden lg:dol-inline-block">
										<?php echo esc_html( dollie()->page()->get_launch_title() ); ?>
									</span>
								</a>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>






<div class="dol-sites dol-relative">
	<div class="dol-loader" data-for="pagination">
		<div class="dol-flex dol-items-center dol-justify-center dol-h-full">
			<svg class="dol-animate-spin dol-h-10 dol-w-10 dol-text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
				<circle class="dol-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
				<path class="dol-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
			</svg>
		</div>
	</div>
	<?php if ( ! empty( $sites ) ) : ?>
		<div class="dol-sites-container">
			<?php foreach ( $sites as $site ) : ?>
				<?php

				$container = dollie()->get_container( $site );

				if ( is_wp_error( $container ) ) {
					continue;
				}

				$lock_classes         = array();
				$list_item_classes    = array( 'dol-sites-item' );
				$btn_controls_classes = array( 'dol-sites-controls' );
				$status_classes       = '';

				if ( $container->is_running() ) {
					$status_classes = 'dol-bg-green-200 dol-text-green-700';
				} elseif ( $container->is_stopped() ) {
					$status_classes = 'dol-bg-yellow-200 dol-text-yellow-700';
				} elseif ( $container->is_deploying() ) {
					$status_classes = 'dol-bg-blue-200 dol-text-blue-700';
				} elseif ( $container->is_failed() ) {
					$status_classes = 'dol-bg-red-200 dol-text-red-700';
				}

				$locking = array(
					'status' => false,
					'action' => '',
				);

				foreach ( $bulk_actions as $bulk_action ) {
					if ( $bulk_action['container_hash'] === $container->get_hash() && ! $bulk_action['status'] ) {
						$locking['status'] = true;
						$locking['action'] = $allowed_bulk_commands[ $bulk_action['action'] ];
					}
				}

				$site_name = $container->get_title();

				if ( $container->is_blueprint() && $blueprint_title = $container->get_saved_title() ) {
					$site_name = $blueprint_title;
				}

				if ( ! $locking['status'] ) {
					$lock_classes[] = 'dol-hidden';
				} else {
					$btn_controls_classes[] = 'dol-hidden';
					$list_item_classes[]    = 'dol-sites-item-locked';
				}

				$lock_classes         = implode( ' ', $lock_classes );
				$btn_controls_classes = implode( ' ', $btn_controls_classes );
				$list_item_classes    = implode( ' ', $list_item_classes );

				?>
				<div class="<?php echo esc_attr( $list_item_classes ); ?>" data-site-hash="<?php echo esc_attr( $container->get_hash() ); ?>">
					<div class="dol-sites-item-inner">
						<div class="<?php echo esc_attr( $lock_classes ); ?> dol-item-execution-placeholder dol-absolute dol-w-full dol-h-full dol-left-0 dol-top-0 dol-bg-white dol-bg-opacity-50 dol-z-10">
							<div class="dol-flex dol-items-center dol-justify-end dol-w-full dol-h-full">
								<div class="dol-flex dol-items-center dol-justify-center dol-text-sm dol-bg-gray-600 dol-text-white dol-font-medium dol-mx-6 dol-px-4 dol-py-2 dol-rounded-full">
									<svg class="dol-animate-spin dol-h-4 dol-w-4 dol-text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
										<circle class="dol-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
										<path class="dol-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
									</svg>
									<span class="dol-item-execution-text dol-inline-block dol-ml-2"><?php echo esc_html( $locking['action'] ); ?></span>
								</div>
							</div>
						</div>
						<?php if ( ! $container->is_failed() ) : ?>
							<!-- <div class="dol-check-wrap">
								<label class="dol-flex dol-items-center dol-h-full dol-px-3">
									<input type="checkbox" name="checkbox"
										value="<?php echo esc_attr( $container->get_id() ); ?>"
										class="dol-scale-125 checked:dol-bg-blue-500" />
								</label>
							</div> -->
						<?php endif; ?>
						<div class="dol-sites-image">
							<div class="dol-sites-image-box">
								<img
									class="dol-block dol-object-cover"
									alt="<?php echo esc_attr( $container->get_url() ); ?>"
									src="<?php echo esc_url( $container->get_screenshot() ); ?>">
							</div>
						</div>
						<div class="dol-sites-name">
							<div class="dol-font-bold dol-text-lg dol-cursor-default dol-truncate">
								<a class="dol-item-name dol-text-normal dol-leading-normal dol-truncate dol-no-underline dol-text-gray-600 dol-space-x-1" href="<?php echo $container->get_permalink(); ?>" title="<?php echo esc_attr( $site_name ); ?>">
									<?php if ( $container->is_vip() ) : ?>
										<span data-toggle="tooltip"
											data-placement="bottom"
											data-tooltip="VIP Site">
											<?php echo dollie()->icon()->vip( 'dol-text-secondary dol-text-sm' ); ?>
										</span>
									<?php endif; ?>
									<span><?php echo esc_html( $site_name ); ?></span>
								</a>
							</div>

							<div class="dol-flex dol-items-center dol-space-x-1 dol-truncate">
								<?php echo dollie()->icon()->link( 'dol-text-xs dol-text-gray-400' ); ?>
								<a class="dol-item-url dol-text-blue-800 hover:dol-text-blue-900 dol-text-xs dol-leading-normal dol-truncate" href="<?php echo esc_url( $container->get_url() ); ?>" title="<?php echo esc_html( $container->get_url() ); ?>" target="_blank">
									<?php echo esc_html( $container->get_url() ); ?>
								</a>
							</div>
						</div>
						<div class="dol-sites-status dol-cursor-default dol-text-sm">
							<span class="dol-px-2.5 dol-py-1 dol-rounded-full dol-text-xs dol-font-semibold dol-leading-none <?php echo esc_attr( $status_classes ); ?>">
								<?php echo esc_html( $container->get_status() ); ?>
							</span>
						</div>
						<div class="dol-sites-version dol-cursor-default dol-text-sm">
							<?php if ( ! $container->is_failed() && ! $container->is_deploying() ) : ?>
								<div class="dol-font-semibold dol-text-gray-600">
									<?php esc_html_e( 'WordPress', 'dollie' ); ?>
								</div>
								<div class="dol-text-xs dol-mt-1">
									<?php printf( __( 'Version %s', 'dollie' ), $container->get_wp_version() ); ?>
								</div>
							<?php endif; ?>
						</div>
						<?php if ( $container->is_blueprint() ) : ?>
							<div class="dol-sites-client dol-cursor-default dol-text-sm">
								<?php if ( $container->is_running() ) : ?>
									<?php if ( $container->get_changes_update_time() ) : ?>
										<div class="dol-font-semibold dol-text-gray-600">
											<?php esc_html_e( 'Blueprint published', 'dollie' ); ?>
										</div>
										<div class="dol-text-xs dol-mt-1">
											<?php echo $container->get_changes_update_time(); ?>
										</div>
									<?php else : ?>
										<div class="dol-font-semibold dol-text-gray-600">
											<?php esc_html_e( 'Blueprint published', 'dollie' ); ?>
										</div>
										<div class="dol-text-xs dol-mt-1">
											<?php esc_html_e( 'Never', 'dollie' ); ?>
										</div>
									<?php endif; ?>
								<?php endif; ?>
							</div>
						<?php else : ?>
							<div class="dol-sites-client dol-cursor-default dol-text-sm">
								<div class="dol-font-semibold dol-text-gray-600">
									<?php echo dollie()->string_variants()->get_user_type_string(); ?>
								</div>
								<div class="dol-text-xs dol-truncate dol-mt-1">
									<?php echo $container->get_author_name(); ?>
								</div>
							</div>
						<?php endif; ?>
						<div class="<?php echo esc_attr( $btn_controls_classes ); ?>">
							<?php if ( $container->is_deploying() ) : ?>
								<a class="dol-inline-block dol-text-sm dol-text-white dol-bg-primary dol-rounded dol-px-3 dol-py-2 hover:dol-text-white hover:dol-bg-primary-600" href="<?php echo $container->get_permalink(); ?>">
									<?php esc_html_e( 'View progress' ); ?>
								</a>
							<?php else : ?>
								<?php if ( $container->is_blueprint() && $container->is_running() ) : ?>
									<a class="dol-inline-block dol-text-sm dol-text-gray-500 dol-bg-gray-200 dol-rounded dol-px-3 dol-py-2 hover:dol-text-white hover:dol-bg-secondary dol-relative dol-group" href="<?php echo $container->get_permalink( 'blueprints' ); ?>" data-tooltip="<?php echo esc_attr__( 'Publish Blueprint', 'dollie' ); ?>">
										<?php if ( ! $container->get_changes_update_time() ) : ?>
											<span class="dol-absolute dol-top-0 dol-left-0 group-hover:dol-hidden dol--mt-1 dol--ml-1 dol-w-3 dol-h-3 dol-block dol-bg-orange-500 dol-rounded-full"></span>
										<?php endif; ?>
										<?php echo dollie()->icon()->launch(); ?>
									</a>
								<?php endif; ?>

								<?php if ( $staging_url = get_post_meta( get_the_ID(), '_wpd_staging_url', true ) ) : ?>
									<a class="dol-inline-block dol-text-sm dol-text-white dol-bg-primary dol-rounded dol-px-3 dol-py-2 hover:dol-text-white hover:dol-bg-primary-600" href="<?php echo $container->get_permalink( 'staging' ); ?>" data-tooltip="<?php echo esc_attr__( 'Visit Staging Area', 'dollie' ); ?>">
										<?php echo dollie()->icon()->staging(); ?>
									</a>
								<?php endif; ?>

								<?php if ( $container->is_running() ) : ?>
									<a target="_blank" class="dol-inline-block dol-text-sm dol-text-gray-500 dol-bg-gray-200 dol-rounded dol-px-3 dol-py-2 hover:dol-text-white hover:dol-bg-secondary" href="<?php echo esc_url( $container->get_customer_login_url() ); ?>" data-tooltip="<?php echo esc_attr__( 'Login as Admin', 'dollie' ); ?>">
										<?php echo dollie()->icon()->site_login(); ?>
									</a>
								<?php endif; ?>

								<a class="dol-inline-block dol-text-sm dol-text-white dol-bg-primary dol-rounded dol-px-3 dol-py-2 hover:dol-text-white hover:dol-bg-primary-600" href="<?php echo $container->get_permalink(); ?>" data-tooltip="<?php echo esc_attr__( 'Manage', 'dollie' ); ?>">
									<?php echo dollie()->icon()->manage(); ?>
								</a>
							<?php endif; ?>
						</div>
					</div>
				</div>
				<?php
				endforeach;
				wp_reset_postdata();
			?>
		</div>

		<div class="dol-sites-pages"
			data-permalink="<?php echo esc_url( $query_data['permalink'] ); ?>"
			data-current-page="<?php echo esc_attr( $query_data['current_page'] ); ?>">
				<?php

				echo paginate_links(
					array(
						'format'       => '?dpg=%#%',
						'current'      => $query_data['current_page'],
						'total'        => $sites_pages,
						'show_all'     => false,
						'type'         => 'plain',
						'end_size'     => 2,
						'mid_size'     => 1,
						'prev_next'    => false,
						'add_args'     => false,
						'add_fragment' => '',
					)
				);

				?>
		</div>
	<?php else : ?>
		<div class="dol-flex dol-flex-col dol-items-center dol-justify-center">
			<div class="dol-text-2xl dol-text-ash-600 dol-py-20">
				<?php

					printf(
						esc_html__( 'No %s Found', 'dollie' ),
						! isset( $_GET['blueprints'] ) || ! $_GET['blueprints'] ?
						dollie()->string_variants()->get_site_type_plural_string() :
						dollie()->string_variants()->get_blueprint_type_plural_string()
					);

				?>
			</div>
		</div>
	<?php endif; ?>
</div>
