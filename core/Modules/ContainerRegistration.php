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

		add_action( 'wp', [ $this, 'add_rundeck_key' ] );
		add_action( 'template_redirect', [ $this, 'add_rundeck_node' ], 10000 );
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

	public function add_rundeck_key() {
		if ( get_option( 'wpd_rundeck_key' ) === false ) {
			update_option( 'wpd_rundeck_key', dollie()->random_string( 12 ) );
		}
	}

	public function add_rundeck_node() {
		if ( did_action( 'template_redirect' ) === 1 && is_singular( 'container' ) ) {
			$currentQuery = dollie()->get_current_object();
			$this->register_rundeck_node( $currentQuery->id );
		}
	}

	public function get_rundeck_nodes() {
		$update = Api::postRequestRundeck( '23/project/Dollie-Containers/source/1/resources?format=xml' );

		return wp_remote_retrieve_body( $update );
	}

	public function register_rundeck_node( $id = null ) {
		if ( $id === null ) {
			$currentQuery = dollie()->get_current_object();
			$post_id      = $currentQuery->id;
		} else {
			$post_id = $id;
		}
		$url = dollie()->get_container_url( $post_id ) . '-' . DOLLIE_RUNDECK_KEY;

		$is_node_added = get_post_meta( $post_id, 'wpd_node_added', true );
		$ip            = get_post_meta( $post_id, 'wpd_container_ip', true );
		$port          = get_post_meta( $post_id, 'wpd_container_port', true );
		$email         = get_post_meta( $post_id, 'wpd_container_launched_by', true );

		// Only run if the node has not been added.
		if ( $is_node_added !== 'yes' ) {

			// Don't do anything if the transient is empty.
			// Output buffer our Node details
			ob_start();
			?>
            <node name="<?php echo $url; ?>" description="Deployed via <?php echo get_site_url(); ?>"
                  tags="<?php echo DOLLIE_RUNDECK_KEY; ?>,<?php echo get_site_url(); ?>,<?php echo $email; ?>"
                  hostname="<?php echo $ip; ?>:<?php echo $port; ?>" username="root"/></project>
			<?php
			// Create our new node details
			$new_node = ob_get_clean();

			// Grab our existing node details
			$all_nodes = $this->get_rundeck_nodes();

			$update_nodes = str_replace( '</project>', $new_node, $all_nodes );

			// Take output buffer for our body in our POST request
			$request_body = $update_nodes;

			// Set up the request
			$update = wp_remote_post(
				DOLLIE_RUNDECK_URL . '/api/23/project/Dollie-Containers/source/1/resources?format=xml', [
					'headers' => [
						'X-Rundeck-Auth-Token' => DOLLIE_RUNDECK_TOKEN,
						'Content-Type'         => 'text/xml',
					],
					'body'    => $request_body,
				]
			);

			if ( is_wp_error( $update ) ) {
				Log::add( 'Node could not be registered for ' . $currentQuery->slug, print_r( $update, true ), 'error' );
			} else {
				update_post_meta( $post_id, 'wpd_node_added', 'yes' );
				Log::add( $currentQuery->slug . ' was added as a Rundeck node' );
			}
		}
	}

	public function remove_rundeck_node( $id = null ) {
		$currentQuery = dollie()->get_current_object();
		$post_id      = $id === null ? $currentQuery->id : $id;
		$url          = dollie()->get_container_url( $post_id ) . '-' . DOLLIE_RUNDECK_KEY;

		// Don't do anything if the transient is empty.
		// Output buffer our Node details
		ob_start();
		?>
        <node name="<?php echo $url; ?>
			  <?php
		// Create our new node details
		$new_node = ob_get_clean();

		// Grab our existing node details
		$all_nodes = $this->get_rundeck_nodes();

		// Find the node we want to remove
		$parsed = $this->get_string_between( $all_nodes, $new_node, '/>' );

		// Create string of the node we want to remove.
		$container_node = $new_node . $parsed . '/>';

		$update_nodes = str_replace( $container_node, '', $all_nodes );

		// Take output buffer for our body in our POST request
		$request_body = $update_nodes;

		// Set up the request
		wp_remote_post(
			DOLLIE_RUNDECK_URL . '/api/3/project/dollie-platform/resources/', [
				'headers' => [
					'X-Rundeck-Auth-Token' => DOLLIE_RUNDECK_TOKEN,
					'Content-Type'         => 'text/xml',
				],
				'body'    => $request_body,
			]
		);

		Log::add( $currentQuery->slug . ' was removed as a Rundeck node', '', 'undeploy' );

		// Let's give Rundeck some time to complete
		add_post_meta( $post_id, 'wpd_node_removed', 'yes', true );
		delete_post_meta( $post_id, 'wpd_node_added' );
	}

}
