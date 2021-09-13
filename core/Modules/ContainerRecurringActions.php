<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;

/**
 * Class ContainerRecurringActions
 *
 * @package Dollie\Core\Modules
 */
class ContainerRecurringActions extends Singleton {

	/**
	 * Container constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'wp_ajax_dollie_create_recurring_action', [ $this, 'create_recurring_action' ] );
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

		foreach ( $params['command'] as $id => $data ) {
			$containers_ids[]['id'] = $id;
		}

		$posts = dollie()->get_containers_data( $containers_ids );

		if ( empty( $posts ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'There has been something wrong with your request.', 'dollie' ) ] );
			exit;
		}

		$targets = [];

		foreach ( $posts as $post ) {
			$commands = [];

			foreach ( $params['command'] as $id => $selected_commands ) {
				if ( (int) $id === (int) $post->ID ) {
					foreach ( $selected_commands as $command ) {
						$interval = false;

						if ( isset( $params['interval'][ $id ] ) && isset( $params['interval'][ $id ][ $command ] ) ) {
							$interval = $params['interval'][ $id ][ $command ];
						}

						if ( $interval ) {
							$commands[] = [
								'name'   => $command,
								'period' => $interval,
							];
						}
					}
				}
			}

			$container_id = get_post_meta( $post->ID, 'wpd_container_id', true );

			if ( $container_id ) {
				$targets[] = [
					'container_id'  => get_post_meta( $post->ID, 'wpd_container_id', true ),
					'container_uri' => dollie()->get_wp_site_data( 'uri', $post->ID ),
					'commands'      => $commands,
				];
			}
		}

		$response = Api::process_response(
			Api::post(
				Api::ROUTE_CONTAINER_RECURRING_ACTION_CREATE,
				[
					'targets' => $targets,
				]
			)
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

		$posts = dollie()->get_containers_data( $_REQUEST['containers'] );

		if ( empty( $posts ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Something went wrong with your request.', 'dollie' ) ] );
			exit;
		}

		$targets         = [];
		$targets_for_api = [];
		foreach ( $posts as $post ) {
			$container_id = get_post_meta( $post->ID, 'wpd_container_id', true );

			if ( $container_id ) {
				$targets[] = [
					'id'           => $post->ID,
					'name'         => get_the_title( $post->ID ),
					'url'          => dollie()->get_site_url( $post->ID ),
					'container_id' => $container_id,
					'commands'     => [],
				];

				$targets_for_api[] = $container_id;
			}
		}

		$response = Api::process_response(
			Api::post(
				Api::ROUTE_CONTAINER_RECURRING_ACTION_GET,
				[
					'targets' => $targets_for_api,
				]
			)
		);

		foreach ( $targets as $key => $target ) {
			foreach ( $response as $container_id => $data ) {
				if ( $targets[ $key ]['container_id'] === $container_id ) {
					foreach ( $data as $commands ) {
						$targets[ $key ]['commands'][ $commands['action'] ] = $commands['period'];
					}
				}
			}
		}

		ob_start();

		?>
			<form action="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>" method="POST" id="dol-schedule-form" data-nonce="<?php echo esc_attr( wp_create_nonce( 'dollie_create_recurring_action' ) ); ?>">
				<div class="dol-rounded dol-overflow-hidden">
					<ul class="dol-list-none dol-m-0 dol-p-0">
						<li class="dol-flex dol-font-bold dol-text-white dol-bg-gray-700">
							<div class="dol-w-4/12 dol-px-4 dol-py-2">
							<?php esc_html_e( 'Site', 'dollie' ); ?>
							</div>
							<div class="dol-w-8/12 dol-px-4 dol-py-2">
							<?php esc_html_e( 'Actions', 'dollie' ); ?>
							</div>
						</li>
					</ul>
					<ul class="dol-schedule-list dol-list-none dol-m-0 dol-p-0 dol-border dol-border-solid dol-border-gray-200">
					<?php foreach ( $targets as $target ) : ?>
							<li class="dol-schedule-list-item dol-flex dol-py-2 dol-border-b dol-border-solid dol-border-gray-200">
								<div class="dol-w-4/12 dol-px-4 dol-py-2">
									<div class="dol-font-bold"><?php echo $target['name']; ?></div>
									<div class="dol-text-sm dol-mt-1 dol-truncate">
										<a href="<?php echo esc_url( $target['url'] ); ?>" target="_blank"><?php echo $target['url']; ?></a>
									</div>
								</div>
								<div class="dol-w-8/12 dol-px-4 dol-py-2">
									<?php foreach ( $this->get_allowed_commands() as $command_name => $command_text ) : ?>
										<div class="dol-action-selector dol-flex dol-items-center dol-mb-2">
											<div class="dol-w-7/12 dol-action-container dol-pr-4 dol-py-2">
												<label class="dol-flex dol-items-center dol-space-x-3">
													<input type="checkbox" class="dol-appearance-none dol-h-4 dol-w-4 dol-border dol-border-gray-300 dol-rounded-md checked:dol-bg-blue-600 checked:dol-border-transparent focus:dol-outline-none" value="<?php echo $command_name; ?>" name="command[<?php echo esc_attr( $target['id'] ); ?>][]" <?php checked( array_key_exists( $command_name, $target['commands'] ) ); ?>>
													<span><?php echo $command_text; ?></span>
												</label>
											</div>
											<div class="dol-w-5/12 dol-interval-container <?php echo ( ! array_key_exists( $command_name, $target['commands'] ) ? esc_attr( 'dol-hidden' ) : '' ); ?>">
												<select name="interval[<?php echo esc_attr( $target['id'] ); ?>][<?php echo $command_name; ?>]" class="dol-py-1 dol-pl-2 dol-pr-6 dol-border-gray-300 dol-rounded dol-text-sm">
													<option value="" selected disabled><?php esc_html_e( 'Select Interval', 'dollie' ); ?></option>
													<?php foreach ( $this->get_allowed_intervals() as $interval_name => $interval_text ) : ?>
														<option value="<?php echo $interval_name; ?>" <?php array_key_exists( $command_name, $target['commands'] ) ? selected( $target['commands'][ $command_name ], $interval_name ) : ''; ?>><?php echo $interval_text; ?></option>
													<?php endforeach; ?>
												</select>
											</div>
										</div>
									<?php endforeach; ?>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>

				<button type="submit" class="dol-create-schedule dol-mt-3 dol-px-4 dol-py-2 dol-bg-primary-500 hover:dol-bg-primary-600 dol-border-0 dol-rounded dol-text-white dol-text-sm focus:dol-outline-none focus:dol-bg-primary-600">
					<i class="fas fa-tools dol-mr-2"></i> <?php esc_html_e( 'Update', 'dollie' ); ?>
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

		$response = Api::process_response(
			Api::post(
				Api::ROUTE_CONTAINER_RECURRING_ACTION_GET,
			)
		);

		$data = [];

		foreach ( $response as $container_id => $item ) {
			$data[]['container_id'] = $container_id;
		}

		$posts = dollie()->get_containers_data( $data, 'container_id' );

		$targets = [];
		foreach ( $posts as $post ) {
			$container_id = get_post_meta( $post->ID, 'wpd_container_id', true );

			if ( $container_id ) {
				$targets[] = [
					'id'           => $post->ID,
					'name'         => get_the_title( $post->ID ),
					'url'          => dollie()->get_site_url( $post->ID ),
					'container_id' => $container_id,
					'commands'     => [],
				];

				$targets_for_api[] = $container_id;
			}
		}

		foreach ( $targets as $key => $target ) {
			foreach ( $response as $container_id => $data ) {
				if ( $targets[ $key ]['container_id'] === $container_id ) {
					foreach ( $data as $commands ) {
						$targets[ $key ]['commands'][ $commands['action'] ] = $commands['period'];
					}
				}
			}
		}

		?>
			<div class="dol-rounded dol-overflow-hidden">
				<ul class="dol-list-none dol-m-0 dol-p-0">
					<li class="dol-flex dol-font-bold dol-text-white dol-bg-gray-700">
						<div class="dol-w-4/12 dol-px-4 dol-py-2">
						<?php esc_html_e( 'Site', 'dollie' ); ?>
						</div>
						<div class="dol-w-8/12 dol-px-4 dol-py-2">
						<?php esc_html_e( 'Actions', 'dollie' ); ?>
						</div>
					</li>
				</ul>
				<ul class="dol-schedule-list dol-list-none dol-m-0 dol-p-0 dol-border dol-border-solid dol-border-gray-200">
				<?php foreach ( $targets as $target ) : ?>
						<li class="dol-schedule-list-item dol-flex dol-py-2 dol-border-b dol-border-solid dol-border-gray-200">
							<div class="dol-w-4/12 dol-px-4 dol-py-2">
								<div class="dol-font-bold"><?php echo $target['name']; ?></div>
								<div class="dol-text-sm dol-mt-1 dol-truncate">
									<a href="<?php echo esc_url( $target['url'] ); ?>" target="_blank"><?php echo $target['url']; ?></a>
								</div>
							</div>
							<div class="dol-w-8/12 dol-px-4 dol-py-2">
								<div class="dol-flex dol-items-center">
									<?php foreach ( $this->get_allowed_commands() as $command_name => $command_text ) : ?>
										<?php
										if ( ! array_key_exists( $command_name, $target['commands'] ) ) {
											continue;}
										?>
											<div class="dol-3/12 dol-p-2">
												<div class="dol-rounded dol-px-4 dol-py-2 dol-border dol-border-solid dol-border-gray-300">
													<div class="dol-font-medium dol-text-sm"><?php echo $command_text; ?></div>
													<div class="dol-text-xs dol-text-right"><?php echo $this->get_allowed_intervals()[ $target['commands'][ $command_name ] ]; ?></div>
												</div>
											</div>
									<?php endforeach; ?>
								</div>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php

		$response = ob_get_clean();

		wp_send_json_success( $response );
	}

}