<?php

namespace Dollie\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Modules\AccessGroups\AccessGroups;
use Dollie\Core\Services\ImportService;
use Dollie\Core\Log;
use Dollie\Core\Singleton;

/**
 * Class Upgrades
 *
 * @package Dollie\Core\Admin
 */
class Upgrades extends Singleton {

	const API_URL = 'https://manager.getdollie.com/releases/packages/';

	/**
	 * Option name that gets saved in the options database table
	 *
	 * @var string
	 */
	private $option_name = 'dollie_db_version';

	/**
	 * Current plugin version
	 *
	 * @var string
	 */
	private $version = DOLLIE_VERSION;

	/**
	 * Keep track if the data was updated during current request
	 *
	 * @var bool
	 */
	private $updated = false;

	/**
	 * Upgrade versions and method callbacks
	 *
	 * @var array
	 */
	private $upgrades = [
		// '2.0.0' => '_upgrade_200',
		//'4.1.4' => '_upgrade_400',
		//'4.2.3' => '_upgrade_421',
		'6.0.0' => '_upgrade_600',
		'6.0.4.1' => '_upgrade_604',
	];

	/**
	 * Upgrades constructor.
	 */
	public function __construct() {
		parent::__construct();

		// just run it. don't show the admin notice.
		add_action( 'admin_init', function () {
			if ( $this->should_run_updates() ) {
				$this->run();
			}
		} );

		// add_action( 'admin_notices', [ $this, 'admin_notice' ], 20 );
	}

	/**
	 * @return bool
	 */
	private function should_run_updates() {
		$user = dollie()->get_user();

		if ( ! $user->can_manage_all_sites() ) {
			return false;
		}

		if ( $this->is_new_update() ) {
			return true;
		}

		return false;
	}

	/**
	 * Show admin notice to update Dollie Database
	 */
	public function admin_notice() {

		if ( $this->should_run_updates() ) {

			// Automatically update forms.
			ImportForms::instance()->import_forms();

			$this->process_update_action();

			// If we still need to show the message
			if ( ! $this->updated ) {
				$url = wp_nonce_url( add_query_arg( 'dollie_db_update', '' ), 'action' );
				?>

                <div class="notice dollie-notice">

                    <div class="dollie-inner-message">

                        <div class="dollie-message-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <h3><?php _e( 'Dollie needs to run some updates', 'dollie' ); ?></h3>
                            <p>
								<?php
								echo wp_kses_post(
									sprintf(
										__(
											'<strong>Dollie</strong> needs to update some of it\'s settings that are used behind the scenes. It
									\'ll only take a second to complete. ',
											'dollie'
										),
										esc_url( $url )
									)
								);
								?>
                            </p>
                        </div>
                        <div class="dollie-msg-button-right">
							<?php echo wp_kses_post( sprintf( __( '<a href="%s">Run Update now</a>', 'dollie' ), esc_url( $url ) ) ); ?>
                        </div>

                    </div>
                </div>
				<?php
			}
		}
	}

	/**
	 * Check if we have a new version update
	 *
	 * @return bool
	 */
	private function is_new_update() {

		// Check for database version.
		$old_upgrades    = get_option( $this->option_name ) ?: [];
		$current_version = $this->version;

		foreach ( $this->upgrades as $version => $method ) {
			if ( ! isset( $old_upgrades[ $version ] ) && version_compare( $current_version, $version, '>=' ) ) {
				return true;
			}
		}

		// Check forms
		if ( ImportForms::instance()->needs_update() ) {
			return true;
		}

		return false;
	}

