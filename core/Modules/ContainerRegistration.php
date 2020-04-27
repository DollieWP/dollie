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

	public function add_worker_key() {
		if ( get_option( 'wpd_rundeck_key' ) === false ) {
			update_option( 'wpd_rundeck_key', dollie()->random_string( 12 ) );
		}
	}

	public function add_worker_node() {
		if ( did_action( 'template_redirect' ) === 1 && is_singular( 'container' ) ) {
			$currentQuery = dollie()->get_current_object();
			$this->register_worker_node( $currentQuery->id );
		}
	}

	public function get_worker_nodes() {
		$requestNodesGet = Api::post( Api::ROUTE_NODES_GET );

		$responseNodesGet = json_decode( wp_remote_retrieve_body( $requestNodesGet ), true );

		if ( $responseNodesGet['status'] === 500 ) {
			return '';
		}

		return $responseNodesGet['body'];
	}

	public function register_worker_node( $id = null ) {
		$currentQuery = dollie()->get_current_object();

		$post_id = $id === null ? $currentQuery->id : $id;
		$url     = dollie()->get_container_url( $post_id ) . '-' . DOLLIE_WORKER_KEY;

		$is_node_added = get_post_meta( $post_id, 'wpd_node_added', true );
		$ip            = get_post_meta( $post_id, 'wpd_container_ip', true );
		$port          = get_post_meta( $post_id, 'wpd_container_port', true );
		$email         = get_post_meta( $post_id, 'wpd_container_launched_by', true );

		// Only run if the node has not been added.
		if ( $is_node_added !== 'yes' ) {

			$new_node =  '  <node name="'.  $url .'" description="Deployed via '. get_site_url() .'"' .
                        ' tags="' . DOLLIE_WORKER_KEY . ',' . get_site_url() . ',' . $email . '"' .
                        ' hostname="' . $ip . ':' . $port . '"' .
                        ' username="root"/>' . "\n" .
                        '</project>';

			// Grab our existing node details
			$all_nodes = $this->get_worker_nodes();
			if ( ! $all_nodes ) {
				return;
			}

			$request_body = str_replace( '</project>', $new_node, $all_nodes );

			// Set up the request
			$update = wp_remote_post(
				DOLLIE_WORKER_URL . '/api/23/project/Dollie-Containers/source/1/resources?format=xml', [
					'headers' => [
						'X-Rundeck-Auth-Token' => DOLLIE_WORKER_TOKEN,
						'Content-Type'         => 'text/xml',
					],
					'body'    => $request_body,
				]
			);

			if ( is_wp_error( $update ) ) {
				Log::add( 'Node could not be registered for ' . $currentQuery->slug, print_r( $update, true ), 'error' );
			} else {
				update_post_meta( $post_id, 'wpd_node_added', 'yes' );
				Log::add( $currentQuery->slug . ' was added as a Worker node' );
			}
		}
	}

}
