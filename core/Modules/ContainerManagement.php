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
		add_action( 'template_redirect', [ $this, 'fetch_container_details' ] );
		add_action( 'template_redirect', [ $this, 'update_container_details' ] );
		add_action( 'untrashed_post', [ $this, 'run_container_untrash_action' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'load_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'load_styles' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_script' ] );

		add_action( 'edit_form_after_title', [ $this, 'add_container_manager_notice' ] );
		add_action( 'add_meta_boxes', [ $this, 'rename_author_box_title' ] );
	}

	public function register_container() {
		$labels = [
			'name'                  => _x( 'Sites', 'dollie', 'dollie' ),
			'singular_name'         => _x( 'Site', 'dollie', 'dollie' ),
			'menu_name'             => esc_html__( 'Sites', 'dollie' ),
			'name_admin_bar'        => esc_html__( 'Post Type', 'dollie' ),
			'archives'              => esc_html__( 'Site Archives', 'dollie' ),
			'attributes'            => esc_html__( 'Site Attributes', 'dollie' ),
			'parent_item_colon'     => esc_html__( 'Parent Site:', 'dollie' ),
			'all_items'             => esc_html__( 'View Sites', 'dollie' ),
			'add_new_item'          => esc_html__( 'Add New Site', 'dollie' ),
			'add_new'               => esc_html__( 'Add New', 'dollie' ),
			'new_item'              => esc_html__( 'New Site', 'dollie' ),
			'edit_item'             => esc_html__( 'Edit Site', 'dollie' ),
			'update_item'           => esc_html__( 'Update Site', 'dollie' ),
			'view_item'             => esc_html__( 'View Site', 'dollie' ),
			'view_items'            => esc_html__( 'View Sites', 'dollie' ),
			'search_items'          => esc_html__( 'Search Sites', 'dollie' ),
			'not_found'             => esc_html__( 'Not found', 'dollie' ),
			'not_found_in_trash'    => esc_html__( 'Not found in Trash', 'dollie' ),
			'featured_image'        => esc_html__( 'Featured Image', 'dollie' ),
			'set_featured_image'    => esc_html__( 'Set featured image', 'dollie' ),
			'remove_featured_image' => esc_html__( 'Remove featured image', 'dollie' ),
			'use_featured_image'    => esc_html__( 'Use as featured image', 'dollie' ),
			'insert_into_item'      => esc_html__( 'Insert into item', 'dollie' ),
			'uploaded_to_this_item' => esc_html__( 'Uploaded to this item', 'dollie' ),
			'items_list'            => esc_html__( 'Sites list', 'dollie' ),
			'items_list_navigation' => esc_html__( 'Sites list navigation', 'dollie' ),
			'filter_items_list'     => esc_html__( 'Filter items list', 'dollie' ),
		];

		$args = [
			'label'               => esc_html__( 'Site', 'dollie' ),
			'description'         => esc_html__( 'Platform Sites', 'dollie' ),
			'labels'              => $labels,
			'supports'            => [ 'title', 'content', 'author', 'custom-fields', 'thumbnail' ],
			'taxonomies'          => [ 'container_category', 'container_tag' ],
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => 'wpd_platform_setup',
			'menu_position'       => - 10,
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => true,
			'menu_icon'           => 'dashicons-image-filter',
			'query_var'           => 'site',
			'rewrite'             => [
				'slug'       => 'site',
				'with_front' => true,
				'pages'      => true,
				'feeds'      => false,
			],
			'show_in_rest'        => false,
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

	public function get_customer_container_details( $container_id = null ) {

		$container = dollie()->get_current_object( $container_id );

		$request = get_transient( 'dollie_s5_container_details_' . $container->slug );

		// Only make request if it's not cached in a transient.
		if ( empty( $request ) ) {

			$container_id = get_post_meta( $container->id, 'wpd_container_id', true );

			// Set up the request
			$requestGetContainer = Api::post( API::ROUTE_CONTAINER_GET, [
				'container_id'  => $container_id,
				'dollie_domain' => DOLLIE_INSTALL,
				'dollie_token'  => Api::get_dollie_token()
			] );

			$responseGetContainer = json_decode( wp_remote_retrieve_body( $requestGetContainer ), true );

			if ( $responseGetContainer['status'] === 500 ) {
				Log::add( 'Container details could not be fetched. for' . $container->slug, print_r( $responseGetContainer['body'], true ), 'error' );

				return [];
			}

			$request = json_decode( $responseGetContainer['body'], true );

			if ( empty( $request ) ) {
				Log::add( 'Container details could not be fetched. for' . $container->slug, print_r( $request, true ), 'error' );

				return [];
			}

			// Set Transient.
			set_transient( 'dollie_s5_container_details_' . $container->slug, $request, MINUTE_IN_SECONDS * 150000 );

			// Update Post Meta.
			update_post_meta( $container->id, 'wpd_container_id', $request['id'], true );
			update_post_meta( $container->id, 'wpd_container_ssh', $request['containerSshPort'] );
			update_post_meta( $container->id, 'wpd_container_user', $request['containerSshUsername'] );
			update_post_meta( $container->id, 'wpd_container_port', $request['containerSshPort'] );
			update_post_meta( $container->id, 'wpd_container_password', $request['containerSshPassword'] );
			update_post_meta( $container->id, 'wpd_container_ip', preg_replace( '/\s+/', '', $request['containerHostIpAddress'] ) );
			update_post_meta( $container->id, 'wpd_container_deploy_time', $request['deployedAt'] );
			update_post_meta( $container->id, 'wpd_container_uri', $request['uri'] );
		}

		return (object) $request;
	}

	public function container_api_request( $url, $transient_id, $user_auth, $user_pass = null ) {
		if ( ob_get_length() > 0 ) {
			@ob_end_flush();
			@flush();
		}

		$transient_name = 'dollie_container_api_request_' . $transient_id;

		$request = get_transient( $transient_name );

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

			$request = json_decode( wp_remote_retrieve_body( $response ), false );

			if ( empty( $request ) ) {
				return [];
			}

			set_transient( $transient_name, $request, MINUTE_IN_SECONDS * 30 );
		}

		return $request;
	}


	public function get_container_wp_info( $container_id = null ) {

		$container = dollie()->get_current_object( $container_id );

		$transient_id   = $container->slug . '_get_container_wp_info';
		$transient_name = 'dollie_container_api_request_' . $transient_id;

		$data = get_transient( $transient_name );

		if ( empty( $data ) ) {
			// Make an API request to get our customer details.
			$request = dollie()->get_customer_container_details( $container->id );

			// Now that we have our container details get our info
			$details_url      = dollie()->get_container_url( $container->id ) . '/wp-content/mu-plugins/platform/container/details';
			$details_username = 'container';

			//Pass on the App ID from our request
			$details_pass = $request->id;

			//Make the request
			$data = dollie()->container_api_request( $details_url, $transient_id, $details_username, $details_pass );
		}

		return $data;
	}

	public function fetch_container_details() {
		if ( isset( $_GET['get-details'] ) ) {
			$currentQuery = dollie()->get_current_object();

			delete_transient( 'dollie_s5_container_details_' . $currentQuery->slug );
			delete_transient( 'dollie_site_users_' . $currentQuery->slug );
			delete_transient( 'dollie_site_news_' . $currentQuery->slug );
		}
	}

	public function update_container_details() {
		if ( isset( $_GET['update-details'] ) ) {
			dollie()->flush_container_details();
		}
	}

	public function container_action( $action, $container_post_id ) {
		$currentQuery = dollie()->get_current_object();

		$post_id = $container_post_id === null ? $currentQuery->id : $container_post_id;
		$site    = get_post_field( 'post_name', $post_id );
		$status  = get_post_meta( $post_id, 'wpd_container_status', true );

		// No need to continue if the status is unchanged.
		if ( $action === $status ) {
			return;
		}

		$container_id = get_post_meta( $post_id, 'wpd_container_id', true );

		$requestTriggerContainer = Api::post( Api::ROUTE_CONTAINER_TRIGGER, [
			'container_id'  => $container_id,
			'action'        => $action,
			'dollie_domain' => DOLLIE_INSTALL,
			'dollie_token'  => Api::get_dollie_token(),
		] );

		$requestTriggerResponse = json_decode( wp_remote_retrieve_body( $requestTriggerContainer ), true );

		if ( $requestTriggerResponse['status'] === 500 ) {
			Log::add( 'container action could not be completed for ' . $currentQuery->slug, print_r( $requestTriggerResponse, true ), 'error' );
		} else {
			if ( $action === 'start' ) {
				delete_post_meta( $post_id, 'wpd_stop_container_at' );
				delete_post_meta( $post_id, 'wpd_scheduled_for_removal' );
				delete_post_meta( $post_id, 'wpd_undeploy_container_at' );
				delete_post_meta( $post_id, 'wpd_scheduled_for_undeployment' );
				// Update the site status so it counts as an active site
				wp_update_post([
					'ID'          => $post_id,
					'post_status' => 'publish',
				]);
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
					// Update the site status so it won't count as an active site.
					wp_update_post([
						'ID'          => $post_id,
						'post_status' => 'draft',
					]);
				}

				Log::add( $site . ' scheduled to be removed', '', 'undeploy' );
			}
			if ( $action === 'undeploy' ) {
				delete_post_meta( $post_id, 'wpd_stop_container_at' );
				delete_post_meta( $post_id, 'wpd_scheduled_for_removal' );
				delete_post_meta( $post_id, 'wpd_undeploy_container_at' );
				delete_post_meta( $post_id, 'wpd_scheduled_for_undeployment' );
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
		wp_register_style( 'dollie-custom-css', DOLLIE_URL . 'assets/css/admin.css', [], DOLLIE_VERSION );
		wp_enqueue_style( 'dollie-custom-css' );
		wp_enqueue_script( 'dollie-custom-js', DOLLIE_URL . 'assets/js/admin.js' );
	}

	public function load_styles( $hook ) {
		wp_register_style( 'dollie-front-css', DOLLIE_URL . 'assets/css/front.css', [], DOLLIE_VERSION );
		wp_enqueue_style( 'dollie-front-css' );
	}


	public function enqueue_admin_script( $hook ) {
		wp_enqueue_script( 'my_custom_script', DOLLIE_URL . 'assets/js/admin.js', [], '1.0' );
	}

	public function sync_containers() {
		// Get list of container from remote API
		$requestGetContainers = Api::post( Api::ROUTE_CONTAINER_GET, [
			'dollie_domain' => DOLLIE_INSTALL,
			'dollie_token'  => Api::get_dollie_token(),
		] );

		// Convert JSON into array.
		$responseGetContainers = json_decode( wp_remote_retrieve_body( $requestGetContainers ), true );

		if ( $responseGetContainers['status'] === 500 ) {
			return [];
		}

		$containers = json_decode( $responseGetContainers['body'], true );

		foreach ( $containers as $key => $container ) {
			// Get container from client's WP install with the server's container ID
			$client_containers = get_posts( [
				'post_type'  => 'container',
				'meta_query' => [
					[
						'key'     => 'wpd_container_id',
						'value'   => $container['id'],
						'compare' => '=',
					],
				]
			] );

			// Get email from the description field and then find author ID based on email.
			$description = explode( '|', $container['description'], 2 );
			$email       = trim( $description[0] );
			$author      = get_user_by( 'email', $email );

			if ( ! $author ) {
				$author = wp_get_current_user();
			}

			$full_url        = parse_url( $container['uri'] );
			$stripped_domain = explode( '.', $full_url['host'] );
			$domain          = $stripped_domain[0];

			$container_post_id = false;

			// If any such container found, update the container author ID based on the email in the "description" field from server's container.
			if ( $client_containers ) {
				foreach ( $client_containers as $client_container ) {
					$container_post_id = $client_container->ID;

					// Update author field of all containers.
					wp_update_post( [
						'ID'          => $client_container->ID,
						'post_author' => $author->ID,
						'post_name'   => $domain,
						'post_title'  => $domain,
					] );
				}
			} else {
				// If no such container found, create one with details from server's container.
				// Add new container post to client's WP
				$container_post_id = wp_insert_post( [
					'post_type'   => 'container',
					'post_status' => 'publish',
					'post_name'   => $domain,
					'post_title'  => $domain,
					'post_author' => $author->ID,
					'meta_input'  => [
						'wpd_container_id'          => $container['id'],
						'wpd_container_user'        => $container['containerSshUsername'],
						'wpd_container_port'        => $container['containerSshPort'],
						'wpd_container_password'    => $container['containerSshPassword'],
						'wpd_container_ip'          => $container['containerHostIpAddress'],
						'wpd_container_status'      => $container['status'],
						'wpd_container_launched_by' => $email,
						'wpd_container_deploy_time' => $container['deployedAt'],
						'wpd_container_uri'         => $container['uri'],
						'wpd_node_added'            => 'yes',
						'wpd_setup_complete'        => 'yes',
						'wpd_refetch_secret_key'    => 'yes',
					],
				] );
			}

			// If the container is not deployed -> trash it.
			if ( $container['status'] !== 'Running' && $container_post_id ) {
				wp_trash_post( $container_post_id );
			}
		}

		flush_rewrite_rules();

		return $containers;
	}

	public function add_container_manager_notice() {
		if ( 'container' !== get_post_type() ) {
			return;
		}

		$container_id  = get_post_meta( $_GET['post'], 'wpd_container_id', true );
		$dashboard_url = get_option( 'options_wpd_api_dashboard_url' );
		?>
        <br>
        <div style="margin-left: 0; z-index: 0" class="dollie-notice">
            <div class="dollie-inner-message">
                <img width="60" src="<?php echo esc_url( DOLLIE_URL . 'assets/img/active.png' ); ?>">
                <div class="dollie-message-center">
                    <h3><?php esc_html_e( 'Need Advanced Options?', 'dollie' ); ?> </h3>
                    <p>
						<?php
						printf(
							'<a href="%s">Visit the Dollie Container Manager</a> to quickly start/stop/restart this site and access other advanced management tools',
							esc_url( $dashboard_url . '/adminUISites/show/' . $container_id )
						);
						?>
                </div>
            </div>
        </div>
		<?php
	}

	public function rename_author_box_title() {
		remove_meta_box( 'authordiv', 'container', 'core' );
		add_meta_box( 'authordiv', __( 'Assigned Customer to this Site', 'wpse39446_domain' ), 'post_author_meta_box', 'container', 'advanced', 'high' );
	}

}
