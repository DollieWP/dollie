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
		return Api::post( Api::ROUTE_NODES_GET );
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

			// Don't do anything if the transient is empty.
			// Output buffer our Node details
            // will stay like this currently
			ob_start();?><node name="<?php echo $url; ?>" description="Deployed via <?php echo get_site_url(); ?>" tags="<?php echo DOLLIE_WORKER_KEY; ?>,<?php echo get_site_url(); ?>,<?php echo $email; ?>" hostname="<?php echo $ip; ?>:<?php echo $port; ?>" username="root"/></project><?php
			$new_node = ob_get_clean();

			// Grab our existing node details
			$all_nodes = $this->get_worker_nodes();

			$update_nodes = str_replace( '</project>', $new_node, $all_nodes );

			// Take output buffer for our body in our POST request
			$request_body = $update_nodes;

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
