<?php

namespace Dollie\Core\Modules\Forms;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Modules\Sites\WP;
use Dollie\Core\Singleton;
use Dollie\Core\Utils\Tpl;

/**
 * Class LaunchSite
 * @package Dollie\Core\Modules\Forms
 */
class LaunchSite extends Singleton {

	private $form_key = 'form_dollie_launch_site';

	/**
	 * LaunchSite constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'acf/init', [ $this, 'acf_init' ] );

	}

	public function acf_init() {

		// Placeholders/Change values
		add_filter( 'acf/prepare_field/name=site_blueprint', [ $this, 'populate_blueprints' ] );
		add_filter( 'acf/prepare_field/name=site_url', [ $this, 'append_site_url' ] );

		// Form args
		add_filter( 'af/form/after_fields/key=' . $this->form_key, [ $this, 'add_modal_data' ], 10, 2 );
		add_filter( 'af/form/args/key=' . $this->form_key, [ $this, 'change_form_args' ] );

		// Form submission action.
		add_action( 'af/form/before_submission/key=' . $this->form_key, [ $this, 'submission_callback' ], 10, 3 );

	}

	public function submission_callback( $form, $fields, $args ) {
		$domain    = af_get_field( 'site_url' );
		$email     = af_get_field( 'site_admin_email' );
		$blueprint = isset( $_COOKIE['dollie_blueprint_id'] ) ? $_COOKIE['dollie_blueprint_id'] : af_get_field( 'site_blueprint' );
		$user_id = get_current_user_id();

		$deploy_data = WP::instance()->deploy_site( $email, $domain, $user_id, $blueprint );

		if ( is_wp_error( $deploy_data ) ) {
			af_add_submission_error( $deploy_data->get_error_message() );
		}
	}

	public function append_site_url( $field ) {
		$field['append'] = DOLLIE_DOMAIN;

		return $field;
	}

	public function add_modal_data() {
		Tpl::load( DOLLIE_MODULE_TPL_PATH . 'launch-splash', [], true );
	}

	public function change_form_args( $args ) {
		$args['redirect']    = add_query_arg( 'site', 'new', $args['redirect'] );
		$args['submit_text'] = esc_html__( 'Launch New Site', 'dollie' );

		return $args;
	}

	public function populate_blueprints( $field ) {
		$query = new \WP_Query( [
			'post_type'      => 'container',
			'posts_per_page' => 1000,
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
				]
			],
			'p'              => isset( $_COOKIE['dollie_blueprint_id'] ) ? $_COOKIE['dollie_blueprint_id'] : '',
		] );

		$choices = [];

		// Build field options array.
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$private = get_field( 'wpd_private_blueprint' );

				if ( $private === 'yes' && ! current_user_can( 'manage_options' ) ) {
					continue;
				}

				if ( get_field( 'wpd_blueprint_image' ) === 'custom' ) {
					$image = get_field( 'wpd_blueprint_custom_image' );
				} elseif ( get_field( 'wpd_blueprint_image' ) === 'theme' ) {
					$image = wpthumb( get_post_meta( get_the_ID(), 'wpd_installation_site_theme_screenshot', true ), 'width=900&crop=0' );
				} else {
					$image = get_post_meta( get_the_ID(), 'wpd_site_screenshot', true );
				}

				$choices[ get_the_ID() ] = '<img data-toggle="tooltip" data-placement="bottom" ' .
				                           'title="' . get_post_meta( get_the_ID(), 'wpd_installation_blueprint_description', true ) . '" ' .
				                           'class="fw-blueprint-screenshot" src=' . $image . '>' .
				                           get_post_meta( get_the_ID(), 'wpd_installation_blueprint_title', true );

			}

			$field['choices'] = $choices;

		}

		// Hide the blueprints field
		if ( isset( $_COOKIE['dollie_blueprint_id'] ) || empty( $choices ) ) {
			$field['class'] = 'acf-hidden';
		}

		// return the field
		return $field;

	}

}
