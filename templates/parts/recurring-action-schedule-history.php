<?php

if ( ! isset( $data ) ) {
	$data = [];
}

?>

<?php if ( ! empty( $data ) ) : ?>
	<div class="dol-recurring-delete-success dol-hidden dol-text-sm dol-text-white dol-bg-green-500 dol-px-4 dol-py-2 dol-rounded dol-mb-3">
		<?php esc_html_e( 'Schedule deleted successfully!', 'dollie' ); ?>
	</div>
	<div class="dol-schedule-list dol-pr-4">
		<?php foreach ( $data as $schedule ) : ?>
			<div class="dol-schedule-list-item dol-relative dol-my-2 dol-border dol-border-solid dol-border-gray-200 dol-rounded dol-overflow-hidden">
				<div class="dol-loader dol-mt-0" data-for="recurring-actions-delete">
					<div class="dol-flex dol-items-center dol-justify-center dol-h-full">
						<svg class="dol-animate-spin dol-h-10 dol-w-10 dol-text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
							<circle class="dol-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
							<path class="dol-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
						</svg>
					</div>
				</div>
				<div class="dol-schedule-accordion dol-cursor-pointer dol-border-0 dol-border-b dol-border-solid dol-border-gray-100 dol-bg-gray-100 dol-px-4 dol-py-2 dol-flex dol-justify-between dol-items-center">
					<div class="dol-font-bold dol-text-lg"><?php echo esc_html( $schedule['name'] ); ?></div>
					<div>
						<span class="dol-acc-closed">
							<?php echo dollie()->icon()->angle_right(); ?>
						</span>
						<span class="dol-acc-opened dol-hidden">
							<?php echo dollie()->icon()->angle_down(); ?>
						</span>
					</div>
				</div>
				<div class="dol-schedule-accordion-content dol-hidden dol-flex-wrap dol--mx-2">
					<div class="dol-w-4/12 dol-px-2">
						<div class="dol-p-4">
							<div class="dol-schedule-action dol-mb-3">
								<div class="dol-font-bold"><?php echo dollie()->icon()->task(); ?> <?php esc_html_e( 'Action', 'dollie' ); ?></div>
								<div class="dol-text-sm dol-text-gray-700"><?php echo dollie()->recurring_actions()->get_allowed_commands()[ $schedule['action'] ]; ?> - <?php echo dollie()->recurring_actions()->get_allowed_intervals()[ $schedule['interval'] ]; ?></div>
							</div>
							<div class="dol-schedule-time dol-mb-3">
								<div class="dol-font-bold"><?php echo dollie()->icon()->clock(); ?> <?php esc_html_e( 'Interval details', 'dollie' ); ?></div>
								<div class="dol-text-sm dol-text-gray-700"><?php echo $schedule['next_run']; ?></div>
							</div>
							<div class="dol-schedule-logs">
								<div class="dol-font-bold"><?php echo dollie()->icon()->logs(); ?></i> <?php esc_html_e( 'Logs', 'dollie' ); ?></div>
								<span class="dol-show-logs dol-text-sm dol-underline dol-cursor-pointer" data-hide-log="<?php esc_html_e( 'Hide logs', 'dollie' ); ?>" data-show-log="<?php esc_html_e( 'Show logs', 'dollie' ); ?>"><?php esc_html_e( 'View logs' ); ?></span>
							</div>
							<div class="dol-mt-4">
								<span class="dol-delete-schedule dol-inline-block dol-text-red-600 dol-text-sm dol-border dol-border-solid dol-border-red-600 hover:dol-bg-red-600 hover:dol-text-white dol-px-4 dol-py-2 dol-rounded dol-cursor-pointer" data-uuid="<?php echo esc_attr( $schedule['uuid'] ); ?>" data-ajax-url="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'dollie_delete_recurring_action' ) ); ?>">
									<?php echo dollie()->icon()->close( 'dol-mr-2' ); ?> <?php esc_html_e( 'Remove', 'dollie' ); ?>
								</span>
							</div>
						</div>
					</div>
					<div class="dol-w-8/12 dol-px-2">
						<ul class="dol-schedule-container-logs dol-hidden dol-list-none dol-m-0 dol-p-0">
							<?php foreach ( $schedule['logs'] as $log ) : ?>
								<li class="dol-flex dol-flex-wrap dol-text-sm dol-px-4 dol-py-2 odd:dol-bg-white even:dol-bg-gray-100">
									<div class="dol-w-3/12"><?php echo $log['created_at']; ?></div>
									<div class="dol-w-9/12"><?php echo $log['message']; ?></div>
								</li>
							<?php endforeach; ?>
							<?php if ( empty( $schedule['logs'] ) ) : ?>
								<li class="dol-text-sm dol-px-4 dol-py-2">
									<?php esc_html_e( 'There are no logs available yet!', 'dollie' ); ?>
								</li>
							<?php endif; ?>
						</ul>
						<ul class="dol-schedule-container-list dol-list-none dol-m-0 dol-p-0">
							<?php foreach ( $schedule['containers'] as $container ) : ?>
								<li class="dol-schedule-container-item dol-px-4 dol-py-2 odd:dol-bg-white even:dol-bg-gray-100 dol-relative">
									<div class="dol-loader dol-mt-0 dol-top-0 dol-left-0" data-for="recurring-container-delete">
										<div class="dol-flex dol-items-center dol-justify-center dol-h-full">
											<svg class="dol-animate-spin dol-h-10 dol-w-10 dol-text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
												<circle class="dol-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
												<path class="dol-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
											</svg>
										</div>
									</div>
									<div class="dol-flex dol-items-center dol-justify-between">
										<div class="">
											<div class="dol-font-bold"><?php echo $container['name']; ?></div>
											<div class="dol-text-xs dol-truncate">
												<a href="<?php echo esc_url( $container['url'] ); ?>" target="_blank"><?php echo $container['url']; ?></a>
											</div>
										</div>
										<div class="dol-text-sm">
											<span class="dol-delete-recurring-container dol-cursor-pointer hover:dol-text-red-600" data-uuid="<?php echo esc_attr( $schedule['uuid'] ); ?>" data-container-hash="<?php echo esc_attr( $container['hash'] ); ?>" data-ajax-url="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'dollie_delete_recurring_container' ) ); ?>"><?php echo dollie()->icon()->close(); ?></span>
										</div>
									</div>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
<?php else : ?>
	<div class="dol-text-sm dol-text-gray-600"><?php esc_html_e( 'You don\'t have any scheduled actions yet.', 'dollie' ); ?></div>
<?php endif; ?>
