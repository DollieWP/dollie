<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;

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
		add_action( 'wp_ajax_dollie_launch_site_blueprint_data', [ $this, 'ajax_get_dynamic_fields' ] );
		add_filter( 'dollie/launch_site/form_deploy_data', [ $this, 'site_launch_add_customizer_data' ], 10, 3 );
		add_filter( 'dollie/launch_site/extras_envvars', [ $this, 'site_launch_set_env_vars' ], 10, 6 );

	}

	public function enqueue_scripts() {

		if ( ! is_page( dollie()->get_launch_page_id() ) ) {
			return;
		}

		wp_register_script( 'dollie-launch-dynamic-data', DOLLIE_ASSETS_URL . 'js/launch-dynamic-data.js', [ 'jquery' ], DOLLIE_VERSION, true );
		wp_localize_script(
			'dollie-launch-dynamic-data',
			'wpdDynamicData',
			[
				'ajaxurl' => admin_url( '/admin-ajax.php' ),
			]
		);

		wp_enqueue_script( 'dollie-launch-dynamic-data' );
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

		$fields = get_field( 'wpd_dynamic_blueprint_data', $blueprint );
		if ( ! empty( $fields ) ) {

			$message = '';

			foreach ( $fields as $field ) {
				$message .= '<div class="acf-field-text acf-field" style="width: 50%;" data-width="50">';
				$message .= '<div class="af-label acf-label">' .
				            '<label>' . $field['name'] . '</label>' .
				            '</div>';
				$message .= '<div class="af-input acf-input">';
				$message .= '<input name="wpd_bp_data[' . $field['placeholder'] . ']" type="text" value="' . $field['default_value'] . '"><br>';
				$message .= '</div>';
				$message .= '</div>';
			}

			\Dollie\Core\Utils\Tpl::load(
				'notice',
				[
					'icon'    => 'fas fa-exclamation-circle',
					'title'   => __( 'Blueprint Customizer', 'dollie' ),
					'message' => __( 'Make sure to set your site details below. We automatically deploy the site with your information.', 'dollie' )
					             . $message,
				],
				true
			);

		}

		wp_send_json_success( [
			'fields' => ob_get_clean()
		] );

		exit;
	}

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
				$image = '';

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

		return $data;
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

		// $blueprints = json_decode( $blueprints, true );

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

			// Time is first part but needs to be split
			$backup_date = explode( '_', $info[0] );

			// Date of backup
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
			$bp_fields     = get_field( 'wpd_dynamic_blueprint_data', $blueprint );

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

}
