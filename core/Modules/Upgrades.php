<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Log;
use Dollie\Core\Singleton;
use Elementor\Plugin;
use WP_Error;

/**
 * Class Upgrades
 *
 * @package Dollie\Core\Modules
 */
class Upgrades extends Singleton {


	const API_URL = 'https://api.getdollie.com/releases/packages/';

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
		'4.1.4' => '_upgrade_400',
		'4.2.3' => '_upgrade_421',
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

		if ( $this->is_new_update() && dollie()->is_api_connected() ) {

			$this->process_update_action();

			// If we still need to show the message
			if ( ! $this->updated ) {
				$url = wp_nonce_url( add_query_arg( 'dollie_db_update', '' ), 'action' );
				?>

				<div class="notice dollie-notice">

					<div class="dollie-inner-message">


						<div class="dollie-message-center">
							<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
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
		 $old_upgrades   = get_option( $this->option_name, [] );
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
	 * @return bool|WP_Error
	 */
	private function _upgrade_200() {
		// Update Page IDs.
		$pages_data = [
			'launch-site'    => [
				'title'    => sprintf( esc_html__( 'Launch New %s', 'dollie-setup' ), dollie()->get_site_type_string() ),
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
	 * @return bool
	 */
	private function _upgrade_400() {
		$pages_data = [
			'launch-site'    => [
				'title'     => sprintf( esc_html__( 'Launch New %s', 'dollie-setup' ), dollie()->get_site_type_string() ),
				'option'    => 'options_wpd_launch_page_id',
				'tpl'       => 'elementor_header_footer',
				'post_type' => 'page',
			],
			'dashboard'      => [
				'title'     => 'Dashboard',
				'option'    => 'options_wpd_dashboard_page_id',
				'tpl'       => 'elementor_header_footer',
				'post_type' => 'page',
			],
			'sites'          => [
				'title'     => 'Sites',
				'option'    => 'options_wpd_sites_page_id',
				'tpl'       => 'elementor_header_footer',
				'post_type' => 'page',
			],
			'customer-login' => [
				'title'     => 'Customer Login',
				'option'    => 'options_wpd_login_page_id',
				'tpl'       => 'elementor_canvas',
				'post_type' => 'page',
			],
			'single'         => [
				'title'     => 'Site template',
				'option'    => 'options_wpd_site_template_id',
				'tpl'       => 'elementor_header_footer',
				'post_type' => 'elementor_library',
			],
		];

		$this->import_pages( $pages_data );

		return true;
	}

	/**
	 * @return bool|WP_Error
	 */
	private function _upgrade_421() {
		$pages_data = [
			'launch-blueprint' => [
				'title'     => 'Launch New Blueprint',
				'option'    => 'options_wpd_launch_blueprint_page_id',
				'tpl'       => 'elementor_header_footer',
				'post_type' => 'page',
			],
		];

		$this->import_pages( $pages_data );

		return true;
	}

	private function import_pages( $pages_data ) {

		foreach ( $pages_data as $slug => $page_data ) {

			// Update existing pages or create new ones
			$existing_id = get_option( $page_data['option'] );

			if ( ! $existing_id || ! get_post( $existing_id ) ) {

				// try to get the page by slug
				$the_page    = get_page_by_path( $slug, OBJECT, $page_data['post_type'] );
				$existing_id = null;

				if ( $the_page ) {
					$existing_id = $the_page->ID;
				} else {
					$post_data = [
						'post_title'    => $page_data['title'],
						'post_content'  => '',
						'post_type'     => $page_data['post_type'],
						'post_status'   => 'publish',
						'page_template' => $page_data['tpl'],
					];

					$existing_id = wp_insert_post( $post_data );
				}

				if ( $existing_id ) {
					update_option( $page_data['option'], $existing_id );
				}
			}

			if ( empty( $existing_id ) ) {
				continue;
			}

			// Add Elementor content
			$source = Plugin::instance()->templates_manager->get_source( 'dollie' );
			$args   = [
				'template_id' => $page_data['post_type'] . '-' . $slug,
			];

			$el_data = $source->get_data( $args );

			if ( ! is_wp_error( $el_data ) ) {

				if ( isset( $el_data['type'] ) ) {
					update_post_meta( $existing_id, '_elementor_template_type', $el_data['type'] );
				}

				update_post_meta( $existing_id, '_elementor_edit_mode', 'builder' );
				update_post_meta( $existing_id, '_wp_page_template', $page_data['tpl'] );

				$el_data['id'] = $existing_id;
				$source->update_item( $el_data );
			}
		}
	}
}
