<?php

namespace Dollie\Core\Modules\Forms;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Log;
use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;

/**
 * Class CreateBlueprint
 *
 * @package Dollie\Core\Modules\Forms
 */
class CreateBlueprint extends Singleton {

	/**
	 * @var string
	 */
	private $form_key = 'form_dollie_create_blueprint';

	/**
	 * CreateBlueprint constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'acf/init', [ $this, 'acf_init' ] );
	}

	/**
	 * Init ACF
	 */
	public function acf_init() {
		// Form args
		add_filter( 'af/form/args/key=' . $this->form_key, [ $this, 'change_form_args' ] );

		// Form submission action.
		add_action( 'af/form/validate/key=' . $this->form_key, [ $this, 'validate_form' ], 10, 2 );

		add_action( 'af/form/before_submission/key=' . $this->form_key, [ $this, 'submission_callback' ], 10, 3 );
	}

	/**
	 * Callback
	 *
	 * @param $form
	 * @param $fields
	 * @param $args
	 */
	public function submission_callback( $form, $fields, $args ) {
		$container_id = isset( $_POST['dollie_post_id'] ) ? (int) $_POST['dollie_post_id'] : 0;
		$container    = dollie()->get_current_object( $container_id );

		if ( $container_id === 0 ) {
			return;
		}

		$container_uri = dollie()->get_wp_site_data( 'uri', $container_id );

		Api::process_response( Api::post( Api::ROUTE_BLUEPRINT_CREATE_OR_UPDATE, [ 'container_uri' => $container_uri ] ) );

		update_post_meta( $container_id, 'wpd_blueprint_created', 'yes' );
		update_post_meta( $container_id, 'wpd_blueprint_time', @date( 'd/M/Y:H:i' ) );

		dollie()->container_screenshot( $container_uri, true );

		Log::add_front( Log::WP_SITE_BLUEPRINT_DEPLOYED, $container, $container->slug );
	}

	/**
	 * Validate form
	 *
	 * @param $form
	 * @param $args
	 */
	public function validate_form( $form, $args ) {
		if ( ! af_get_field( 'confirmation' ) ) {
			af_add_error( 'confirmation', __( 'Please confirm blueprint creation', 'dollie' ) );
		}
	}

	/**
	 * Change form args
	 *
	 * @param $args
	 *
	 * @return mixed
	 */
	public function change_form_args( $args ) {
		$args['submit_text'] = __( 'Deploy Blueprint', 'dollie' );

		return $args;
	}


}
