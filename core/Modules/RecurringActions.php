<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;

/**
 * Class RecurringActions
 *
 * @package Dollie\Core\Modules
 */
class RecurringActions extends Singleton {
	/**
	 * Container constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'wp_ajax_dollie_create_recurring_action', [ $this, 'create_recurring_action' ] );
		add_action( 'wp_ajax_dollie_delete_recurring_action', [ $this, 'remove_recurring_action' ] );
		add_action( 'wp_ajax_dollie_delete_recurring_container', [ $this, 'remove_recurring_container' ] );
		add_action( 'wp_ajax_dollie_get_selected_sites', [ $this, 'get_selected_sites' ] );
		add_action( 'wp_ajax_dollie_get_schedule_history', [ $this, 'get_schedule_history' ] );
	}

	/**
	 * Get allowed commands
	 *
	 * @return array
	 */
	public function get_allowed_commands() {
		return [
			'update-plugins'        => __( 'Update Plugins', 'dollie' ),
			'update-themes'         => __( 'Update Themes', 'dollie' ),
			'regenerate-screenshot' => __( 'Regenerate Screenshot', 'dollie' ),
			'restart'               => __( 'Restart', 'dollie' ),
		];
	}

	/**
	 * Get allowed intervals
	 *
	 * @return array
	 */
	public function get_allowed_intervals() {
		return [
			'daily'        => __( 'Daily', 'dollie' ),
			'twiceDaily'   => __( 'Twice a day', 'dollie' ),
			'weekly'       => __( 'Weekly', 'dollie' ),
			'monthly'      => __( 'Montly', 'dollie' ),
			'twiceMonthly' => __( 'Twice a month', 'dollie' ),
		];
	}

