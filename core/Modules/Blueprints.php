<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;
use Dollie\Core\Log;

/**
 * Class Blueprints
 *
 * @package Dollie\Core\Modules
 */
class Blueprints extends Singleton {

	const COOKIE_NAME = 'dollie_blueprint_id';
	const COOKIE_GET_PARAM = 'blueprint_id';

	/**
	 * Backups constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'init', [ $this, 'set_cookie' ], - 99999 );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_footer', [ $this, 'blueprint_notice' ] );
		add_action( 'wp_ajax_dollie_launch_site_blueprint_data', [ $this, 'ajax_get_dynamic_fields' ] );
		add_filter( 'dollie/launch_site/form_deploy_data', [ $this, 'site_launch_add_customizer_data' ], 10, 3 );
		add_filter( 'dollie/launch_site/extras_envvars', [ $this, 'site_launch_set_env_vars' ], 10, 6 );

		add_action( 'acf/save_post', [ $this, 'update_create_blueprint' ] );
		add_action( 'wp_ajax_dollie_check_dynamic_fields', [ $this, 'check_dynamic_fields' ] );
	}

	/**
	 * Enqueue scripts
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_register_script( 'dollie-launch-dynamic-data', DOLLIE_ASSETS_URL . 'js/launch-dynamic-data.js', [ 'jquery' ], DOLLIE_VERSION, true );
		wp_localize_script(
			'dollie-launch-dynamic-data',
			'wpdDynamicData',
			[
				'ajaxurl'                => admin_url( '/admin-ajax.php' ),
				'validationErrorMessage' => __( 'Please fill in the Realtime Customizer fields.', 'dollie' ),
			]
		);
	}

	/**
	 * Add dynamic blueprint customizer fields to the launch form
	 */
	public function ajax_get_dynamic_fields() {
		$blueprint = (int) $_POST['blueprint'];

		if ( empty( $blueprint ) ) {
			return;
		}

		ob_start();

		$fields = get_field( 'wpd_dynamic_blueprint_data', 'create_update_blueprint_' . $blueprint );
		if ( ! empty( $fields ) ) {

			$message = '';

			foreach ( $fields as $field ) {

				if ( empty( $field['placeholder'] ) ) {
					continue;
				}

				$message .= '<div class="acf-field-text acf-field" style="width: 50%;" data-width="50">';
				$message .= '<div class="af-label acf-label">' .
				            '<label>' . $field['name'] . '</label>' .
				            '</div>';
				$message .= '<div class="af-input acf-input">';
				$message .= '<input name="wpd_bp_data[' . $field['placeholder'] . ']" type="text" placeholder="' . $field['default_value'] . '"><br>';
				$message .= '</div>';
				$message .= '</div>';
			}

			\Dollie\Core\Utils\Tpl::load(
				'notice',
				[
					'icon'    => 'fas fa-exclamation-circle',
					'title'   => __( 'Realtime Customizer', 'dollie' ),
					'message' => '<div>' . __( 'Make sure to set your site details below. We automatically launch the site with your information.', 'dollie' )
					             . '</div>'
					             . $message,
				],
				true
			);
		}

		wp_send_json_success(
			[
				'fields' => ob_get_clean(),
			]
		);

		exit;
	}

	/**
	 * Show default blueprint
	 *
	 * @return bool
	 */
	public static function show_default_blueprint() {
		return get_field( 'wpd_show_default_blueprint', 'options' );
	}

	/**
	 * Get all available blueprints
	 *
	 * @param string $value_format image|title
	 *
	 * @return array
	 */
	public function get( $value_format = 'image' ) {
		$data = [];

		$sites = get_posts(
			[
				'post_type'      => 'container',
				'posts_per_page' => - 1,
				'meta_query'     => [
					'relation' => 'AND',
					[
						'key'   => 'wpd_blueprint_created',
						'value' => 'yes',
					],
					[
						'key'   => 'wpd_is_blueprint',
						'value' => 'yes',
					],
					[
						'key'     => 'wpd_installation_blueprint_title',
						'compare' => 'EXISTS',
					],
				],
			]
		);

		if ( empty( $sites ) ) {
			return $data;
		}

		foreach ( $sites as $site ) {
			$private = get_field( 'wpd_private_blueprint', $site->ID );

			if ( 'yes' === $private ) {
				continue;
			}

			if ( 'image' === $value_format ) {

				if ( get_field( 'wpd_blueprint_image', $site->ID ) === 'custom' ) {
					$image = get_field( 'wpd_blueprint_custom_image', $site->ID );
					if ( is_array( $image ) ) {
						$image = $image['url'];
					}
				} else {
					$image = get_post_meta( $site->ID, 'wpd_site_screenshot', true );
				}
				$value = '<img data-toggle="tooltip" data-placement="bottom" ' .
				         'data-tooltip="' . esc_attr( get_post_meta( $site->ID, 'wpd_installation_blueprint_description', true ) ) . '" ' .
				         'class="fw-blueprint-screenshot acf__tooltip" src=' . $image . '>' .
				         esc_html( get_post_meta( $site->ID, 'wpd_installation_blueprint_title', true ) );
			} else {
				$value = get_post_meta( $site->ID, 'wpd_installation_blueprint_title', true );
			}

			$data[ $site->ID ] = $value;
		}

		return apply_filters( 'dollie/blueprints', $data );
	}

