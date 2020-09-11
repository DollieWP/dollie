<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;
use Dollie\Core\Log;
use WP_Query;

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
		add_action( 'init', [ $this, 'register_container_category' ], 0 );
		add_action( 'template_redirect', [ $this, 'bypass_output_caching' ] );
		add_action( 'template_redirect', [ $this, 'remove_container_details_transients' ] );
		add_action( 'template_redirect', [ $this, 'update_container_details' ] );
		add_action( 'untrashed_post', [ $this, 'run_container_untrash_action' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'load_admin_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'load_styles' ] );

		add_action( 'edit_form_after_title', [ $this, 'add_container_manager_notice' ] );
		add_action( 'add_meta_boxes', [ $this, 'rename_author_box_title' ] );
		add_action( 'acf/save_post', [ $this, 'update_customer_role' ] );
		add_action( 'acf/save_post', [ $this, 'update_all_customers_role' ] );

		add_action( 'acf/input/admin_footer', [ $this, 'change_role_option_notice' ] );
	}

	/**
	 * Register container post type
	 */
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
			'supports'            => [ 'title', 'content', 'author', 'custom-fields', 'thumbnail', 'page-attributes' ],
			'taxonomies'          => [ 'container_category', 'container_tag' ],
			'hierarchical'        => true,
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

	/**
	 * Register container taxonomy
	 */
	public function register_container_category() {
		$labels = array(
			'name'                       => _x( 'Blueprint Categories', 'Taxonomy General Name', 'dollie' ),
			'singular_name'              => _x( 'Blueprint Category', 'Taxonomy Singular Name', 'dollie' ),
			'menu_name'                  => __( 'Taxonomy', 'dollie' ),
			'all_items'                  => __( 'All Items', 'dollie' ),
			'parent_item'                => __( 'Parent Item', 'dollie' ),
			'parent_item_colon'          => __( 'Parent Item:', 'dollie' ),
			'new_item_name'              => __( 'New Item Name', 'dollie' ),
			'add_new_item'               => __( 'Add New Item', 'dollie' ),
			'edit_item'                  => __( 'Edit Item', 'dollie' ),
			'update_item'                => __( 'Update Item', 'dollie' ),
			'view_item'                  => __( 'View Item', 'dollie' ),
			'separate_items_with_commas' => __( 'Separate items with commas', 'dollie' ),
			'add_or_remove_items'        => __( 'Add or remove items', 'dollie' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'dollie' ),
			'popular_items'              => __( 'Popular Items', 'dollie' ),
			'search_items'               => __( 'Search Items', 'dollie' ),
			'not_found'                  => __( 'Not Found', 'dollie' ),
			'no_terms'                   => __( 'No items', 'dollie' ),
			'items_list'                 => __( 'Items list', 'dollie' ),
			'items_list_navigation'      => __( 'Items list navigation', 'dollie' ),
		);
		$args   = array(
			'labels'            => $labels,
			'hierarchical'      => false,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => true,
		);

		register_taxonomy( 'container_category', array( 'container' ), $args );
	}

	/**
	 * Skip caching
	 */
	public function bypass_output_caching() {
		if ( is_singular( 'container' ) ) {
			header( 'Content-Encoding: none' );

			// Explicitly disable caching so NGINX and other upstreams won't cache.
			header( 'Cache-Control: no-cache, must-revalidate' );
			header( 'X-Accel-Buffering: no' );
		}
	}

	/**
	 * Get container details
	 *
	 * @param null $container_id
	 *
	 * @return array|object
	 */
	public function get_customer_container_details( $container_id = null ) {
		$container = dollie()->get_current_object( $container_id );
		$request   = get_transient( 'dollie_s5_container_details_' . $container->slug );

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

	/**
	 * Do container request
	 *
	 * @param $url
	 * @param $transient_id
	 * @param $user_auth
	 * @param null $user_pass
	 *
	 * @return array|mixed
	 */
	public function container_api_request( $url, $transient_id = null, $user_auth = null, $user_pass = null ) {
		if ( ob_get_length() > 0 ) {
			@ob_end_flush();
			@flush();
		}

		if ( $user_auth === null ) {
			$user_auth = DOLLIE_S5_USER;
		}

		if ( $user_pass === null ) {
			$user_pass = DOLLIE_S5_PASSWORD;
		}

		$request = [];

		if ( isset( $transient_id ) ) {
			$transient_name = 'dollie_container_api_request_' . $transient_id;
			$request        = get_transient( $transient_name );
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

			if ( isset( $transient_name ) ) {
				set_transient( $transient_name, $request, MINUTE_IN_SECONDS * 30 );
			}
		}

		return $request;
	}

	/**
	 * Get container wp info
	 *
	 * @param null $container_id
	 *
	 * @return mixed
	 */
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

	/**
	 * Get container login info
	 *
	 * @param null $container_id
	 * @param string $site_username
	 *
	 * @return mixed
	 */
	public function get_container_login_token( $container_id = null, $site_username = '' ) {
		$container = dollie()->get_current_object( $container_id );

		// Make an API request to get our customer details.
		$request = dollie()->get_customer_container_details( $container->id );

		// Now that we have our container details get our info
		$details_url = dollie()->get_container_url( $container->id ) . '/wp-content/mu-plugins/platform/container/details/login.php';
		$details_url .= '?username=' . $site_username;

		// Pass on the App ID from our request.
		$details_username = 'container';
		$details_pass     = $request->id;

		//Make the request
		return dollie()->container_api_request( $details_url, null, $details_username, $details_pass );
	}

	/**
	 * Remove container details transients
	 */
	public function remove_container_details_transients() {
		if ( isset( $_GET['get-details'] ) ) {
			$currentQuery = dollie()->get_current_object();

			delete_transient( 'dollie_s5_container_details_' . $currentQuery->slug );
			delete_transient( 'dollie_site_users_' . $currentQuery->slug );
			delete_transient( 'dollie_site_news_' . $currentQuery->slug );
		}
	}

	/**
	 * Flush container details
	 */
	public function update_container_details() {
		if ( isset( $_GET['update-details'] ) ) {
			dollie()->flush_container_details();
		}
	}

	/**
	 * Do container action
	 *
	 * @param $action
	 * @param $container_post_id
	 */
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

			// if it was already removed from dollie
			if ( $action === 'undeploy' ) {
				wp_trash_post( $post_id );
				Log::add( $site . ' was removed locally, no match found on dollie.io', '', 'undeploy' );
			}

			Log::add( 'container action could not be completed for ' . $currentQuery->slug, print_r( $requestTriggerResponse, true ), 'error' );
		} else {
			if ( $action === 'start' ) {
				delete_post_meta( $post_id, 'wpd_stop_container_at' );
				delete_post_meta( $post_id, 'wpd_scheduled_for_removal' );
				delete_post_meta( $post_id, 'wpd_undeploy_container_at' );
				delete_post_meta( $post_id, 'wpd_scheduled_for_undeployment' );
				// Update the site status so it counts as an active site
				wp_update_post( [
					'ID'          => $post_id,
					'post_status' => 'publish',
				] );
			}

			if ( $action === 'stop' ) {
				// Get today's timestamp.
				$today        = mktime( 0, 0, 0, date( 'm' ), date( 'd' ), date( 'Y' ) );
				$trigger_date = get_post_meta( $post_id, 'wpd_stop_container_at', true );

				//If our "stop" time has passed our current time, it's time to flip the switch and stop the container.
				if ( $trigger_date < $today ) {
					$delay_in_days = 7;

					// Calculate the "stop" date and set it 3 days into the future.
					$trigger_date = mktime( 0, 0, 0, date( 'm' ), date( 'd' ) + $delay_in_days, date( 'Y' ) );
					update_post_meta( $post_id, 'wpd_container_status', 'stopped' );
					update_post_meta( $post_id, 'wpd_scheduled_for_undeployment', 'yes' );
					update_post_meta( $post_id, 'wpd_undeploy_container_at', $trigger_date );

					// Update the site status so it won't count as an active site.
					wp_update_post( [
						'ID'          => $post_id,
						'post_status' => 'draft',
					] );
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

	/**
	 * Start container after untrash
	 *
	 * @param $post_id
	 */
	public function run_container_untrash_action( $post_id ) {
		$post_type = get_post( $post_id )->post_type;
		if ( $post_type === 'container' ) {
			$this->container_action( 'start', $post_id );
		}
	}

	/**
	 * Container manager notice
	 */
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

	/**
	 * Load scripts
	 *
	 * @param $hook
	 */
	public function load_admin_scripts( $hook ) {
		wp_register_style( 'dollie-custom-css', DOLLIE_URL . 'assets/css/admin.css', [], DOLLIE_VERSION );
		wp_enqueue_style( 'dollie-custom-css' );
		wp_enqueue_script( 'dollie-custom-js', DOLLIE_URL . 'assets/js/admin.js', [], DOLLIE_VERSION );
	}

	/**
	 * Load styles
	 *
	 * @param $hook
	 */
	public function load_styles( $hook ) {
		wp_register_style( 'dollie-front-css', DOLLIE_URL . 'assets/css/front.css', [], DOLLIE_VERSION );
		wp_enqueue_style( 'dollie-front-css' );
	}

	/**
	 * Container author box
	 */
	public function rename_author_box_title() {
		remove_meta_box( 'authordiv', 'container', 'core' );
		add_meta_box( 'authordiv', __( 'Assigned Customer to this Site', 'wpse39446_domain' ), 'post_author_meta_box', 'container', 'advanced', 'high' );
	}

	/**
	 * Update user role on container when profile changes
	 *
	 * @param $user_id
	 */
	public function update_customer_role( $user_id ) {
		// Make sure we are editing user
		if ( strpos( $user_id, 'user_' ) === false ) {
			return;
		}

		$user_id = (int) str_replace( 'user_', '', $user_id );

		if ( ! $user_id || user_can( $user_id, 'administrator' ) ) {
			return;
		}

		$fields = get_fields( 'user_' . $user_id );

		$role = '';

		if ( isset( $fields['wpd_client_site_permissions'] ) ) {
			$role = $fields['wpd_client_site_permissions'];
		}

		if ( $role === 'default' ) {
			$role = get_field( 'wpd_client_site_permission', 'options' );
		}

		$last_role = get_user_meta( $user_id, 'wpd_client_last_changed_role', true );

		if ( $last_role !== $role ) {
			update_user_meta( $user_id, 'wpd_client_last_changed_role', $role );
		}

		if ( ! $role || $last_role === $role ) {
			return;
		}

		$query = new WP_Query( [
			'author'         => $user_id,
			'post_type'      => 'container',
			'posts_per_page' => - 1,
			'post_status'    => 'publish'
		] );

		$user_data = get_userdata( $user_id );

		if ( $query->have_posts() ) {
			$params = [
				'email' => $user_data->user_email
			];

			foreach ( $query->posts as $post ) {
				$initial_username = $this->get_container_client_username( $post->ID );

				$params['container_uri'] = get_post_meta( $post->ID, 'wpd_container_uri', true );
				$params['username']      = $initial_username;
				$params['password']      = wp_generate_password();

				$action_id = as_enqueue_async_action( 'dollie/jobs/single/change_container_customer_role', [
					'params'       => $params,
					'container_id' => $post->ID,
					'user_id'      => $user_id,
					'role'         => $role
				] );

				update_post_meta( $post->ID, '_wpd_user_role_change_pending', $action_id );

			}
		}

		wp_reset_postdata();

		Log::add( 'Scheduled job to update client access role for ' . $user_data->display_name );

	}

	/**
	 * Return the username used for site launch.
	 *
	 * @param $container_id
	 *
	 * @return mixed
	 */
	public function get_container_client_username( $container_id ) {
		$initial_username = get_post_meta( $container_id, 'wpd_username', true );

		if ( ! $initial_username ) {
			$details = $this->get_container_wp_info( $container_id );

			// If we have an admin
			if ( $details->Admin ) {
				update_post_meta( $container_id, 'wpd_username', $details->Admin );
			}

			$initial_username = $details->Admin;
		}

		return $initial_username;
	}

	/**
	 * Update containers for all customers
	 *
	 * @param $post_id
	 */
	public function update_all_customers_role( $post_id ) {
		if ( $post_id !== 'options' ) {
			return;
		}

		$role = get_field( 'wpd_client_site_permission', $post_id );

		if ( get_option( 'wpd_client_last_changed_role', '' ) === $role ) {
			return;
		}

		update_option( 'wpd_client_last_changed_role', $role );

		foreach ( get_users() as $user ) {
			if ( $user->has_cap( 'administrator' ) ) {
				continue;
			}

			$this->update_customer_role( 'user_' . $user->ID );
		}

		Log::add( 'Started to update all customers access role' );
	}

	/**
	 * Change role notice
	 */
	public function change_role_option_notice() {
		?>
        <script type="text/javascript">
            (function ($) {
                var customer_role = $('[data-name="wpd_client_site_permission"]');
                if (customer_role.length) {
                    var key = customer_role.data('key');

                    $('[name="acf[' + key + ']"]').on('change', function () {
                        alert('IMPORTANT! Changing the clients permission will change the permission for ALL the websites of ALL your clients. Changing to Editor will cause all your clients to have only editor role accounts on their websites. Please note that doesn\'t affect the websites launched by administrators.');
                    })
                }
            })(jQuery);
        </script>
		<?php
	}

	/**
	 * Get container screenshot
	 *
	 * @param $container_uri
	 * @param bool $regenerate
	 *
	 * @return array|mixed|null
	 */
	public function get_screenshot( $container_uri, $regenerate = false ) {
		$requestScreenshot = Api::post( API::ROUTE_CONTAINER_SCREENSHOT, [
			'container_uri' => $container_uri,
			'regenerate'    => $regenerate ? 'yes' : '',
			'dollie_domain' => DOLLIE_INSTALL,
			'dollie_token'  => Api::get_dollie_token()
		] );

		return json_decode( wp_remote_retrieve_body( $requestScreenshot ), true );
	}

}
