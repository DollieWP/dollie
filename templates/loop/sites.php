<?php
$user = dollie()->get_user();
$subscription_vip = dollie()->subscription()->has_vip(get_current_user_id());
$global_vip = get_field( 'wpd_enable_global_vip_sites', 'options' );

if ( ! isset( $view_type ) ) {
	$view_type = 'list';
}

$list_class = 'dol-sites-' . $view_type;

$list_btn_active = 'list' === $view_type ? 'dol-switch-active' : '';
$grid_btn_active = 'grid' === $view_type ? 'dol-switch-active' : '';

$action_hashes = [];
foreach ( dollie()->bulk_actions()->get_saved_bulk_actions() as $action ) {
	if ( ! in_array( $action['container_hash'], $action_hashes ) ) {
		$action_hashes[] = $action['container_hash'];
	}
}

$bulk_actions = dollie()->bulk_actions()->get_bulk_actions( $action_hashes );

if ( is_wp_error( $bulk_actions ) ) {
	$bulk_actions = [];
}

$allowed_bulk_commands = dollie()->bulk_actions()->get_allowed_commands_in_progress();

dollie()->load_template( 'loop/parts/modal-actions', array(), true );
dollie()->load_template( 'loop/parts/modal-filters', array(), true );

?>

<div class="dol-mb-6 dol-widget-site-search">
	<div class="dol-rounded dol-p-4 dol-bg-white dol-shadow-md">
		<div class="dol-flex dol-flex-wrap dol-items-center md:dol-justify-between dol-space-y-4 md:dol-space-y-0">
			<div class="dol-flex dol-items-center">
				<div class="dol-check-wrap dol-inline-block">
					<label class="dol-flex dol-items-center">
						<input type="checkbox" name="checkbox" class="dol-select-all-container dol-scale-125 checked:dol-bg-blue-500 dol-mr-2" />
						<span class="dol-text-sm"><?php esc_html_e( 'Select all', 'dollie' ); ?></span>
					</label>
				</div>

				<button type="button" data-modal-id="dol-modal-id-actions" class="dol-open-modal">
					<?php echo dollie()->icon()->task( 'dol-mr-2' ); ?> <?php esc_html_e( 'Run Actions', 'dollie' ); ?>
				</button>
			</div>

			<div class="dol-flex dol-flex-wrap dol-items-center dol-space-y-2 md:dol-space-y-0 md:dol-space-x-4">
				<div class="dol-flex dol-items-center dol-space-x-2">
					<div class="dol-relative">
						<div class="dol-absolute dol-h-full dol-flex dol-items-center dol-px-3"><?php echo dollie()->icon()->search( 'dol-text-gray-500' ); ?></div>
						<input type="text" name="site_search" class="dol-search-input dol-search-site dol-w-full md:dol-w-64"
							data-list-type="<?php echo esc_attr( $view_type ); ?>"
							data-permalink="<?php echo esc_attr( $query_data['permalink'] ); ?>"
							data-per-page="<?php echo ( isset( $_GET['per_page'] ) ? esc_attr( $_GET['per_page'] ) : '' ); ?>"
							data-search-term=""
							placeholder="<?php printf( esc_html__( 'Search for a %s', 'dollie' ), dollie()->string_variants()->get_site_type_string() ); ?>">
					</div>
					<span data-modal-id="dol-modal-id-filters" class="dol-open-modal dol-p-3 dol-m-0 dol-bg-gray-200 hover:dol-bg-gray-300 dol-text-gray-700 dol-block dol-cursor-pointer">
						<?php echo dollie()->icon()->filter(); ?>
					</span>
					<?php if ( $subscription_vip  ) : ?>
					<a href="<?php echo esc_html( dollie()->page()->get_sites_url() ); ?>/?vip=yes" data-tooltip="<?php printf( esc_html__( 'Show Only Your VIP %s', 'dollie' ), dollie()->string_variants()->get_site_type_plural_string() ); ?>" class="dol-layout-preview dol-preview-bar-layout">
						<?php echo dollie()->icon()->vip(); ?>
					</a>
					<?php endif; ?>
				</div>

				<div class="dol-flex dol-items-center dol-rounded dol-overflow-hidden">
					<span data-tooltip="<?php printf( esc_html__( 'Show your %s in the List View', 'dollie' ), dollie()->string_variants()->get_site_type_plural_string() ); ?>" class="dol-list-switch <?php echo esc_attr( $list_btn_active ); ?>" data-list-type="list">
						<?php echo dollie()->icon()->list(); ?>
					</span>
					<span data-tooltip="<?php printf( esc_html__( 'Show your %s in the Grid View', 'dollie' ), dollie()->string_variants()->get_site_type_plural_string() ); ?>" class="dol-list-switch <?php echo esc_attr( $grid_btn_active ); ?>" data-list-type="grid">
						<?php echo dollie()->icon()->grid(); ?>
					</span>
					<a href="<?php echo dollie()->get_preview_url(); ?>/?type=my-sites" data-tooltip="<?php printf( esc_html__( 'Show Your %s using the Live Preview Bar', 'dollie' ), dollie()->string_variants()->get_site_type_plural_string() ); ?>" class="dol-layout-preview dol-preview-bar-layout">
						<?php echo dollie()->icon()->preview(); ?>
					</a>
					<?php if ( isset( $_GET['blueprints'] ) && $_GET['blueprints'] ) : ?>
						<a href="<?php echo esc_html( dollie()->page()->get_launch_blueprint_url() ); ?>"
							class="dol-nav-btn dol-bg-secondary dol-text-white dol-radius-0 dol-ml-3 dol-mr-0 dol-p-3">
							<span class="dol-inline-block dol-text-center dol-mr-1">
								<?php echo dollie()->icon()->launch(); ?>
							</span>
							<?php echo esc_html( dollie()->page()->get_launch_blueprint_title() ); ?>
						</a>
					<?php else : ?>
						<a href="<?php echo esc_html( dollie()->page()->get_launch_site_url() ); ?>"
							class="dol-nav-btn dol-bg-secondary dol-text-white dol-radius-0 dol-ml-3 dol-mr-0 dol-p-3">
							<span class="dol-inline-block dol-text-center dol-mr-1">
								<?php echo dollie()->icon()->launch(); ?>
							</span>
							<?php echo esc_html( dollie()->page()->get_launch_title() ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="dol-check-bulk-action"
	data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
	data-nonce="<?php echo esc_attr( wp_create_nonce( 'dollie_check_bulk_action' ) ); ?>"></div>
<div id="dol-recurring-action"
	data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
	data-nonce="<?php echo esc_attr( wp_create_nonce( 'dollie_get_recurring_action' ) ); ?>"></div>

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

		<div class="dol-sites-container <?php echo esc_attr( $list_class ); ?>">
			<?php foreach ( $sites as $site ) : ?>
				<?php

				$container = dollie()->get_container( $site );

				if ( is_wp_error( $container ) ) {
					continue;
				}

				$list_item_class      = array();
				$lock_classes         = array();
				$btn_controls_classes = array();

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

				$data = array(
					'slug'       => $container->get_slug(),
					'domain'     => $container->get_url(),
					'name'       => $container->get_title() ?: __( 'Unnamed', 'dollie' ),
					'wp_version' => $container->get_wp_version(),
				);

				if ( $container->is_blueprint() ) {
					$list_item_class[] = 'dol-blueprint-site';
					$blueprint_title   = $container->get_saved_title();

					if ( $blueprint_title ) {
						$data['name'] = $blueprint_title;
					}
				}

				if ( ! $locking['status'] ) {
					$lock_classes[] = 'dol-hidden';
				} else {
					$btn_controls_classes[] = 'dol-hidden';
					$list_item_class[]      = 'dol-sites-item-locked';
				}

				$list_item_class[] = 'dol-sites-' . $view_type . '-item';

				$lock_classes         = implode( ' ', $lock_classes );
				$btn_controls_classes = implode( ' ', $btn_controls_classes );
				$list_item_class      = implode( ' ', $list_item_class );

				?>
				<div class="dol-sites-item <?php echo esc_attr( $list_item_class ); ?>" data-site-hash="<?php echo esc_attr( $container->get_hash() ); ?>">
					<div class="dol-sites-item-inner dol-relative dol-divide-y dol-divide-gray-200 dol-shadow dol-rounded-md dol-widget-custom">
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
						<div class="dol-check-wrap">
							<label class="dol-flex dol-items-center">
								<input type="checkbox" name="checkbox" value="<?php echo esc_attr( $container->get_id() ); ?>" class="dol-scale-125 checked:dol-bg-blue-500 dol-mr-2" />
							</label>
						</div>

						<div class="dol-sites-image dol-relative">
							<div class="dol-sites-image-box">
								<img width="700" class="dol-block dol-object-cover" alt="<?php echo $container->get_url(); ?>" src="<?php echo $container->get_screenshot(); ?>">
							</div>

							<div class="dol-sites-status">
								<?php if ( $container->is_running() ) : ?>
									<span class="dol-flex dol-h-4 dol-w-4 dol-relative">
										<span class="dol-animate-ping dol-absolute dol-inline-flex dol-h-full dol-w-full dol-rounded-full dol-bg-green-500 dol-opacity-75"></span>
										<span class="dol-relative dol-inline-flex dol-rounded-full dol-h-4 dol-w-4 dol-bg-green-600"></span>
									</span>
								<?php elseif ( $container->is_deploying() ) : ?>
									<span class="dol-flex dol-h-4 dol-w-4 dol-relative">
										<span class="dol-animate-ping dol-absolute dol-inline-flex dol-h-full dol-w-full dol-rounded-full dol-bg-yellow-600 dol-opacity-75"></span>
										<span class="dol-relative dol-inline-flex dol-rounded-full dol-h-4 dol-w-4 dol-bg-yellow-600"></span>
									</span>
								<?php else : ?>
									<span class="dol-flex dol-h-4 dol-w-4 dol-relative">
										<span class="dol-animate-ping dol-absolute dol-inline-flex dol-h-full dol-w-full dol-rounded-full dol-bg-red-500 dol-opacity-75"></span>
										<span class="dol-relative dol-inline-flex dol-rounded-full dol-h-4 dol-w-4 dol-bg-red-600"></span>
									</span>
								<?php endif; ?>
							</div>
						</div>
						<div class="dol-sites-name">
							<div class="dol-px-4">
								<div class="dol-font-bold dol-text-lg dol-cursor-default dol-truncate">
									<a class="dol-item-name dol-text-normal dol-leading-normal dol-truncate dol-text-gray-600" href="<?php echo $container->get_permalink(); ?>" title="<?php echo esc_attr( $data['name'] ); ?>">
									<?php if ( $container->is_vip() ) : ?>
												<span data-toggle="tooltip"
												data-placement="bottom"
												data-tooltip="VIP Site">
												<?php echo dollie()->icon()->vip( 'dol-text-secondary dol-text-s' ); ?>
												</span>
										<?php endif;?>
										<?php echo esc_html( $data['name'] ); ?>
									</a>
								</div>

								<div class="dol-flex dol-items-center dol-truncate">
									<a class="dol-item-url dol-text-brand-500 hover:dol-text-brand-600 dol-text-sm dol-leading-normal dol-truncate" href="<?php echo esc_url( $data['domain'] ); ?>" title="<?php echo esc_html( $data['domain'] ); ?>" target="_blank">
										<?php echo esc_html( $data['domain'] ); ?>
									</a>
								</div>
							</div>
						</div>
						<div class="dol-sites-version dol-cursor-default dol-text-sm">
							<?php if ( ! $container->is_deploying() ) : ?>
								<div class="dol-font-semibold dol-text-gray-500">
									<?php esc_html_e( 'WordPress', 'dollie' ); ?>
								</div>
								<div class="dol-font-bold ">
									<?php printf( __( 'Version %s', 'dollie' ), $data['wp_version'] ); ?>
								</div>
							<?php endif; ?>
						</div>
						<?php if ( $container->is_blueprint() && ! $container->is_deploying() ) : ?>
							<div class="dol-sites-client dol-cursor-default dol-text-sm">
								<div class="dol-font-semibold dol-text-gray-500">
									<?php esc_html_e( 'Blueprint Updated', 'dollie' ); ?>
								</div>
								<div class="dol-font-bold">
									<?php if ( $container->get_changes_update_time() ) : ?>
										<?php echo $container->get_changes_update_time(); ?>
									<?php else : ?>
										<a class="dol-link" href="<?php echo $container->get_permalink( 'blueprints' ); ?>">
											<?php esc_html_e( 'Never. Update now!', 'dollie' ); ?>
										</a>
									<?php endif; ?>
								</div>
							</div>
						<?php else : ?>
							<div class="dol-sites-client dol-cursor-default dol-text-sm">
								<div class="dol-font-semibold dol-text-gray-500">
									<?php echo dollie()->string_variants()->get_user_type_string(); ?>
								</div>
								<div class="dol-font-bold ">
									<?php echo $container->get_author_name(); ?>
								</div>
							</div>
						<?php endif; ?>
						<div class="dol-sites-controls <?php echo esc_attr( $btn_controls_classes ); ?>">
							<?php if ( $container->is_deploying() ) : ?>
								<a class="dol-inline-block dol-text-sm dol-text-white dol-bg-primary dol-rounded dol-px-3 dol-py-2 hover:dol-text-white hover:dol-bg-primary-600" href="<?php echo $container->get_permalink(); ?>">
									<?php esc_html_e( 'View progress' ); ?>
								</a>
							<?php else : ?>
								<a class="dol-inline-block dol-text-sm dol-text-white dol-bg-primary dol-rounded dol-px-3 dol-py-2 hover:dol-text-white hover:dol-bg-primary-600" href="<?php echo $container->get_permalink(); ?>" data-tooltip="<?php echo esc_attr__( 'Manage', 'dollie' ); ?>">
									<?php echo dollie()->icon()->manage(); ?>
								</a>

								<?php if ( $container->is_blueprint() ) : ?>
									<a class="dol-inline-block dol-text-sm dol-text-white dol-bg-primary dol-rounded dol-px-3 dol-py-2 hover:dol-text-white hover:dol-bg-primary-600" href="<?php echo $container->get_permalink( 'blueprints' ); ?>" data-tooltip="<?php echo esc_attr__( 'Update Blueprint', 'dollie' ); ?>">
										<?php echo dollie()->icon()->blueprint(); ?>
									</a>
								<?php endif; ?>

								<?php
								$staging_url = get_post_meta( get_the_ID(), '_wpd_staging_url', true );
								$login_link  = $container->get_customer_login_url();
								?>

								<?php if ( $staging_url ) : ?>
									<a class="dol-inline-block dol-text-sm dol-text-white dol-bg-primary dol-rounded dol-px-3 dol-py-2 hover:dol-text-white hover:dol-bg-primary-600" href="<?php echo $container->get_permalink( 'staging' ); ?>" data-tooltip="<?php echo esc_attr__( 'Visit Staging Area', 'dollie' ); ?>">
										<?php echo dollie()->icon()->staging(); ?>
									</a>
								<?php endif; ?>

								<?php if ( ! empty( $login_link ) ) : ?>
									<a target="_blank" class="dol-inline-block dol-text-sm dol-text-gray-500 dol-bg-gray-200 dol-rounded dol-px-3 dol-py-2 hover:dol-text-white hover:dol-bg-secondary" href="<?php echo esc_url( $login_link ); ?>" data-tooltip="<?php echo esc_attr__( 'Login to Site as Admin', 'dollie' ); ?>">
										<?php echo dollie()->icon()->login(); ?>
									</a>
								<?php endif; ?>
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
			data-current-page="<?php echo esc_attr( $query_data['current_page'] ); ?>"
			data-list-type="<?php echo esc_attr( $view_type ); ?>">
				<?php

				echo paginate_links(
					[
						'total'        => $sites_pages,
						'show_all'     => false,
						'type'         => 'plain',
						'end_size'     => 2,
						'mid_size'     => 1,
						'prev_next'    => false,
						'add_args'     => false,
						'add_fragment' => '',
					]
				);

				?>
		</div>
	<?php else : ?>
		<div class="dol-flex dol-flex-col dol-items-center dol-justify-center">
			<div class="dol-text-2xl dol-text-ash-600">
				<?php printf( esc_html__( 'No %s Found', 'dollie' ), dollie()->string_variants()->get_site_type_plural_string() ); ?>
			</div>

			<?php if ( ! isset( $_GET['blueprints'] ) || ! $_GET['blueprints'] ) : ?>
				<div class="dol-my-4">
					<a href="<?php echo esc_html( dollie()->page()->get_launch_site_url() ); ?>"
						class="dol-btn dol-bg-secondary dol-text-white dol-mt-3">
						<span class="dol-inline-block dol-text-center dol-pr-2">
							<?php echo dollie()->icon()->launch(); ?>
						</span>
						<?php echo esc_html( dollie()->page()->get_launch_title() ); ?>
					</a>
				</div>
			<?php else : ?>
				<div class="dol-my-4">
				<?php
					dollie()->load_template(
						'notice',
						[
							'type'         => 'info',
							'icon'         => 'fas fa-copy',
							'title'        => __( 'You have no Blueprints yet!', 'dollie' ),
							'message'      => __(
								sprintf(
									'Blueprints allow you to create beautiful pre-made websites for your customers they can launch with the click of a button. <br><br><a href="%s">Launch Your First Blueprint</a>',
									dollie()->page()->get_launch_blueprint_url()
								),
								'dollie'
							),
							'bottom_space' => true,
						],
						true
					);
				?>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
