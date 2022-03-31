<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Api\BlueprintApi;
use Dollie\Core\Singleton;
use Dollie\Core\Log;

/**
 * Class Blueprints
 *
 * @package Dollie\Core\Modules
 */
class Blueprints extends Singleton {
	use BlueprintApi;

	/**
	 * Blueprints constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'init', [ $this, 'set_cookie' ], -99999 );
		add_action( 'wp_footer', [ $this, 'blueprint_notice' ] );
		add_action( 'wp_ajax_dollie_launch_site_blueprint_data', [ $this, 'ajax_get_dynamic_fields' ] );
		add_filter( 'dollie/launch_site/form_deploy_data', [ $this, 'site_launch_add_customizer_data' ], 10, 3 );
		add_filter( 'dollie/launch_site/extras_envvars', [ $this, 'site_launch_set_env_vars' ], 10, 6 );

		add_action( 'wp_ajax_dollie_check_dynamic_fields', [ $this, 'check_dynamic_fields' ] );
	}

	/**
	 * Add dynamic blueprint customizer fields to the launch form
	 */
	public function ajax_get_dynamic_fields() {
		$blueprint = dollie()->get_container( (int) $_POST['blueprint'] );

		if ( is_wp_error( $blueprint ) ) {
			return;
		}

		ob_start();

		$fields = get_field( 'wpd_dynamic_blueprint_data', 'create_update_blueprint_' . $blueprint->get_id() );
		// $fields = $blueprint->get_dynamic_fields();

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

			dollie()->load_template(
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
				'posts_per_page' => -1,
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
	 * Set blueprint cookie
	 */
	public function set_cookie() {
		if ( isset( $_GET[ DOLLIE_BLUEPRINTS_COOKIE_PARAM ] ) && (int) $_GET[ DOLLIE_BLUEPRINTS_COOKIE_PARAM ] > 0 ) {
			$cookie_id = sanitize_text_field( $_GET[ DOLLIE_BLUEPRINTS_COOKIE_PARAM ] );
		}

		// No Cookies set? Check is parameter are valid.
		if ( isset( $cookie_id ) ) {
			setcookie( DOLLIE_BLUEPRINTS_COOKIE, $cookie_id, time() + ( 86400 * 30 ), '/' );
		}
	}

	/**
	 * Blueprint notice
	 *
	 * @return void
	 */
	public function blueprint_notice() {
		if ( ! is_singular( 'container' ) ) {
			return;
		}

		$container = dollie()->get_container();

		if ( is_wp_error( $container ) || ! $container->is_blueprint() || 'Deploying' !== $container->get_status() ) {
			return;
		}

		$updated_time = $container->get_changes_update_time();
		?>

		<?php if ( ! $updated_time ) : ?>
			<div class="dol-fixed dol-w-full dol-bg-gray-700 dol-p-3 dol-text-white dol-bottom-0 dol-left-0 dol-z-50 dol-text-center">
				<?php echo dollie()->icon()->blueprint(); ?>
				<a class="dol-text-white hover:dol-text-white" href=" <?php echo $container->get_permalink(); ?>/blueprints">
					<strong><?php esc_html_e( 'Staging', 'dollie' ); ?></strong>
					- <?php esc_html_e( 'This Blueprint is still in staging mode. Click here to make it available for your customers.', 'dollie' ); ?>
				</a>
			</div>
		<?php else : ?>
			<div class="dol-fixed dol-w-full dol-bg-secondary dol-p-3 dol-text-white dol-bottom-0 dol-left-0 dol-z-50 dol-text-center">
				<a class="dol-text-white hover:dol-text-white" href="<?php echo $container->get_permalink() . '/blueprints'; ?>">
					<?php echo dollie()->icon()->blueprint(); ?><strong> <?php esc_html_e( 'Live', 'dollie' ); ?></strong> -
					<?php printf( __( 'This Blueprint was last updated at %1$s. Made changes since then? Don’t forget to update this blueprint.', 'dollie' ), $updated_time ); ?>
				</a>
			</div>
		<?php endif; ?>

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
		$container = dollie()->get_container( $container_id );

		if ( is_wp_error( $container ) || ! $container->is_site() ) {
			return [];
		}

		$blueprints = $container->get_available_blueprints();

		if ( is_wp_error( $blueprints ) ) {
			return [];
		}

		return $blueprints;
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
	 * Check dynamic fields
	 *
	 * @return void
	 */
	public function check_dynamic_fields() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'check_dynamic_fields_nonce' ) ) {
			wp_send_json_error();
		}

		$container = dollie()->get_container( $_REQUEST['container'] );

		if ( is_wp_error( $container ) ) {
			wp_send_json_error();
		}

		$dynamic_fields = get_field( 'wpd_dynamic_blueprint_data', 'create_update_blueprint_' . $container->get_id() );
		$fields         = [];

		foreach ( $dynamic_fields as $field ) {
			$fields[] = $field['placeholder'];
		}

		$response = $container->check_dynamic_fields( $container->hash, $fields );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error();
		}

		$missing_fields = array_filter(
			$response,
			function( $value ) {
				return false === $value;
			}
		);

		if ( empty( $missing_fields ) ) {
			ob_start();
			?>
			<div class="dol-w-full dol-items-center dol-px-4 dol-py-2 dol-text-base dol-leading-6 dol-rounded dol-text-white dol-bg-green-600">
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