	/**
	 * Create recurring action
	 *
	 * @return void
	 */
	public function create_recurring_action() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'dollie_create_recurring_action' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid request.', 'dollie' ) ] );
		}

		if ( ! isset( $_POST['data'] ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid request.', 'dollie' ) ] );
		}

		$containers_ids = [];
		$params         = [];
		parse_str( $_REQUEST['data'], $params );

		if ( ! $params['schedule-name'] ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid schedule name.', 'dollie' ) ] );
		}

		if ( ! array_key_exists( $params['action'], $this->get_allowed_commands() ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Action not allowed.', 'dollie' ) ] );
		}

		if ( ! array_key_exists( $params['interval'], $this->get_allowed_intervals() ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Interval not allowed.', 'dollie' ) ] );
		}

		foreach ( $params['containers'] as $container_id ) {
			$containers_ids[]['id'] = $container_id;
		}

		$posts = dollie()->containers_query( $containers_ids );

		if ( empty( $posts ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'There has been something wrong with your request.', 'dollie' ) ] );
			exit;
		}

		$targets = [];

		foreach ( $posts as $post ) {
			$container_id = get_post_meta( $post->ID, 'wpd_container_id', true );

			if ( $container_id ) {
				$targets[] = [
					'container_id'  => get_post_meta( $post->ID, 'wpd_container_id', true ),
					'container_uri' => dollie()->get_wp_site_data( 'uri', $post->ID ),
				];
			}
		}

		$response = Api::post(
			Api::ROUTE_CONTAINER_RECURRING_ACTION_CREATE,
			[
				'name'     => $params['schedule-name'],
				'targets'  => $targets,
				'action'   => $params['action'],
				'interval' => $params['interval'],
			]
		);

		wp_send_json_success( $response );
	}

	public function remove_recurring_container() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'dollie_delete_recurring_container' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid request.', 'dollie' ) ] );
		}

		if ( ! isset( $_REQUEST['uuid'] ) || ! $_REQUEST['uuid'] ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid request.', 'dollie' ) ] );
		}

		$response = Api::post(
			Api::ROUTE_CONTAINER_RECURRING_CONTAINER_DELETE,
			[
				'uuid'         => sanitize_text_field( $_REQUEST['uuid'] ),
				'container_id' => sanitize_text_field( $_REQUEST['container_id'] ),
			]
		);

		wp_send_json_success( $response );
	}

	/**
	 * Remove recurring action
	 *
	 * @return void
	 */
	public function remove_recurring_action() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'dollie_delete_recurring_action' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid request.', 'dollie' ) ] );
		}

		if ( ! isset( $_REQUEST['uuid'] ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid request.', 'dollie' ) ] );
		}

		$uuid = sanitize_text_field( $_REQUEST['uuid'] );

		$response = Api::post(
			Api::ROUTE_CONTAINER_RECURRING_ACTION_DELETE,
			[
				'uuid' => sanitize_text_field( $_REQUEST['uuid'] ),
			]
		);

		wp_send_json_success( $response );
	}

	/**
	 * Get selected sites template
	 *
	 * @return void
	 */
	public function get_selected_sites() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'dollie_get_selected_sites' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid request.', 'dollie' ) ] );
		}

		$posts = dollie()->containers_query( $_REQUEST['containers'] );

		if ( empty( $posts ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Something went wrong with your request.', 'dollie' ) ] );
			exit;
		}

		$targets = [];

		foreach ( $posts as $post ) {
			$container    = dollie()->get_container( $post );
			$container_id = get_post_meta( $post->ID, 'wpd_container_id', true );

			if ( $container_id ) {
				$targets[] = [
					'id'           => $container->get_id(),
					'name'         => $container->get_title(),
					'url'          => $container->get_url(),
					'container_id' => $container_id,
					'commands'     => [],
				];
			}
		}

		ob_start();

		?>
		<div class="dol-recurring-success dol-hidden dol-text-sm dol-text-white dol-bg-green-500 dol-px-4 dol-py-2 dol-rounded dol-mb-3">
			<?php esc_html_e( 'Schedule created successfully! You can see all the scheduled actions in the "Scheduled actions" tab.', 'dollie' ); ?>
		</div>
		<div class="dol-loader dol-mt-0" data-for="recurring-actions-create">
			<div class="dol-flex dol-items-center dol-justify-center dol-h-full">
				<svg class="dol-animate-spin dol-h-10 dol-w-10 dol-text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
					<circle class="dol-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
					<path class="dol-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
				</svg>
			</div>
		</div>
		<form action="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>" method="POST" id="dol-schedule-form" data-nonce="<?php echo esc_attr( wp_create_nonce( 'dollie_create_recurring_action' ) ); ?>">
			<div class="dol-flex dol-flex-wrap dol--mx-2">
				<div class="dol-w-4/12 dol-px-2">
					<div class="dol-border dol-border-solid dol-border-gray-300 dol-rounded dol-p-4">
						<div class="dol-mb-4">
							<label for="dol-schedule-name" class="dol-font-bold dol-mb-2 dol-text-sm"><?php echo dollie()->icon()->task(); ?> <?php esc_html_e( 'Scheduled Task Name', 'dollie' ); ?></label>
							<input type="text" name="schedule-name" value="" placeholder="<?php esc_html_e( 'My schedule\'s name', 'dollie' ); ?>" id="dol-schedule-name" class="dol-px-2 dol-py-2 dol-border dol-border-solid dol-border-gray-300 dol-rounded" required>
						</div>
						<div class="dol-mb-4">
							<label for="dol-action" class="dol-font-bold dol-mb-2 dol-text-sm"><?php echo dollie()->icon()->task(); ?> <?php esc_html_e( 'Action', 'dollie' ); ?></label>
							<select name="action" id="dol-action" class="dol-py-2 dol-pl-4 dol-pr-6 dol-rounded" required>
								<option value="" selected disabled><?php esc_html_e( 'Select Action', 'dollie' ); ?></option>
								<?php foreach ( $this->get_allowed_commands() as $command_name => $command_text ) : ?>
									<option value="<?php echo $command_name; ?>"><?php echo $command_text; ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div>
							<label for="dol-interval" class="dol-font-bold dol-mb-2 dol-text-sm"><?php echo dollie()->icon()->clock(); ?><?php esc_html_e( 'Interval', 'dollie' ); ?></label>
							<select name="interval" id="dol-interval" class="dol-py-2 dol-pl-4 dol-pr-6 dol-rounded" required>
								<option value="" selected disabled><?php esc_html_e( 'Select Interval', 'dollie' ); ?></option>
								<?php foreach ( $this->get_allowed_intervals() as $interval_name => $interval_text ) : ?>
									<option value="<?php echo $interval_name; ?>"><?php echo $interval_text; ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
				</div>
				<div class="dol-w-8/12 dol-px-2">
					<div class="dol-border dol-border-solid dol-border-gray-300 dol-rounded dol-overflow-hidden">
						<div class="dol-check-wrap dol-p-4 dol-bg-gray-800">
							<label class="dol-checkbox">
								<span class="checkbox__input dol-bg-white dol-overflow-hidden dol-rounded">
									<input type="checkbox" class="dol-select-all-schedule">
									<span class="checkbox__control">
										<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' aria-hidden="true" focusable="false">
											<path fill='none' stroke='currentColor' stroke-width='3' d='M1.73 12.91l6.37 6.37L22.79 4.59' />
										</svg>
									</span>
								</span>
								<span class="radio__label dol-text-white dol-text-sm"><?php esc_html_e( 'Select all', 'dollie' ); ?></span>
							</label>
						</div>
						<ul class="dol-schedule-create-list dol-list-none dol-m-0 dol-p-0">
							<?php foreach ( $targets as $target ) : ?>
								<li class="dol-schedule-list-item dol-px-4 dol-py-2 odd:dol-bg-white even:dol-bg-gray-100 dol-flex dol-items-center">
									<div class="dol-check-wrap dol-inline-block">
										<label class="dol-checkbox">
											<span class="checkbox__input dol-bg-white dol-overflow-hidden dol-rounded">
												<input type="checkbox" name="containers[]" value="<?php echo esc_attr( $target['id'] ); ?>" required>
												<span class="checkbox__control">
													<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' aria-hidden="true" focusable="false">
														<path fill='none' stroke='currentColor' stroke-width='3' d='M1.73 12.91l6.37 6.37L22.79 4.59' />
													</svg>
												</span>
											</span>
											<span class="radio__label"></span>
										</label>
									</div>
									<div class="dol-ml-2">
										<div class="dol-font-bold"><?php echo $target['name']; ?></div>
										<div class="dol-text-xs dol-truncate">
											<a href="<?php echo esc_url( $target['url'] ); ?>" target="_blank"><?php echo $target['url']; ?></a>
										</div>
									</div>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			</div>

			<button type="submit" class="dol-create-schedule dol-mt-3 dol-px-4 dol-py-2 dol-bg-primary-500 hover:dol-bg-primary-600 dol-border-0 dol-rounded dol-text-white dol-text-sm focus:dol-outline-none focus:dol-bg-primary-600">
				<?php echo dollie()->icon()->save(); ?> <?php esc_html_e( 'Save', 'dollie' ); ?>
			</button>
		</form>
		<?php

		$response = ob_get_clean();

		wp_send_json_success( $response );
	}

	/**
	 * Get schedule history
	 *
	 * @return void
	 */
	public function get_schedule_history() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'dollie_get_schedule_history' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid request.', 'dollie' ) ] );
		}

		$response = Api::post(
			Api::ROUTE_CONTAINER_RECURRING_ACTION_GET,
		);

		$data = [];

		if ( is_array( $response ) && ! empty( $response ) ) {
			foreach ( $response as $schedule ) {
				$containers_ids = [];

				foreach ( $schedule['containers'] as $container ) {
					$containers_ids[]['container_id'] = $container['id'];
				}

				if ( ! empty( $containers_ids ) ) {
					$posts = dollie()->containers_query( $containers_ids, 'container_id' );
				} else {
					$posts = [];
				}

				$containers = [];
				$logs       = [];

				foreach ( $schedule['containers'] as $container ) {
					if ( ! empty( $container['logs'] ) ) {
						foreach ( $container['logs'] as $log ) {
							$logs[] = $log;
						}
					}
				}

				foreach ( $posts as $post ) {
					$container    = dollie()->get_container( $post );
					$container_id = get_post_meta( $post->ID, 'wpd_container_id', true );

					if ( $container_id ) {
						$containers[] = [
							'id'           => $container->get_id(),
							'name'         => $container->get_title(),
							'url'          => $container->get_url(),
							'container_id' => $container_id,
						];
					}
				}

				$data[] = [
					'uuid'       => $schedule['uuid'],
					'name'       => $schedule['name'],
					'action'     => $schedule['action'],
					'interval'   => $schedule['interval'],
					'next_run'   => $schedule['next_run'],
					'containers' => $containers,
					'logs'       => $logs,
				];
			}
		}

		ob_start();

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
									<?php echo dollie()->icon()->arrow_right(); ?>
								</span>
								<span class="dol-acc-opened dol-hidden">
									<?php echo dollie()->icon()->arrow_down(); ?>
								</span>
							</div>
						</div>
						<div class="dol-schedule-accordion-content dol-hidden dol-flex-wrap dol--mx-2">
							<div class="dol-w-4/12 dol-px-2">
								<div class="dol-p-4">
									<div class="dol-schedule-action dol-mb-3">
										<div class="dol-font-bold"><?php echo dollie()->icon()->task(); ?> <?php esc_html_e( 'Action', 'dollie' ); ?></div>
										<div class="dol-text-sm dol-text-gray-700"><?php echo $this->get_allowed_commands()[ $schedule['action'] ]; ?> - <?php echo $this->get_allowed_intervals()[ $schedule['interval'] ]; ?></div>
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
											<?php echo dollie()->icon()->clock(); ?> <?php esc_html_e( 'Remove', 'dollie' ); ?>
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
													<span class="dol-delete-recurring-container dol-cursor-pointer hover:dol-text-red-600" data-uuid="<?php echo esc_attr( $schedule['uuid'] ); ?>" data-container-id="<?php echo esc_url( $container['container_id'] ); ?>" data-ajax-url="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'dollie_delete_recurring_container' ) ); ?>"><?php echo dollie()->icon()->clock(); ?></span>
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
		<?php

		$response = ob_get_clean();

		wp_send_json_success( $response );
	}
}
