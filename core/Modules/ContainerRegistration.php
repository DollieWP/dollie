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

	private function get_string_between( $string, $start, $end ) {
		$string = ' ' . $string;
		$ini    = strpos( $string, $start );
		if ( $ini === 0 ) {
			return '';
		}
		$ini += strlen( $start );
		$len = strpos( $string, $end, $ini ) - $ini;

		return substr( $string, $ini, $len );
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
	 * Get worker nodes
	 *
	 * @return string
	 */
	public function get_worker_nodes() {
		$request_nodes_get = Api::post( Api::ROUTE_NODES_GET );

		$response_nodes_get = json_decode( wp_remote_retrieve_body( $request_nodes_get ), true );

		if ( $response_nodes_get['status'] === 500 ) {
			return '';
		}

		return $response_nodes_get['body'];
	}

	/**
	 * Register worker node
	 *
	 * @param null $id
	 */
	public function register_worker_node( $id = null ) {
		$currentQuery = dollie()->get_current_object( $id );

		$post_id = $currentQuery->id;

		$is_node_added = get_post_meta( $post_id, 'wpd_node_added', true );
		$ip            = get_post_meta( $post_id, 'wpd_container_ip', true );
		$port          = get_post_meta( $post_id, 'wpd_container_port', true );
		$email         = get_post_meta( $post_id, 'wpd_container_launched_by', true );

		// Only run if the node has not been added.
		if ( $is_node_added !== 'yes' ) {
			$request_create_node = Api::post( Api::ROUTE_NODES_CREATE, [
				'container_url' => dollie()->get_container_url( $post_id ),
				'site_url'      => get_site_url(),
				'email'         => $email,
				'ip'            => $ip,
				'port'          => $port
			] );

			if ( Api::process_response( $request_create_node ) === false ) {
				Log::add( 'Node could not be registered for ' . $currentQuery->slug, print_r( $request_create_node, true ), 'error' );

				return;
			}

			update_post_meta( $post_id, 'wpd_node_added', 'yes' );
			Log::add( $currentQuery->slug . ' was added as a Worker node' );
		}
	}

}
