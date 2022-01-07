<?php

if ( ! isset( $view_type ) ) {
	$view_type = 'list';
}

$list_class = 'dol-sites-' . $view_type;

$list_btn_active = 'list' === $view_type ? 'dol-switch-active' : '';
$grid_btn_active = 'grid' === $view_type ? 'dol-switch-active' : '';

dollie()->check_bulk_actions();

$bulk_actions          = dollie()->get_bulk_actions();
$allowed_bulk_commands = dollie()->get_allowed_commands_in_progress();

dollie()->load_template( 'loop/parts/modal-actions', [], true );
dollie()->load_template( 'loop/parts/modal-filters', [], true );

?>

<div class="dol-mb-6 dol-widget-site-search">
	<div class="dol-rounded dol-p-4 dol-bg-white dol-shadow-md">
		<div class="dol-flex dol-flex-wrap dol-items-center md:dol-justify-between">
			<div>
				<div class="dol-check-wrap dol-inline-block">
					<label class="dol-checkbox">
						<span class="checkbox__input dol-bg-white dol-overflow-hidden dol-rounded">
							<input type="checkbox" name="checkbox" class="dol-select-all-container">
							<span class="checkbox__control">
								<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' aria-hidden="true"
									 focusable="false">
									<path fill='none' stroke='currentColor' stroke-width='3'
										  d='M1.73 12.91l6.37 6.37L22.79 4.59'/>
								</svg>
							</span>
						</span>
						<span class="radio__label"></span>
					</label>
				</div>

				<button type="button" data-modal-id="dol-modal-id-actions" class="dol-open-modal">
					<i class="fas fa-tools dol-mr-2"></i> <?php esc_html_e( 'Run Actions', 'dollie' ); ?>
				</button>
			</div>

			<div>
				<div class="dol-relative dol-w-auto dol-inline-block">
					<div class="dol-absolute dol-left-0 dol-top-0 dol-ml-4 dol-flex dol-items-center dol-h-full">
						<i class="fas fa-search dol-text-ash-500"></i>
					</div>
					<input type="text" name="site_search"
						   class="dol-search-input dol-search-site dol-w-full md:dol-w-64"
						   data-list-type="<?php echo esc_attr( $view_type ); ?>"
						   data-permalink="<?php echo esc_attr( $query_data['permalink'] ); ?>"
						   data-per-page=<?php echo( isset( $_GET['per_page'] ) ? esc_attr( $_GET['per_page'] ) : '' ); ?> data-search-term=""
						   placeholder="<?php esc_html_e( 'Search for a site...', 'dollie' ); ?>">
				</div>

				<div class="dol-mx-3 dol-inline-block">
					<span data-modal-id="dol-modal-id-filters"
						  class="dol-open-modal dol-block dol-p-3 dol-m-0 dol-bg-gray-200 hover:dol-bg-gray-300 dol-text-gray-700 dol-flex dol-items-center dol-cursor-pointer">
						<i class="fas fa-filter"></i>
					</span>
				</div>

				<div class="dol-inline-flex dol-items-center dol-rounded dol-overflow-hidden dol-mb-4 md:dol-mb-0 dol-h-10 md:dol-h-auto">
					<span data-tooltip="<?php echo esc_attr__( 'Show your sites in the List view', 'dollie' ); ?>"
						  class="dol-list-switch dol-inline-flex <?php echo esc_attr( $list_btn_active ); ?>"
						  data-list-type="list">
						<i class=" fas fa-list"></i>
					</span>
					<span data-tooltip="<?php echo esc_attr__( 'Show your sites in the Grid view', 'dollie' ); ?>"
						  class="dol-list-switch dol-inline-flex <?php echo esc_attr( $grid_btn_active ); ?>"
						  data-list-type="grid">
						<i class=" fas fa-th"></i>
					</span>
					<a href="<?php echo dollie()->get_preview_url(); ?>/?type=
					my-sites">
						<span data-tooltip="<?php echo esc_attr__( 'Show your sites using the Live Preview bar', 'dollie' ); ?>"
							  class="dol-layout-preview dol-inline-flex dol-preview-bar-layout" data-list-type="layout">
							<i class=" fas fa-desktop"></i>
						</span>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="dol-check-bulk-action" data-ajax-url="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>"
	 data-nonce="<?php echo esc_attr( wp_create_nonce( 'dollie_check_bulk_action' ) ); ?>"></div>
