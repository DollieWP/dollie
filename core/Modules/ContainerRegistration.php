<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Helpers;
use Dollie\Core\Log;

/**
 * Class ContainerRegistration
 * @package Dollie\Core\Modules
 */
class ContainerRegistration extends Singleton {

	/**
	 * @var mixed
	 */
	private $helpers;

	/**
	 * ContainerRegistration constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->helpers = Helpers::instance();

		add_action( 'wp', [ $this, 'add_rundeck_key' ] );
		add_action( 'template_redirect', [ $this, 'add_rundeck_node' ], 10000 );
	}

	private function get_string_between( $string, $start, $end ) {
		$string = ' ' . $string;
		$ini    = strpos( $string, $start );
		if ( $ini == 0 ) {
			return '';
		}
		$ini += strlen( $start );
		$len = strpos( $string, $end, $ini ) - $ini;

		return substr( $string, $ini, $len );
	}

	public function add_rundeck_key() {
		if ( get_option( 'wpd_rundeck_key' ) === false ) {
			update_option( 'wpd_rundeck_key', wpd_random_string( 12 ) );
		}
	}

	public function add_rundeck_node() {
		global $wp_query;
		$post_id = $wp_query->get_queried_object_id();
		if ( did_action( 'template_redirect' ) === 1 && is_singular( 'container' ) ) {
			wpd_register_rundeck_node( $post_id );
		}
	}

	public function get_rundeck_nodes() {
		//Set up the request
		$update = wp_remote_get(
			DOLLIE_RUNDECK_URL . '/api/23/project/Dollie-Containers/source/1/resources?format=xml', array(
				'headers' => array(
					'X-Rundeck-Auth-Token' => DOLLIE_RUNDECK_TOKEN,
					'Content-Type'         => 'application/json',
				),
			)
		);

		//Parse the JSON request
		return wp_remote_retrieve_body( $update );
	}

	public function register_rundeck_node( $id = null ) {
		global $wp_query;

		if ( $id === null ) {
			$post_id = $wp_query->get_queried_object_id();
		} else {
			$post_id = $id;
		}
		$url = wpd_get_container_url( $post_id ) . '-' . DOLLIE_RUNDECK_KEY;

		$post_slug     = get_queried_object()->post_name;
		$is_node_added = get_post_meta( $post_id, 'wpd_node_added', true );
		$ip            = get_post_meta( $post_id, 'wpd_container_ip', true );
		$port          = get_post_meta( $post_id, 'wpd_container_port', true );
		$email         = get_post_meta( $post_id, 'wpd_container_launched_by', true );

		//Only run if the node has not been added.
		if ( $is_node_added != 'yes' ) {

			// Don't do anything if the transient is empty.
			//Output buffer our Node details
			ob_start();
			?>
            <node name="<?php echo $url; ?>" description="Deployed via <?php echo get_site_url(); ?>"
                  tags="<?php echo DOLLIE_RUNDECK_KEY; ?>,<?php echo get_site_url(); ?>,<?php echo $email; ?>"
                  hostname="<?php echo $ip; ?>:<?php echo $port; ?>" username="root"/></project>
			<?php
			//Create our new node details
			$new_node = ob_get_clean();

			//Grab our existing node details
			$all_nodes = wpd_get_rundeck_nodes();

			$update_nodes = str_replace( '</project>', $new_node, $all_nodes );

			//print(print_r($update_nodes,true));
			//die();

			//echo $update_nodes;

			//Take output buffer for our body in our POST request
			$request_body = $update_nodes;

			//Set up the request
			$update = wp_remote_post(
				DOLLIE_RUNDECK_URL . '/api/23/project/Dollie-Containers/source/1/resources?format=xml', array(
					'headers' => array(
						'X-Rundeck-Auth-Token' => DOLLIE_RUNDECK_TOKEN,
						'Content-Type'         => 'text/xml',
					),
					'body'    => $request_body,
				)
			);
			//Parse the JSON request
			$answer = wp_remote_retrieve_body( $update );

			if ( is_wp_error( $update ) ) {
				Log::add( 'Node could not be registered for ' . $post_slug, print_r( $update, true ), 'error' );
			} else {
				update_post_meta( $post_id, 'wpd_node_added', 'yes' );
				Log::add( $post_slug . ' was added as a Rundeck node' );
			}

		}
	}

	public function remove_rundeck_node( $id = null ) {
		global $wp_query;
		if ( $id === null ) {
			$post_id = $wp_query->get_queried_object_id();
		} else {
			$post_id = $id;
		}

		$url = wpd_get_container_url( $post_id ) . '-' . DOLLIE_RUNDECK_KEY;


		$post_slug = get_queried_object()->post_name;

		// Don't do anything if the transient is empty.
		//Output buffer our Node details
		ob_start();
		?>
        <node name="<?php echo $url; ?>
			  <?php
		//Create our new node details
		$new_node = ob_get_clean();

		//Grab our existing node details
		$all_nodes = wpd_get_rundeck_nodes();

		//Find the node we want to remove
		$parsed = get_string_between( $all_nodes, $new_node, '/>' );

		//Create string of the node we want to remove.
		$container_node = $new_node . $parsed . '/>';

		$update_nodes = str_replace( $container_node, '', $all_nodes );

		//Take output buffer for our body in our POST request
		$request_body = $update_nodes;

		//Set up the request
		$update = wp_remote_post(
			DOLLIE_RUNDECK_URL . '/api/3/project/dollie-platform/resources/', array(
				'headers' => array(
					'X-Rundeck-Auth-Token' => DOLLIE_RUNDECK_TOKEN,
					'Content-Type'         => 'text/xml',
				),
				'body'    => $request_body,
			)
		);
		
		//Parse the JSON request
		$answer = wp_remote_retrieve_body( $update );

		Log::add( $post_slug . ' was removed as a Rundeck node', '', 'undeploy' );
		//Let's give Rundeck some time to complete
		add_post_meta( $post_id, 'wpd_node_removed', 'yes', true );
		delete_post_meta( $post_id, 'wpd_node_added' );
	}

}