	/**
	 * Get available blueprints
	 *
	 * @param int $site_id
	 *
	 * @return array
	 */
	public function get_by_site( $site_id = null ) {

		$sub_page = get_query_var( 'sub_page' );
		$site     = dollie()->get_current_object( $site_id );

		$secret = get_post_meta( $site->id, 'wpd_container_secret', true );

		$request_get_blueprint = Api::post(
			Api::ROUTE_BLUEPRINT_GET,
			[
				'container_url'    => dollie()->get_container_url( $site->id, true ),
				'container_secret' => $secret,
			]
		);

		$blueprints_response = Api::process_response( $request_get_blueprint, null );

		if ( false === $blueprints_response || 500 === $blueprints_response['status'] ) {
			return [];
		}

		$blueprints = dollie()->maybe_decode_json( $blueprints_response['body'] );

		if ( ! is_array( $blueprints ) || empty( $blueprints ) ) {
			return [];
		}

		set_transient( 'dollie_' . $site->slug . '_total_blueprints', count( $blueprints ), MINUTE_IN_SECONDS * 1 );
		update_post_meta( $site->id, 'wpd_installation_blueprints_available', count( $blueprints ) );

		return $blueprints;
	}

	/**
	 * Set blueprint cookie
	 */
	public function set_cookie() {
		if ( isset( $_GET[ self::COOKIE_GET_PARAM ] ) && (int) $_GET[ self::COOKIE_GET_PARAM ] > 0 ) {
			$cookie_id = sanitize_text_field( $_GET[ self::COOKIE_GET_PARAM ] );
		}

		// No Cookies set? Check is parameter are valid.
		if ( isset( $cookie_id ) ) {
			setcookie( self::COOKIE_NAME, $cookie_id, time() + ( 86400 * 30 ), '/' );
		}
	}

	/**
	 * Blueprint notice
	 *
	 * @return void
	 */
	public function blueprint_notice() {
		$deploying = 'pending' === \Dollie\Core\Modules\Container::instance()->get_status();

		if ( ! is_singular( 'container' ) || ! dollie()->is_blueprint() || $deploying ) {
			return;
		}

		$post_id = get_the_ID();

		if ( dollie()->is_blueprint_staging( $post_id ) ) { ?>
            <div class="dol-fixed dol-w-full dol-bg-gray-700 dol-p-3 dol-text-white dol-bottom-0 dol-left-0 dol-z-50 dol-text-center">
                <i class="fas fa-copy"></i>
                <a class="dol-text-white hover:dol-text-white" href=" <?php echo get_permalink(); ?>/blueprints">
                    <strong><?php esc_html_e( 'Staging', 'dollie' ); ?></strong>
                    - <?php esc_html_e( 'This Blueprint is still in staging mode. Click here to make it available for your customers.', 'dollie' ); ?>
                </a>
            </div>
			<?php
		} else {
			$blueprint_time = get_post_meta( $post_id, 'wpd_blueprint_time', true );
			?>
            <div class="dol-fixed dol-w-full dol-bg-secondary dol-p-3 dol-text-white dol-bottom-0 dol-left-0 dol-z-50 dol-text-center">
                <a class="dol-text-white hover:dol-text-white" href="<?php echo get_permalink() . '/blueprints'; ?>">
                    <i class="fas fa-copy"></i> <strong><?php esc_html_e( 'Live', 'dollie' ); ?></strong> -
					<?php printf( __( 'This Blueprint was last updated at %1$s. Made changes since then? Don’t forget to update this blueprint.', 'dollie' ), $blueprint_time ); ?>
                </a>
            </div>
		<?php } ?>
		<?php
	}

	/**
	 * Get available blueprints
	 *
	 * @param null $container_id
	 *
	 * @return array
	 */
	public function get_available( $container_id = null ) {
		$container = dollie()->get_current_object( $container_id );

		$blueprints           = $this->get_by_site( $container->id );
		$formatted_blueprints = [];

		foreach ( $blueprints as $blueprint ) {
			$info = explode( '|', $blueprint );

			if ( strpos( $info[1], 'MB' ) !== false ) {
				$get_mb_size = str_replace( 'MB', '', $info[1] );
				$size        = $get_mb_size . ' MB';
			} else {
				$size = $info[1];
			}

			// Time is first part but needs to be split.
			$backup_date = explode( '_', $info[0] );

			// Date of backup.
			$date        = strtotime( $backup_date[0] );
			$raw_time    = str_replace( '-', ':', $backup_date[1] );
			$pretty_time = date( 'g:i a', strtotime( $raw_time ) );

			$formatted_blueprints[] = [
				'size' => $size,
				'date' => date( 'd F y', $date ),
				'time' => $pretty_time,
			];
		}

		return $formatted_blueprints;
	}