<div id="dol-recurring-action" data-ajax-url="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>"
	 data-nonce="<?php echo esc_attr( wp_create_nonce( 'dollie_get_recurring_action' ) ); ?>"></div>

<div class="dol-sites dol-relative">
	<div class="dol-loader" data-for="pagination">
		<div class="dol-flex dol-items-center dol-justify-center dol-h-full">
			<svg class="dol-animate-spin dol-h-10 dol-w-10 dol-text-white" xmlns="http://www.w3.org/2000/svg"
				 fill="none" viewBox="0 0 24 24">
				<circle class="dol-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
				<path class="dol-opacity-75" fill="currentColor"
					  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
			</svg>
		</div>
	</div>
	<?php if ( $sites->have_posts() ) : ?>
		<?php if ( isset( $_GET['blueprints'] ) && $_GET['blueprints'] ) : ?>
			<div class="dol-bg-primary dol-p-3 dol-text-white dol-rounded dol-shadow dol-w-full dol-text-sm dol-mb-3">
				<?php esc_html_e( 'You are now viewing the blueprints made by you and your team.', 'dollie' ); ?>
			</div>
		<?php endif; ?>

		<div class="dol-sites-container <?php echo esc_attr( $list_class ); ?>">
			<?php while ( $sites->have_posts() ) : ?>
				<?php

				$sites->the_post();

				$list_item_class        = [];
				$execution_lock_classes = [];
				$btn_controls_classes   = [];

				$domain           = dollie()->get_wp_site_data( 'uri', get_the_ID() );
				$executing_action = [
					'status'  => false,
					'command' => '',
				];

				foreach ( $bulk_actions as $bulk_action ) {
					if ( $bulk_action['container_uri'] === $domain ) {
						$executing_action['status']  = true;
						$executing_action['command'] = $allowed_bulk_commands[ $bulk_action['action'] ];
					}
				}

				$data = [
					'slug'       => get_post_field( 'post_name', get_the_ID() ),
					'domain'     => get_post_meta( get_the_ID(), 'wpd_domains', true ) ?: dollie()->get_container_url( get_the_ID() ),
					'name'       => get_post_meta( get_the_ID(), 'wpd_installation_name', true ) ?: __( 'Unnamed', 'dollie' ),
					'wp_version' => get_post_meta( get_the_ID(), 'wpd_installation_version', true ),
					'is_running' => dollie()->get_container_status( get_the_ID() ) === 'start',
				];


				if ( dollie()->is_blueprint( get_the_ID() ) ) {
					$list_item_class[] = 'dol-blueprint-site';
					if ( get_field( 'wpd_installation_blueprint_title', get_the_ID() ) ) {
						$data['name'] = get_field( 'wpd_installation_blueprint_title', get_the_ID() );
					}
				}

				if ( ! $executing_action['status'] ) {
					$execution_lock_classes[] = 'dol-hidden';
				} else {
					$btn_controls_classes[] = 'dol-hidden';
					$list_item_class[]      = 'dol-sites-item-locked';
				}

				$list_item_class[] = 'dol-sites-' . $view_type . '-item';

				$execution_lock_classes = implode( ' ', $execution_lock_classes );
				$btn_controls_classes   = implode( ' ', $btn_controls_classes );
				$list_item_class        = implode( ' ', $list_item_class );

				?>
				<div class="dol-sites-item <?php echo esc_attr( $list_item_class ); ?>"
					 data-site-name="<?php echo esc_attr( $domain ); ?>">
					<div class="dol-sites-item-inner dol-relative dol-divide-y dol-divide-gray-200 dol-shadow dol-rounded-md dol-widget-custom dark:dol-bg-gray-800">
						<div class="<?php echo esc_attr( $execution_lock_classes ); ?> dol-item-execution-placeholder dol-absolute dol-w-full dol-h-full dol-left-0 dol-top-0 dol-bg-white dol-bg-opacity-50 dol-z-10">
							<div class="dol-flex dol-items-center dol-justify-end dol-w-full dol-h-full">
								<div class="dol-flex dol-items-center dol-justify-center dol-text-sm dol-bg-gray-600 dol-text-white dol-font-medium dol-mx-6 dol-px-4 dol-py-2 dol-rounded-full">
									<svg class="dol-animate-spin dol-h-4 dol-w-4 dol-text-white"
										 xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
										<circle class="dol-opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
												stroke-width="4"></circle>
										<path class="dol-opacity-75" fill="currentColor"
											  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
									</svg>
									<span class="dol-item-execution-text dol-inline-block dol-ml-2"><?php echo esc_html( $executing_action['command'] ); ?></span>
								</div>
							</div>
						</div>
						<div class="dol-check-wrap">
							<label class="dol-checkbox">
								<span class="checkbox__input">
									<input type="checkbox" name="checkbox"
										   value="<?php echo esc_attr( get_the_ID() ); ?>">
									<span class="checkbox__control">
										<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' aria-hidden="true"
											 focusable="false">
											<path fill='none' stroke='currentColor' stroke-width='3'
												  d='M1.73 12.91l6.37 6.37L22.79 4.59'/>
										</svg>
									</span>
								</span>
								<span class="radio__label"></span>
							</label>
						</div>

						<div class="dol-sites-image dol-relative">
							<div class="dol-sites-image-box">
								<?php echo dollie()->get_site_screenshot( get_the_ID() ); ?>
							</div>

							<div class="dol-sites-status">
								<?php if ( $data['is_running'] ) : ?>
									<span class="dol-flex dol-h-4 dol-w-4 dol-relative">
										<span class="dol-animate-ping dol-absolute dol-inline-flex dol-h-full dol-w-full dol-rounded-full dol-bg-green-500 dol-opacity-75"></span>
										<span class="dol-relative dol-inline-flex dol-rounded-full dol-h-4 dol-w-4 dol-bg-green-600"></span>
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
								<div class="dol-font-bold dol-text-lg dol-cursor-default">
									<a class="dol-item-name dol-text-normal dol-leading-normal dol-truncate dol-text-gray-600"
									   href="<?php echo dollie()->get_site_url( get_the_ID() ); ?>" target="_blank">
										<?php echo esc_html( $data['name'] ); ?>
									</a>
								</div>

								<div class="dol-flex dol-items-center">
									<a class="dol-item-url dol-text-brand-500 hover:dol-text-brand-600 dol-text-sm dol-leading-normal dol-truncate"
									   href="<?php echo esc_url( $data['domain'] ); ?>" target="_blank">
										<?php echo esc_html( $data['domain'] ); ?>
									</a>
								</div>
							</div>
						</div>
						<div class="dol-sites-version dol-cursor-default dol-text-sm">
							<div class="dol-font-semibold dol-text-gray-500">
								<?php esc_html_e( 'WordPress', 'dollie' ); ?>
							</div>
							<div class="dol-font-bold ">
								<?php printf( __( 'Version %s', 'dollie' ), $data['wp_version'] ); ?>
							</div>
						</div>
						<?php if ( dollie()->is_blueprint( get_the_ID() ) ) : ?>
							<div class="dol-sites-client dol-cursor-default dol-text-sm">
								<div class="dol-font-semibold dol-text-gray-500">
									<?php esc_html_e( 'Blueprint Updated', 'dollie' ); ?>
								</div>
								<div class="dol-font-bold">
									<?php if ( get_post_meta( get_the_ID(), 'wpd_blueprint_time', true ) ) : ?>
										<?php echo get_post_meta( get_the_ID(), 'wpd_blueprint_time', true ); ?>
									<?php else : ?>
										<a class="dol-link"
										   href="<?php echo get_the_permalink( get_the_ID() ); ?>blueprints">
											<?php esc_html_e( 'Never. Update now!', 'dollie' ); ?>
										</a>
									<?php endif; ?>
								</div>
							</div>
						<?php else : ?>
							<div class="dol-sites-client dol-cursor-default dol-text-sm">
								<div class="dol-font-semibold dol-text-gray-500">
									<?php esc_html_e( 'Customer', 'dollie' ); ?>
								</div>
								<div class="dol-font-bold ">
									<?php echo get_the_author(); ?>
								</div>
							</div>
						<?php endif; ?>
						<div class="dol-sites-controls <?php echo esc_attr( $btn_controls_classes ); ?>">
							<?php if ( dollie()->is_blueprint( get_the_ID() ) ) : ?>
								<a class="dol-inline-block dol-text-sm dol-text-white dol-font-semibold dol-bg-primary dol-rounded dol-px-3 dol-py-2 hover:dol-text-white hover:dol-bg-primary-600"
								   href="<?php echo get_the_permalink( get_the_ID() ); ?>blueprints"
								   data-tooltip="<?php echo esc_attr__( 'Update Blueprint', 'dollie' ); ?>">
									<i class="fas fa-sync"></i>
								</a>
							<?php else : ?>
								<a class="dol-inline-block dol-text-sm dol-text-white dol-font-semibold dol-bg-primary dol-rounded dol-px-3 dol-py-2 hover:dol-text-white hover:dol-bg-primary-600"
								   href="<?php echo dollie()->get_site_url( get_the_ID() ); ?>"
								   data-tooltip="<?php echo esc_attr__( 'Manage', 'dollie' ); ?>">
									<i data-tooltip="Carefully crafted site designs made by our team which you can use as a starting point for your new site."
									   class="fas fa-cog svg-tooltip acf__tooltip"></i>
								</a>
							<?php endif; ?>

							<?php
							$staging_url = get_post_meta( get_the_ID(), '_wpd_staging_url', true );
							if ( $staging_url ) :
								?>
								<a class="dol-inline-block dol-text-sm dol-text-white dol-font-semibold dol-bg-primary dol-rounded dol-px-3 dol-py-2 hover:dol-text-white hover:dol-bg-primary-600"
								   href="<?php echo get_the_permalink( get_the_ID() ); ?>staging"
								   data-tooltip="<?php echo esc_attr__( 'Visit Staging Area', 'dollie' ); ?>">
									<i class="fas fa-copy"></i>
								</a>
							<?php endif; ?>


							<?php
							$login_link = dollie()->get_customer_login_url( get_the_ID() );
							if ( ! empty( $login_link ) ) :
								?>
								<a class="dol-inline-block dol-text-sm dol-text-gray-500 dol-font-semibold dol-bg-gray-200 dol-rounded dol-px-3 dol-py-2 hover:dol-text-white hover:dol-bg-secondary"
								   href="<?php echo esc_url( $login_link ); ?>"
								   data-tooltip="<?php echo esc_attr__( 'Login to Site as Admin', 'dollie' ); ?>">
									<i class="fas fa-sign-in-alt"></i>
								</a>
							<?php endif; ?>
						</div>
					</div>
				</div>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
		</div>

		<div class="dol-sites-pages" data-permalink="<?php echo esc_url( $query_data['permalink'] ); ?>"
			 data-current-page="<?php echo esc_attr( $query_data['current_page'] ); ?>"
			 data-list-type="<?php echo esc_attr( $view_type ); ?>">
			<?php

			echo paginate_links(
				[
					'total'        => $sites->max_num_pages,
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
		<div class="dol-flex dol-items-center dol-justify-center dol-h-40">
			<div class="dol-text-2xl dol-text-ash-600 dol-font-bold">
				<?php esc_html_e( 'No sites found.', 'dollie' ); ?>
			</div>
		</div>
	<?php endif; ?>
</div>