	/**
	 * Handle all the versions upgrades
	 */
	public function run() {
		$old_upgrades    = get_option( $this->option_name, [] );
		$errors          = false;
		$current_version = $this->version;

		foreach ( $this->upgrades as $version => $method ) {
			if ( ! isset( $old_upgrades[ $version ] ) && version_compare( $current_version, $version, '>=' ) ) {

				// Run the upgrade.
				$upgrade_result = $this->$method();

				if ( is_wp_error( $upgrade_result ) ) {
					Log::add( 'Upgrade error', $upgrade_result->get_error_message() );
				}

				// Early exit the loop if an error occurs.
				if ( $upgrade_result === true || is_array( $upgrade_result ) ) {
					$old_upgrades[ $version ] = true;
				} else {
					$errors = true;
					break;
				}
			}
		}

		// Save successful upgrades.
		update_option( $this->option_name, $old_upgrades );

		if ( $errors === false ) {
			$this->updated = true;
		}
	}

	/**
	 * Call the upgrade function and conditionally show admin notice
	 */
	private function process_update_action() {
		if ( isset( $_REQUEST['dollie_db_update'] ) ) {

			if ( wp_verify_nonce( $_REQUEST['_wpnonce'], 'action' ) ) {
				$this->run();
			}

			if ( $this->updated === true ) {
				echo '<div class="notice notice-success">
            		 <p>' . esc_html__( 'Awesome, database is now at the latest version!', 'dollie' ) . '</p>
         		</div>';
			} else {
				echo '<div class="notice notice-warning">
            		 <p>' . esc_html__( 'Something went wrong. Could you please email us at support@getdollie.com so we can help you resolve this?', 'dollie' ) . '</p>
         		</div>';
			}
		}
	}

	/**
	 * @return bool|\WP_Error
	 */
	private function _upgrade_200() {
		// Update Page IDs.
		$pages_data = [
			'launch-site'    => [
				'title'    => sprintf( esc_html__( 'Launch New %s', 'dollie-setup' ), dollie()->string_variants()->get_site_type_string() ),
				'template' => 'page-templates/dollie-launch-site.php',
				'option'   => 'options_wpd_launch_page_id',
			],
			'dashboard'      => [
				'title'    => 'Dashboard',
				'template' => 'page-templates/dollie-dashboard.php',
				'option'   => 'options_wpd_dashboard_page_id',
			],
			'customers'      => [
				'title'    => 'Customers',
				'template' => 'page-templates/dollie-customers.php',
				'option'   => 'options_wpd_customers_page_id',
			],
			'customer-login' => [
				'title'    => 'Customer Login',
				'template' => 'page-templates/dollie-login.php',
				'option'   => 'options_wpd_login_page_id',
			],
		];

		foreach ( $pages_data as $slug => $page_data ) {

			// Skip if is already set
			if ( get_option( $page_data['option'] ) && get_post( $page_data['option'] ) ) {
				continue;
			}

			$the_page    = get_page_by_path( $slug );
			$the_page_id = null;

			if ( $the_page ) {
				$the_page_id = $the_page->ID;
			} else {

				$pages = get_pages(
					[
						'meta_key'   => '_wp_page_template',
						'meta_value' => $page_data['template'],
					]
				);

				foreach ( $pages as $page ) {
					$the_page_id = $page->ID;
				}

				// Create it
				if ( ! $the_page_id ) {
					$post_data = [
						'post_title'   => $page_data['title'],
						'post_content' => '',
						'post_type'    => 'page',
						'post_status'  => 'publish',
						'meta_input'   => [
							'_wp_page_template' => $page_data['template'],
						],
					];

					$the_page_id = wp_insert_post( $post_data );
				}
			}

			if ( $the_page_id ) {
				update_option( $page_data['option'], $the_page_id );
			}
		}

		return true;
	}

	/**
	 * @return bool|array
	 */
	private function _upgrade_421() {
		return ImportService::instance()->import_elementor_template();
	}

	/**
	 * @return bool
	 */
	private function _upgrade_600() {
		return AccessGroups::instance()->migrate_woo_to_acces_groups();
	}

	/**
	 * @return bool
	 */
	private function _upgrade_604() {
		global $wpdb;

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM ". $wpdb->prefix ."actionscheduler_actions WHERE status IN ('pending', 'complete') AND hook LIKE 'dollie/jobs/recurring/%'"
				)
			);

		return true;
	}

}
