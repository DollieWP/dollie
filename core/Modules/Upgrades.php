<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Log;
use Dollie\Core\Singleton;
use WP_Error;

/**
 * Class Upgrades
 * @package Dollie\Core\Modules
 */
class Upgrades extends Singleton {

	/**
	 * Option name that gets saved in the options database table
	 *
	 * @var string
	 */
	private $option_name = 'dollie_db_version';

	/**
	 * Current plugin version
	 * @var string
	 */
	private $version = DOLLIE_VERSION;

	/**
	 * Keep track if the data was updated during current request
	 * @var bool
	 */
	private $updated = false;

	/**
	 * Upgrade versions and method callbacks
	 * @var array
	 */
	private $upgrades = [
		'2.0.0' => '_upgrade_200',
	];

	/**
	 * Upgrades constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'admin_notices', [ $this, 'admin_notice' ], 20 );
	}

	/**
	 * Show admin notice to update Dollie Database
	 */
	public function admin_notice() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( $this->is_new_update() ) {

			$this->process_update_action();

			// If we still need to show the message
			if ( ! $this->updated ) {
				$url = wp_nonce_url( add_query_arg( 'dollie_db_update', '' ), 'action' );
				?>

                <div class="notice dollie-notice">

                    <div class="dollie-inner-message">

                        <img width="60" src="<?php echo esc_url( DOLLIE_URL . 'assets/img/active.png' ); ?>">
                        <div class="dollie-message-center">
                            <h3><?php esc_html_e( 'Database update required', 'dollie' ); ?></h3>
                            <p>
								<?php
								echo wp_kses_post( sprintf(
									__( '<strong>Dollie</strong> needs to update your database to the latest version. Please make sure to create a backup first.', 'dollie' ),
									esc_url( $url )
								) ); ?>
                            </p>
                        </div>
                        <div class="dollie-msg-button-right">
							<?php echo wp_kses_post( sprintf( __( '<a href="%s">Update now</a>', 'dollie' ), esc_url( $url ) ) ); ?>
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
	 * Call the upgrade function and conditionally show admin notice
	 */
	private function process_update_action() {
		if ( isset( $_REQUEST['dollie_db_update'] ) ) {
			$nonce = $_REQUEST['_wpnonce'];

			if ( wp_verify_nonce( $nonce, 'action' ) ) {
				$this->do_upgrades();
			}

			if ( $this->updated === true ) {
				echo '<div class="notice notice-success">
            		 <p>' . esc_html__( 'Awesome, database is now at the latest version!', 'dollie' ) . '</p>
         		</div>';
			} else {
				echo '<div class="notice notice-warning">
            		 <p>' . esc_html__( 'Something went wrong, please check logs.', 'dollie' ) . '</p>
         		</div>';
			}
		}
	}


	/**
	 * Handle all the versions upgrades
	 */
	public function do_upgrades() {
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
				if ( $upgrade_result === true ) {
					$old_upgrades[ $version ] = true;
				} else {
					$errors = true;
					break;
				}
			}
		}

		// Save successful upgrades.
		update_option( $this->option_name, $old_upgrades );

		// Try to update forms on each plugin update.
		$forms_result = ImportForms::instance()->import_forms();
		if ( ! $forms_result ) {
			$errors = true;
		}

		if ( $errors === false ) {
			$this->updated = true;
		}
	}

	/**
	 * @return bool|WP_Error
	 */
        private function _upgrade_200() {

		// Update Page IDs.
		$pages_data = [
			'launch-site'    => [
				'title'    => 'Launch New Site',
				'template' => 'page-templates/dollie-launch-site.php',
				'option'   => 'options_wpd_launch_page_id',
			],
			'dashboard'      => [
				'title'    => 'Dashboard',
				'template' => 'page-templates/dollie-dashboard.php',
				'option'   => 'options_wpd_dashboard_page_id',
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

				$pages = get_pages( array(
					'meta_key'   => '_wp_page_template',
					'meta_value' => $page_data['template']
				) );

				foreach ( $pages as $page ) {
					$the_page_id = $page->ID;
				}

				//create it
				if ( ! $the_page_id ) {
					$post_data = array(
						'post_title'   => $page_data['title'],
						'post_content' => '',
						'post_type'    => 'page',
						'post_status'  => 'publish',
						'meta_input'   => array(
							'_wp_page_template' => $page_data['template'],
						),
					);

					$the_page_id = wp_insert_post( $post_data );
				}
			}

			if ( $the_page_id ) {
				update_option( $page_data['option'], $the_page_id );
			}
		}

		return true;
	}
}
