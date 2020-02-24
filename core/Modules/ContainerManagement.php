<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;
use Dollie\Core\Log;

/**
 * Class ContainerManagement
 * @package Dollie\Core\Modules
 */
class ContainerManagement extends Singleton {

	/**
	 * ContainerManagement constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'init', [ $this, 'register_container' ], 0 );
		add_action( 'template_redirect', [ $this, 'bypass_output_caching' ] );
		add_action( 'template_redirect', [ $this, 'run_container_actions' ] );
		add_action( 'gform_after_submission_3', [ $this, 'show_rundeck_output' ], 10, 2 );
		add_action( 'template_redirect', [ $this, 'fetch_container_details' ] );
		add_action( 'template_redirect', [ $this, 'update_container_details' ] );
		add_action( 'untrashed_post', [ $this, 'run_container_untrash_action' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'load_scripts' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_script' ] );
	}

	public function register_container() {
		$labels = [
			'name'                  => _x( 'Containers', DOLLIE_SLUG, DOLLIE_SLUG ),
			'singular_name'         => _x( 'Container', DOLLIE_SLUG, DOLLIE_SLUG ),
			'menu_name'             => esc_html__( 'Containers', DOLLIE_SLUG ),
			'name_admin_bar'        => esc_html__( 'Post Type', DOLLIE_SLUG ),
			'archives'              => esc_html__( 'Container Archives', DOLLIE_SLUG ),
			'attributes'            => esc_html__( 'Container Attributes', DOLLIE_SLUG ),
			'parent_item_colon'     => esc_html__( 'Parent Container:', DOLLIE_SLUG ),
			'all_items'             => esc_html__( 'View Containers', DOLLIE_SLUG ),
			'add_new_item'          => esc_html__( 'Add New Container', DOLLIE_SLUG ),
			'add_new'               => esc_html__( 'Add New', DOLLIE_SLUG ),
			'new_item'              => esc_html__( 'New Container', DOLLIE_SLUG ),
			'edit_item'             => esc_html__( 'Edit Container', DOLLIE_SLUG ),
			'update_item'           => esc_html__( 'Update Container', DOLLIE_SLUG ),
			'view_item'             => esc_html__( 'View Container', DOLLIE_SLUG ),
			'view_items'            => esc_html__( 'View Containers', DOLLIE_SLUG ),
			'search_items'          => esc_html__( 'Search Containers', DOLLIE_SLUG ),
			'not_found'             => esc_html__( 'Not found', DOLLIE_SLUG ),
			'not_found_in_trash'    => esc_html__( 'Not found in Trash', DOLLIE_SLUG ),
			'featured_image'        => esc_html__( 'Featured Image', DOLLIE_SLUG ),
			'set_featured_image'    => esc_html__( 'Set featured image', DOLLIE_SLUG ),
			'remove_featured_image' => esc_html__( 'Remove featured image', DOLLIE_SLUG ),
			'use_featured_image'    => esc_html__( 'Use as featured image', DOLLIE_SLUG ),
			'insert_into_item'      => esc_html__( 'Insert into item', DOLLIE_SLUG ),
			'uploaded_to_this_item' => esc_html__( 'Uploaded to this item', DOLLIE_SLUG ),
			'items_list'            => esc_html__( 'Containers list', DOLLIE_SLUG ),
			'items_list_navigation' => esc_html__( 'Containers list navigation', DOLLIE_SLUG ),
			'filter_items_list'     => esc_html__( 'Filter items list', DOLLIE_SLUG ),
		];

		$args = [
			'label'              => esc_html__( 'Container', DOLLIE_SLUG ),
			'description'        => esc_html__( 'Platform Containers', DOLLIE_SLUG ),
			'labels'             => $labels,
			'supports'           => [ 'title', 'content', 'author', 'custom-fields', 'thumbnail' ],
			'taxonomies'         => [ 'container_category', 'container_tag' ],
			'hierarchical'       => false,
			'public'             => true,
			'show_ui'            => true,
			'show_in_menu'       => 'wpd_platform_setup',
			'menu_position'      => - 10,
			'show_in_admin_bar'  => true,
			'show_in_nav_menus'  => true,
			'can_export'         => true,
			'has_archive'        => false,
			'publicly_queryable' => true,
			'menu_icon'          => 'dashicons-image-filter',
			'query_var'          => 'site',
			'rewrite'            => [
				'slug'       => 'site',
				'with_front' => true,
				'pages'      => true,
				'feeds'      => false,
			],
			'show_in_rest'       => false,
		];

		register_post_type( 'container', $args );
	}

	public function bypass_output_caching() {
		if ( is_singular( 'container' ) ) {
			header( 'Content-Encoding: none' );

			// Explicitly disable caching so NGINX and other upstreams won't cache.
			header( 'Cache-Control: no-cache, must-revalidate' );
			header( 'X-Accel-Buffering: no' );
		}
	}

	public function get_customer_container_details() {
		$currentQuery = dollie()->helpers()->get_current_object();
		$container_id = get_post_meta( $currentQuery->id, 'wpd_container_id', true );

		$request = get_transient( 'dollie_s5_container_details_' . $currentQuery->slug );

		// Only make request if it's not cached in a transient.
		if ( empty( $request ) ) {

			// Set up the request
			$response = Api::getRequestDollie( $container_id, 30 );

			if ( is_wp_error( $response ) ) {
				Log::add( 'Container details could not be fetched. for' . $currentQuery->slug, print_r( $response, true ), 'error' );

				return [];
			}

			$request = json_decode( wp_remote_retrieve_body( $response ) );
			// Parse the JSON request
			$update_answer   = wp_remote_retrieve_body( $response );
			$update_response = json_decode( $update_answer, true );

			if ( empty( $request ) ) {
				Log::add( 'Container details could not be fetched. for' . $currentQuery->slug, print_r( $response, true ), 'error' );

				return [];
			}
			// Set Transient and Update Post Meta
			set_transient( 'dollie_s5_container_details_' . $currentQuery->slug, $request, MINUTE_IN_SECONDS * 150000 );
			update_post_meta( $currentQuery->id, 'wpd_container_id', $update_response['id'], true );
			update_post_meta( $currentQuery->id, 'wpd_container_ssh', $update_response['containerSshPort'] );
			update_post_meta( $currentQuery->id, 'wpd_container_user', $update_response['containerSshUsername'] );
			update_post_meta( $currentQuery->id, 'wpd_container_port', $update_response['containerSshPort'] );
			update_post_meta( $currentQuery->id, 'wpd_container_password', $update_response['containerSshPassword'] );
			update_post_meta( $currentQuery->id, 'wpd_container_ip', preg_replace( '/\s+/', '', $update_response['containerHostIpAddress'] ) );
			update_post_meta( $currentQuery->id, 'wpd_container_deploy_time', $update_response['deployedAt'] );
			update_post_meta( $currentQuery->id, 'wpd_container_uri', $update_response['uri'] );
		}

		return $request;
	}

	public function container_api_request( $url, $transient_id, $user_auth, $user_pass ) {
		if ( ob_get_length() > 0 ) {
			@ob_end_flush();
			@flush();
		}

		$currentQuery = dollie()->helpers()->get_current_object();
		$request      = get_transient( 'dollie_container_api_request_' . $currentQuery->slug . '_' . $transient_id );

		if ( $user_auth === null ) {
			$user_auth = DOLLIE_S5_USER;
		}

		if ( $user_pass === null ) {
			$user_pass = DOLLIE_S5_PASSWORD;
		}

		// Only make request if it's not cached in a transient.
		if ( empty( $request ) ) {
			$response = wp_remote_request( $url, [
				'method'  => 'GET',
				'headers' => [
					'Authorization' => 'Basic ' . base64_encode( $user_auth . ':' . $user_pass ),
				]
			] );

			if ( is_wp_error( $response ) ) {
				return [];
			}

			$request = json_decode( wp_remote_retrieve_body( $response ) );

			if ( empty( $request ) ) {
				return [];
			}

			set_transient( 'dollie_container_api_request_' . $currentQuery->slug . '_' . $transient_id, $request, MINUTE_IN_SECONDS * 30 );
		}

		return $request;
	}

	public function start_rundeck_job( $job_id ) {
		$currentQuery = dollie()->helpers()->get_current_object();

		$post_body = [
			'filter' => 'name: https://' . $currentQuery->slug . DOLLIE_DOMAIN . '-' . DOLLIE_RUNDECK_KEY
		];

		Api::postRequestRundeck( '1/job/' . $job_id . '/run/', $post_body );
	}

	public function run_container_actions() {
		// Only fire on our site management page
		if ( ! isset( $_GET['update-site-action'] ) ) {
			return;
		}

		// Trigger the right Rundeck Action
		if ( isset( $_POST['install-plugin'] ) ) {
			$this->start_rundeck_job( '1fcf1bdb-95a4-4ecb-bc46-78382f5a930b' );
		}

		// Trigger the right Rundeck Action
		if ( isset( $_POST['switch-to-php-5'] ) ) {
			$this->start_rundeck_job( 'a98ea708-f42e-418c-bc35-1066fb533e8e' );
		}

		// Trigger the right Rundeck Action
		if ( isset( $_POST['create-new-backup'] ) ) {
			$this->start_rundeck_job( '5b51b1a4-bcc7-4c2c-a799-b024e561c87f' );
		}

		// Trigger the right Rundeck Action
		if ( isset( $_POST['switch-to-php-7'] ) ) {
			$this->start_rundeck_job( 'cf9d0568-e150-4f59-b014-b7c7d9ca5e46' );
		}

		// Caching Method = Super Cache
		if ( isset( $_POST['caching-wpsc'] ) ) {
			$this->start_rundeck_job( '00beea1e-fe15-436b-872e-43ead65c8c51' );
		}
	}

	public function show_rundeck_output( $entry, $form ) {
		$currentQuery = dollie()->helpers()->get_current_object();

		$post_body = [
			'filter' => 'name: https://' . $currentQuery->slug . DOLLIE_DOMAIN . '-' . DOLLIE_RUNDECK_KEY
		];

		$update = Api::postRequestRundeck( '1/job/6f757271-a39e-4eb2-8f89-f6668033a262/run/', $post_body );

		// Parse the JSON request
		$answer = wp_remote_retrieve_body( $update );
		$xml    = simplexml_load_string( $answer, 'SimpleXMLElement', LIBXML_NOCDATA );
		$json   = json_encode( $xml );
		$array  = json_decode( $json, true );

		$execution_id = $array['executions']['execution']['@attributes']['id'];

		sleep( 6 );

		// Set up the request
		$update = Api::postRequestRundeck( '5/execution/' . $execution_id . '/output?format=text' );

		// Parse the JSON request
		$answer = wp_remote_retrieve_body( $update );

		?>
        <div class="box-light padding-full ">
			<?php echo $answer; ?>
        </div>
		<?php
	}

	public function fetch_container_details() {
		if ( isset( $_GET['get-details'] ) ) {
			$currentQuery = dollie()->helpers()->get_current_object();

			delete_transient( 'dollie_s5_container_details_' . $currentQuery->slug );
			delete_transient( 'dollie_site_users_' . $currentQuery->slug );
			delete_transient( 'dollie_site_news_' . $currentQuery->slug );
		}
	}

	public function update_container_details() {
		if ( isset( $_GET['update-details'] ) ) {
			dollie()->helpers()->flush_container_details();
		}
	}

	public function container_action( $action, $container_post_id ) {
		$currentQuery = dollie()->helpers()->get_current_object();

		$post_id = $container_post_id === null ? $currentQuery->id : $container_post_id;
		$site    = get_post_field( 'post_name', $post_id );
		$status  = get_post_meta( $post_id, 'wpd_container_status', true );

		// No need to continue if the status is unchanged.
		if ( $action === $status ) {
			return;
		}

		$container_id = get_post_meta( $post_id, 'wpd_container_id', true );

		$update = Api::postRequestDollie( $container_id . '/' . $action, [], 45 );

		if ( is_wp_error( $update ) ) {
			Log::add( 'container action could not be completed for ' . $currentQuery->slug, print_r( $update, true ), 'error' );
		} else {
			if ( $action === 'start' ) {
				delete_post_meta( $post_id, 'wpd_stop_container_at' );
				delete_post_meta( $post_id, 'wpd_scheduled_for_removal' );
				delete_post_meta( $post_id, 'wpd_undeploy_container_at' );
				delete_post_meta( $post_id, 'wpd_scheduled_for_undeployment' );
			}
			if ( $action === 'stop' ) {
				// Get today's timestamp.
				$today        = mktime( 0, 0, 0, date( 'm' ), date( 'd' ), date( 'Y' ) );
				$trigger_date = get_post_meta( $post_id, 'wpd_stop_container_at', true );

				//If our "stop" time has passed our current time, it's time to flip the switch and stop the container.
				if ( $trigger_date < $today ) {
					$delay_in_days = 14;
					// Calculate the "stop" date and set it 3 days into the future.
					$trigger_date = mktime( 0, 0, 0, date( 'm' ), date( 'd' ) + $delay_in_days, date( 'Y' ) );
					update_post_meta( $post_id, 'wpd_container_status', 'stopped' );
					update_post_meta( $post_id, 'wpd_scheduled_for_undeployment', 'yes' );
					update_post_meta( $post_id, 'wpd_undeploy_container_at', $trigger_date );
				}

				Log::add( $site . ' scheduled to be removed', '', 'undeploy' );
			}
			if ( $action === 'undeploy' ) {
				delete_post_meta( $post_id, 'wpd_stop_container_at' );
				delete_post_meta( $post_id, 'wpd_scheduled_for_removal' );
				delete_post_meta( $post_id, 'wpd_undeploy_container_at' );
				delete_post_meta( $post_id, 'wpd_scheduled_for_undeployment' );
				ContainerRegistration::instance()->remove_rundeck_node( $post_id );
				wp_trash_post( $post_id );

				Log::add( $site . ' was undeployed', '', 'undeploy' );
			}

			update_post_meta( $post_id, 'wpd_container_status', $action );
			Log::add( $site . ' status changed to: ' . $action );
		}
	}

	public function run_container_untrash_action( $post_id ) {
		$post_type = get_post( $post_id )->post_type;
		if ( $post_type === 'container' ) {
			$this->container_action( 'start', $post_id );
		}
	}

	public function load_scripts( $hook ) {
		wp_register_style( 'dollie-custom-css', DOLLIE_PLUGIN_URL . 'assets/css/admin.css' );
		wp_enqueue_style( 'dollie-custom-css' );
		wp_enqueue_script( 'dollie-custom-js', DOLLIE_PLUGIN_URL . 'assets/js/admin.js' );
	}

	public function enqueue_admin_script( $hook ) {
		wp_enqueue_script( 'my_custom_script', DOLLIE_PLUGIN_URL . 'assets/js/admin.js', [], '1.0' );
	}

	public function sync_containers() {
		$site_url = get_site_url();

		// Get list of container from remote API
		$server_containers = Api::getRequestDollie( '', 30 );

		// Convert JSON into array.
		$server_containers = json_decode( wp_remote_retrieve_body( $server_containers ), true );

		foreach ( $server_containers as $key => $server_container ) {
			// Filter out only the containers having given site url in description.
			if ( strpos( $server_container['description'], $site_url ) ) {

				// Get container from client's WP install with the server's container ID
				$client_containers = get_posts( [
					'post_type'  => 'container',
					'meta_query' => [
						[
							'key'     => 'wpd_container_id',
							'value'   => $server_container['id'],
							'compare' => '=',
						],
					]
				] );

				// Get email from the description field and then find author ID based on email.
				$description     = explode( '|', $server_container['description'], 2 );
				$email           = trim( $description[0] );
				$author          = get_user_by( 'email', $email );
				$full_url        = parse_url( $server_container['uri'] );
				$stripped_domain = explode( '.', $full_url['host'] );
				$domain          = $stripped_domain[0];

				// If any such container found, update the container author ID based on the email in the "description" field from server's container.
				if ( $client_containers ) {
					foreach ( $client_containers as $client_container ) {

						// Update author field of all containers.
						wp_update_post( [
							'ID'          => $client_container->ID,
							'post_author' => $author->ID,
							'post_name'   => $domain,
							'post_title'  => $domain,
						] );
					}
				} else {
					// If no such container found, create one with deatils from server's container.
					// Add new container post to client's WP
					wp_insert_post( [
						'post_type'   => 'container',
						'post_status' => 'publish',
						'post_name'   => $domain,
						'post_title'  => $domain,
						'post_author' => $author->ID,
						'meta_input'  => [
							'wpd_container_id'          => $server_container['id'],
							'wpd_container_user'        => $server_container['containerSshUsername'],
							'wpd_container_port'        => $server_container['containerSshPort'],
							'wpd_container_password'    => $server_container['containerSshPassword'],
							'wpd_container_ip'          => $server_container['containerHostIpAddress'],
							'wpd_container_status'      => $server_container['status'],
							'wpd_container_launched_by' => $email,
							'wpd_container_deploy_time' => $server_container['deployedAt'],
							'wpd_container_uri'         => $server_container['uri'],
							'wpd_node_added'            => 'yes',
							'wpd_setup_complete'        => 'yes',
							'wpd_refetch_secret_key'    => 'yes',
						],
					] );
				}
			} else {
				unset( $server_containers[ $key ] );
			}
		}

		return $server_containers;
	}

}