	/**
	 * Integrate the blueprint customizer form data into the deploy data variable
	 *
	 * @param $deploy_data
	 * @param $domain
	 * @param $blueprint
	 *
	 * @return mixed
	 */
	public function site_launch_add_customizer_data( $deploy_data, $domain, $blueprint ) {
		if ( isset( $_POST['wpd_bp_data'] ) && is_array( $_POST['wpd_bp_data'] ) ) {
			$bp_customizer = [];
			$bp_fields     = get_field( 'wpd_dynamic_blueprint_data', 'create_update_blueprint_' . $blueprint );

			if ( ! empty( $bp_fields ) ) {
				foreach ( $bp_fields as $bp_field ) {
					if ( ! empty( $bp_field['placeholder'] ) && isset( $_POST['wpd_bp_data'][ $bp_field['placeholder'] ] ) ) {
						$bp_customizer[ $bp_field['placeholder'] ] = $_POST['wpd_bp_data'][ $bp_field['placeholder'] ];
					}
				}
			}

			if ( ! empty( $bp_customizer ) ) {
				$deploy_data['bp_customizer'] = $bp_customizer;
			}
		}

		return $deploy_data;
	}

	/**
	 * Add the blueprint customizer variables to the API request
	 *
	 * @param $vars
	 * @param $domain
	 * @param $user_id
	 * @param $email
	 * @param $blueprint
	 * @param $deploy_data
	 *
	 * @return mixed
	 */
	public function site_launch_set_env_vars( $vars, $domain, $user_id, $email, $blueprint, $deploy_data ) {
		if ( isset( $deploy_data['bp_customizer'] ) && is_array( $deploy_data['bp_customizer'] ) ) {
			$vars['dynamic_fields'] = $deploy_data['bp_customizer'];
		}

		return $vars;
	}

	/**
	 * Update or create blueprint
	 *
	 * @param string $post_id
	 *
	 * @return void
	 */
	public function update_create_blueprint( $post_id ) {
		$container = dollie()->get_current_object( get_the_ID() );

		if ( strpos( $post_id, 'create_update_blueprint' ) === false || 'container' !== get_post_type() || ! dollie()->is_blueprint() || ! $container ) {
			return;
		}

		$container_uri = dollie()->get_wp_site_data( 'uri', get_the_ID() );

		Api::process_response( Api::post( Api::ROUTE_BLUEPRINT_CREATE_OR_UPDATE, [ 'container_uri' => $container_uri ] ) );

		update_post_meta( get_the_ID(), 'wpd_blueprint_created', 'yes' );
		update_post_meta( get_the_ID(), 'wpd_blueprint_time', @date( 'd/M/Y:H:i' ) );

		dollie()->container_screenshot( $container_uri, true );

		Log::add_front( Log::WP_SITE_BLUEPRINT_DEPLOYED, $container, $container->slug );
	}

	public function check_dynamic_fields() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'check_dynamic_fields_nonce' ) ) {
			wp_send_json_error();
		}

		$container = dollie()->get_current_object( $_REQUEST['container'] );

		if ( ! $container ) {
			wp_send_json_error();
		}

		$dynamic_fields = get_field( 'wpd_dynamic_blueprint_data', 'create_update_blueprint_' . $container->id );
		$check_fields   = [];

		foreach ( $dynamic_fields as $field ) {
			$check_fields[] = [
				'search' => $field['placeholder'],
			];
		}

		$response = Api::process_response(
			Api::post(
				Api::ROUTE_BLUEPRINT_CHECK_DYNAMIC_FIELDS,
				[
					'container_uri' => dollie()->get_container_url( $container->id ),
					'fields'        => $check_fields,
				]
			)
		);

		$success = true;

		foreach ( $response as $placeholder => $status ) {
			if ( ! $status ) {
				$success = false;
			}
		}

		if ( $success ) {
			ob_start();
			?>
            <div class="dol-w-full dol-items-center dol-px-4 dol-py-2 dol-text-base dol-leading-6 dol-rounded dol-text-white dol-bg-green-600 dol-font-bold">
				<?php esc_html_e( 'All customizer\'s fields were successfully found in the blueprint.', 'dollie' ); ?>
            </div>
			<?php
			$message = ob_get_clean();
		} else {
			ob_start();
			?>
            <div class="dol-w-full dol-items-center dol-px-4 dol-py-2 dol-text-base dol-leading-6 dol-rounded dol-text-white dol-bg-red-500">
                <div class="dol-font-bold"><?php esc_html_e( 'The following fields were not found in the blueprint:', 'dollie' ); ?></div>

                <ul>
					<?php foreach ( $response as $placeholder => $status ) : ?>
						<?php if ( ! $status ) : ?>
                            <li><?php echo $placeholder; ?></li>
						<?php endif; ?>
					<?php endforeach; ?>
                </ul>

                <div class="dol-mt-4 dol-text-sm dol-medium">
					<?php esc_html_e( 'Make sure you add the missing fields into your blueprint or completly remove them from the customizer\'s fields and then update it.', 'dollie' ); ?>
                </div>
            </div>
			<?php
			$message = ob_get_clean();
		}

		wp_send_json_success( [ 'output' => $message ] );
	}
}
