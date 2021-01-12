<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;
use Dollie\Core\Log;

/**
 * Class ContainerRegistration
 *
 * @package Dollie\Core\Modules
 */
class ContainerRegistration extends Singleton {

	/**
	 * ContainerRegistration constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'wp', [ $this, 'add_worker_key' ] );
		add_action( 'template_redirect', [ $this, 'add_worker_node' ], 10000 );
	}

	/**
	 * Add worker key
	 */
	public function add_worker_key() {
		if ( get_option( 'wpd_rundeck_key' ) === false ) {
			update_option( 'wpd_rundeck_key', dollie()->random_string( 12 ) );
		}
	}

	/**
	 * Add worker node
	 */
	public function add_worker_node() {
		if ( did_action( 'template_redirect' ) === 1 && is_singular( 'container' ) ) {
			$this->register_worker_node();
		}
	}

	/**
	 * Register worker node
	 *
	 * @param null $id
	 */
	public function register_worker_node( $id = null ) {
		$current_query = dollie()->get_current_object( $id );

		$post_id = $current_query->id;

		$is_node_added = get_post_meta( $post_id, 'wpd_node_added', true );
		$container_url = dollie()->get_container_url( $post_id, true );

		// Only run if the node has not been added.
		if ( ! empty( $container_url ) && 'yes' !== $is_node_added ) {

			$data = [
				'container_url' => $container_url,
				'site_url'      => get_site_url(),
				'email'         => get_post_meta( $post_id, 'wpd_container_launched_by', true ),
				'ip'            => dollie()->get_wp_site_data( 'ip', $post_id ),
				'port'          => dollie()->get_wp_site_data( 'ssh_port', $post_id ),
			];

			$request_create_node = Api::post( Api::ROUTE_NODES_CREATE, $data );

			if ( Api::process_response( $request_create_node ) === false ) {
				Log::add( 'Node could not be registered for ' . $current_query->slug, print_r( $request_create_node, true ), 'error' );

				return;
			}

			update_post_meta( $post_id, 'wpd_node_added', 'yes' );
		}
	}

}
