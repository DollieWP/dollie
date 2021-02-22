<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Modules\Sites\WP;
use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;
use Dollie\Core\Log;
use Dollie\Core\Utils\Helpers;
use WP_Query;

/**
 * Class Container
 *
 * @package Dollie\Core\Modules
 */
class Container extends Singleton {


	/**
	 * Container constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'init', [ $this, 'register' ], 0 );
		add_action( 'init', [ $this, 'register_category' ], 0 );
		add_filter( 'init', [ $this, 'rewrite_rules_container_sub_pages' ], 20 );
		add_action( 'init', [ $this, 'set_default_view_time_total_containers' ] );
		add_action( 'init', [ $this, 'check_deployment_domain_status' ] );

		add_filter( 'query_vars', [ $this, 'query_vars' ] );
		add_filter( 'single_template', [ $this, 'container_template' ] );

		add_action( 'template_redirect', [ $this, 'remove_customer_domain_action' ] );
		add_action( 'template_redirect', [ $this, 'remove_details_transients' ] );
		add_action( 'template_redirect', [ $this, 'update_details' ] );

		add_action( 'untrashed_post', [ $this, 'run_untrash_action' ] );

		add_action( 'add_meta_boxes', [ $this, 'rename_author_box_title' ] );
		add_filter( 'manage_container_posts_columns', [ $this, 'rename_author_box_column' ] );
		add_filter( 'parse_query', [ $this, 'filter_blueprint_from_sites' ] );
		add_filter( 'page_row_actions', [ $this, 'add_container_title_actions' ], 10, 2 );
		add_filter( 'manage_container_posts_columns', [ $this, 'replace_container_title_column' ] );
		add_action( 'manage_container_posts_custom_column', [ $this, 'add_new_container_title_content' ], 10, 2 );

		add_action( 'acf/save_post', [ $this, 'update_customer_role' ] );
		add_action( 'acf/save_post', [ $this, 'update_all_customers_role' ] );
		add_action( 'acf/save_post', [ $this, 'update_deployment_domain' ] );

		add_action( 'acf/input/admin_footer', [ $this, 'change_role_notice' ] );
		add_action( 'edit_form_after_title', [ $this, 'add_container_manager_notice' ] );
	}

	/**
	 * Register container post type
	 */
	public function register() {
		$labels = [
			'name'                  => _x( 'Sites', 'dollie', 'dollie' ),
			'singular_name'         => _x( 'Site', 'dollie', 'dollie' ),
			'menu_name'             => __( 'Sites', 'dollie' ),
			'name_admin_bar'        => __( 'Post Type', 'dollie' ),
			'archives'              => __( 'Site Archives', 'dollie' ),
			'attributes'            => __( 'Site Attributes', 'dollie' ),
			'parent_item_colon'     => __( 'Parent Site:', 'dollie' ),
			'all_items'             => __( 'View Sites', 'dollie' ),
			'add_new_item'          => __( 'Add New Site', 'dollie' ),
			'add_new'               => __( 'Add New', 'dollie' ),
			'new_item'              => __( 'New Site', 'dollie' ),
			'edit_item'             => __( 'Edit Site', 'dollie' ),
			'update_item'           => __( 'Update Site', 'dollie' ),
			'view_item'             => __( 'View Site', 'dollie' ),
			'view_items'            => __( 'View Sites', 'dollie' ),
			'search_items'          => __( 'Search Sites', 'dollie' ),
			'not_found'             => __( 'Not found', 'dollie' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'dollie' ),
			'featured_image'        => __( 'Featured Image', 'dollie' ),
			'set_featured_image'    => __( 'Set featured image', 'dollie' ),
			'remove_featured_image' => __( 'Remove featured image', 'dollie' ),
			'use_featured_image'    => __( 'Use as featured image', 'dollie' ),
			'insert_into_item'      => __( 'Insert into item', 'dollie' ),
			'uploaded_to_this_item' => __( 'Uploaded to this item', 'dollie' ),
			'items_list'            => __( 'Sites list', 'dollie' ),
			'items_list_navigation' => __( 'Sites list navigation', 'dollie' ),
			'filter_items_list'     => __( 'Filter items list', 'dollie' ),
		];

		$args = [
			'label'               => __( 'Site', 'dollie' ),
			'description'         => __( 'Platform Sites', 'dollie' ),
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
	public function register_category() {
		$labels = [
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
		];
		$args   = [
			'labels'            => $labels,
			'hierarchical'      => false,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => false,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => true,
		];

		register_taxonomy( 'container_category', [ 'container' ], $args );
	}

	/**
	 * Custom template for container
	 *
	 * @param $single
	 *
	 * @return string
	 */
	public function container_template( $single ) {
		global $post;

		if ( 'container' === $post->post_type ) {
			return DOLLIE_MODULE_TPL_PATH . '/container.php';
		}

		return $single;
	}

	/**
	 * Add custom query var for contaienr post type
	 *
	 * @param $vars
	 *
	 * @return array
	 */
	public function query_vars( $vars ) {
		$vars[] = 'sub_page';

		return $vars;
	}

	/**
	 * Add container subpages
	 */
	public function rewrite_rules_container_sub_pages() {
		$post_type = 'site';
		$sub_pages = '(dashboard|plugins|themes|emails|domains|backups|updates|developer-tools|blueprints|delete|migrate)';

		add_rewrite_rule(
			$post_type . '\/([^\/]+)(?:\/' . $sub_pages . ')\/?$',
			'index.php?' . $post_type . '=$matches[1]&post_type=' . $post_type . '&sub_page=$matches[2]',
			'top'
		);
	}

	/**
	 * Remove customer domain
	 */
	public function remove_customer_domain_action() {
		if ( isset( $_REQUEST['remove_customer_domain'] ) ) {

			// Prevent unauthorized access
			if ( ! is_user_logged_in() ) {
				return;
			}

			$current_query = dollie()->get_current_object();

			// Prevent unauthorized access
			if ( ! current_user_can( 'manage_options' ) && ! $current_query->author != get_current_user_id() ) {
				return;
			}

			$this->remove_domain( $current_query->id );
		}
	}

	/**
	 * @param null|int $post_id
	 */
	public function remove_domain( $post_id = null ) {

		$container = dollie()->get_current_object( $post_id );
		$post_id   = $container->id;

		$container_id = get_post_meta( $post_id, 'wpd_container_id', true );
		$route_id     = get_post_meta( $post_id, 'wpd_domain_id', true );
		$www_route_id = get_post_meta( $post_id, 'wpd_www_domain_id', true );

		if ( ! $route_id || ! $www_route_id ) {
			return;
		}

		Api::process_response(
			Api::post(
				Api::ROUTE_DOMAIN_ROUTES_DELETE,
				[
					'container_id' => $container_id,
					'route_id'     => $route_id,
				]
			)
		);

		Api::process_response(
			Api::post(
				Api::ROUTE_DOMAIN_ROUTES_DELETE,
				[
					'container_id' => $container_id,
					'route_id'     => $www_route_id,
				]
			)
		);

		// Change the site URL back to temporary domain
		$old_url = str_replace(
			[
				'http://',
				'https://',
			],
			'',
			get_post_meta( $post_id, 'wpd_domains', true )
		);

		$new_url = str_replace(
			[
				'http://',
				'https://',
			],
			'',
			dollie()->get_wp_site_data( 'uri', $post_id )
		);

		$this->update_url(
			$new_url,
			'www.' . $old_url,
			$container->id
		);

		sleep( 5 );

		$this->update_url(
			$new_url,
			$old_url,
			$container->id
		);

		dollie()->flush_container_details();

		delete_post_meta( $post_id, 'wpd_domain_migration_complete' );
		delete_post_meta( $post_id, 'wpd_cloudflare_zone_id' );
		delete_post_meta( $post_id, 'wpd_cloudflare_id' );
		delete_post_meta( $post_id, 'wpd_cloudflare_active' );
		delete_post_meta( $post_id, 'wpd_cloudflare_api' );
		delete_post_meta( $post_id, 'wpd_domain_id' );
		delete_post_meta( $post_id, 'wpd_letsencrypt_enabled' );
		delete_post_meta( $post_id, 'wpd_domains' );
		delete_post_meta( $post_id, 'wpd_www_domain_id' );
		delete_post_meta( $post_id, 'wpd_cloudflare_email' );

		wp_redirect( get_site_url() . '/site/' . $container->slug . '/?get-details' );
		exit();
	}

	/**
	 * Update WP site url option
	 *
	 * @param $new_url
	 * @param string  $old_url
	 * @param null    $container_id
	 *
	 * @return bool|mixed
	 */
	public function update_url( $new_url, $old_url = '', $container_id = null ) {

		if ( empty( $new_url ) ) {
			return false;
		}

		$container = dollie()->get_current_object( $container_id );

		if ( empty( $old_url ) ) {
			$old_url = str_replace(
				[
					'http://',
					'https://',
				],
				'',
				dollie()->get_container_url( $container->id )
			);
		}

		$request_domain_update = Api::post(
			Api::ROUTE_DOMAIN_UPDATE,
			[
				'container_uri' => dollie()->get_wp_site_data( 'uri', $container->id ),
				'route'         => $new_url,
				'install'       => $old_url,
			]
		);

		return Api::process_response( $request_domain_update );
	}

	/**
	 * @param null $domain
	 * @param null $container_id
	 *
	 * @return bool
	 */
	public function update_url_with_domain( $domain = null, $container_id = null ) {

		$container = dollie()->get_current_object( $container_id );

		if ( empty( $domain ) ) {
			$domain = get_post_meta( $container->id, 'wpd_domains', true );
		}

		$old_url = str_replace(
			[
				'http://',
				'https://',
			],
			'',
			dollie()->get_container_url( $container->id )
		);

		$response_data = $this->update_url( $domain, $old_url, $container->id );

		if ( $response_data === false ) {

			Log::add( 'Search and replace ' . $container->slug . ' to update URL to ' . $domain . ' has failed' );

			return false;
		}

		Log::add( 'Search and replace ' . $container->slug . ' to update URL to ' . $domain . ' has started', $response_data );

		// Mark domain URL migration as complete
		update_post_meta( $container->id, 'wpd_domain_migration_complete', 'yes' );

		return true;
	}

	/**
	 * Default view time total containers
	 */
	public function set_default_view_time_total_containers() {
		$query = new WP_Query(
			[
				'post_type'     => 'container',
				'post_status'   => 'publish',
				'post_per_page' => 9999999,
				'meta_query'    => [
					[
						'key'     => 'wpd_last_viewed',
						'compare' => 'NOT EXISTS',
					],
				],
			]
		);

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				update_post_meta( get_the_ID(), 'wpd_last_viewed', '1' );
			}
		}

		wp_reset_postdata();
		wp_reset_query();
	}

	/**
	 * Get container details
	 *
	 * @param $post_id
	 *
	 * @return array
	 */
	public function get_container_details( $post_id ) {
		$current_user = wp_get_current_user();
		$post         = get_post( $post_id );
		$post_slug    = $post->post_name;

		$data = [
			'container_details' => [],
			'site_data'         => [],
			'post_id'           => $post_id,
			'post_slug'         => $post_slug,
			'post'              => $post,
		];

		// Make an API request to get our customer details.
		$request = $this->get_customer_details( $post_id );

		if ( ! empty( $request ) ) {
			// Now that we have our container details get our info
			$details_url          = dollie()->get_container_url( $post_id ) . WP::PLATFORM_PATH . 'container/details';
			$details_transient_id = $post_slug . '_get_container_wp_info';

			// Make the request
			$details_request = dollie()->container_api_request( $details_url, $details_transient_id, 'container', $request->id );

			// Decode to PHP currently used
			$data['container_details'] = json_decode( json_encode( $details_request ), true );

			// Set last viewed meta
			if ( $post->post_author === $current_user->ID ) {
				update_post_meta( $post_id, 'wpd_last_viewed', time() );
			}

			if ( ! empty( $data['container_details'] ) && $data['container_details']['Secret'] !== '' ) {
				update_post_meta( $post_id, 'wpd_container_secret', $data['container_details']['Secret'] );
				update_post_meta( $post_id, 'wpd_installation_name', $data['container_details']['Name'] );
				update_post_meta( $post_id, 'wpd_installation_users', $data['container_details']['Members'] );
				update_post_meta( $post_id, 'wpd_installation_admin_email', $data['container_details']['Admin Email'] );
				update_post_meta( $post_id, 'wpd_installation_is_multisite', $data['container_details']['Multisite'] );
				update_post_meta( $post_id, 'wpd_installation_size', dollie()->convert_to_readable_size( $data['container_details']['Size'] ) );
				update_post_meta( $post_id, 'wpd_installation_size_bytes', $data['container_details']['Size'] );
				update_post_meta( $post_id, 'wpd_installation_version', $data['container_details']['Version'] );

				$post_data = [
					'post_title' => $data['container_details']['Name'],
				];
				wp_update_post( $post_data );
				wp_update_post( $post_data );
			}

			// Now that we have our container stats get our secret key
			$stats_url          = dollie()->get_container_url( $post_id ) . WP::PLATFORM_PATH . 'container/details/stats.php';
			$stats_transient_id = $post_slug . '_get_container_site_info';

			// Make the request
			$stats_request = dollie()->container_api_request( $stats_url, $stats_transient_id, 'container', $request->id );

			// Decode to PHP currently used
			$data['site_data'] = json_decode( json_encode( $stats_request ), true );

			if ( ! empty( $data['site_data'] ) && ! empty( $stats_request ) ) {
				update_post_meta( $post_id, 'wpd_installation_themes', $data['site_data']['Theme Name'] );
				update_post_meta( $post_id, 'wpd_installation_site_icon', $data['site_data']['Site Icon'] );
				update_post_meta( $post_id, 'wpd_installation_site_theme_screenshot', $data['site_data']['Theme Screenshot'] );
				update_post_meta( $post_id, 'wpd_installation_emails_sent', $data['site_data']['Emails'] );
				update_post_meta( $post_id, 'wpd_installation_posts', $data['site_data']['Posts'] );
				update_post_meta( $post_id, 'wpd_installation_pages', $data['site_data']['Pages'] );

				if ( ! empty( $data['site_data']['Sites'] ) ) {
					update_post_meta( $post_id, 'wpd_installation_sites_live', $data['site_data']['Sites']['publish'] );
					update_post_meta( $post_id, 'wpd_installation_sites_trashed', $data['site_data']['Sites']['trash'] );
				}

				update_post_meta( $post_id, 'wpd_installation_comments_total', $data['site_data']['Comments Total'] );
				update_post_meta( $post_id, 'wpd_installation_comments_moderated', $data['site_data']['Comments Moderation'] );
				update_post_meta( $post_id, 'wpd_installation_comments_trash', $data['site_data']['Comments Trash'] );
				update_post_meta( $post_id, 'wpd_installation_comments_approved', $data['site_data']['Comments Approved'] );
				update_post_meta( $post_id, 'wpd_installation_comments_spam', $data['site_data']['Comments Spam'] );
				update_post_meta( $post_id, 'wpd_installation_revisions', $data['site_data']['Revisions'] );
				update_post_meta( $post_id, 'wpd_installation_plugin_updates', $data['site_data']['Plugin Updates'] );
				update_post_meta( $post_id, 'wpd_installation_theme_updates', $data['site_data']['Theme Updates'] );

				if ( ! empty( $data['site_data']['WP Updates'] ) ) {
					update_post_meta( $post_id, 'wpd_installation_wp_updates', $data['site_data']['WP Updates'] );
				}

				if ( ! empty( $data['site_data']['WP Update Summary'] ) ) {
					update_post_meta( $post_id, 'wpd_installation_update_summary', $data['site_data']['WP Update Summary'] );
				}
			}
		}

		return $data;
	}

	/**
	 * Get container details
	 *
	 * @param null $container_id
	 *
	 * @return array|object
	 */
	public function get_customer_details( $container_id = null ) {
		$container = dollie()->get_current_object( $container_id );

		if ( ! $container->id ) {
			return [];
		}

		$request = get_transient( 'dollie_s5_container_details_' . $container->slug );

		// Only make request if it's not cached in a transient.
		if ( empty( $request ) ) {

			$container_id = get_post_meta( $container->id, 'wpd_container_id', true );

			// Set up the request
			$request_get_container = Api::post(
				API::ROUTE_CONTAINER_GET,
				[
					'container_id' => $container_id,
				]
			);

			$request_response = Api::process_response( $request_get_container, null );

			if ( empty( $request_response ) || 500 === $request_response['status'] ) {

				Log::add( 'Container details could not be fetched for ' . $container->slug, print_r( $request_get_container, true ), 'error' );

				return [];
			}

			$request = dollie()->maybe_decode_json( $request_response['body'], true );

			// Set Transient.
			set_transient( 'dollie_s5_container_details_' . $container->slug, $request, MINUTE_IN_SECONDS * 150000 );

			// Update Post Meta.
			WP::instance()->store_container_data( $container->id, $request );
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
	public function do_api_request( $url, $transient_id = null, $user_auth = null, $user_pass = null ) {
		if ( null === $user_auth || null === $user_pass ) {
			return [];
		}

		$request = [];

		if ( isset( $transient_id ) ) {
			$transient_name = 'dollie_container_api_request_' . $transient_id;
			$request        = get_transient( $transient_name );
		}

		// Only make request if it's not cached in a transient.
		if ( empty( $request ) ) {
			$response = wp_remote_request(
				$url,
				[
					'method'  => 'GET',
					'headers' => [
						'Authorization' => 'Basic ' . base64_encode( $user_auth . ':' . $user_pass ),
					],
				]
			);
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
	public function get_info( $container_id = null ) {
		$container = dollie()->get_current_object( $container_id );

		$transient_id   = $container->slug . '_get_container_wp_info';
		$transient_name = 'dollie_container_api_request_' . $transient_id;

		$data = get_transient( $transient_name );

		if ( empty( $data ) ) {
			// Make an API request to get our customer details.
			$request = dollie()->get_customer_container_details( $container->id );

			// Now that we have our container details get our info
			$details_url = dollie()->get_container_url( $container->id ) . WP::PLATFORM_PATH . 'container/details';

			$data = dollie()->container_api_request( $details_url, $transient_id, 'container', $request->id );
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
	public function get_login_token( $container_id = null, $site_username = '' ) {
		$container = dollie()->get_current_object( $container_id );

		// Make an API request to get our customer details.
		$request = dollie()->get_customer_container_details( $container->id );

		$container_url = dollie()->get_container_url( $container->id );

		if ( empty( $container_url ) ) {
			return '';
		}

		// Now that we have our container details get our info
		$details_url = dollie()->get_container_url( $container->id ) . WP::PLATFORM_PATH . 'container/details/login.php?username=' . $site_username;

		return dollie()->container_api_request( $details_url, null, 'container', $request->id );
	}

	/**
	 * Remove container details transients
	 */
	public function remove_details_transients() {
		if ( isset( $_GET['get-details'] ) ) {
			$current_query = dollie()->get_current_object();

			delete_transient( 'dollie_s5_container_details_' . $current_query->slug );
			delete_transient( 'dollie_site_users_' . $current_query->slug );
			delete_transient( 'dollie_site_news_' . $current_query->slug );
		}
	}

	/**
	 * Flush container details
	 */
	public function update_details() {
		if ( ! is_singular( 'container' ) ) {
			return;
		}

		if ( isset( $_GET['update-details'] ) ) {
			dollie()->flush_container_details();
		}

		if ( isset( $_GET['update-domain-url'] ) ) {
			$this->update_url_with_domain();
			wp_redirect( trailingslashit( get_permalink() ) . 'domains' );
			exit;
		}
	}

	/**
	 * Do container action
	 *
	 * @param $action
	 * @param $container_post_id
	 */
	public function trigger( $action, $container_post_id ) {
		$current_query = dollie()->get_current_object( $container_post_id );

		$post_id = $current_query->id;
		$site    = get_post_field( 'post_name', $post_id );

		// No need to continue if the status is unchanged.
		if ( $action === $this->get_status( $post_id ) ) {
			return;
		}

		$container_id = get_post_meta( $post_id, 'wpd_container_id', true );

		$request_trigger_container = Api::post(
			Api::ROUTE_CONTAINER_TRIGGER,
			[
				'container_id' => $container_id,
				'action'       => $action,
			]
		);

		$request_response = Api::process_response( $request_trigger_container, null );

		if ( empty( $request_response ) || 500 === $request_response['status'] ) {

			Log::add( 'Container action could not be completed for ' . $current_query->slug, print_r( $request_trigger_container, true ), 'error' );

			return;
		}

		$request = dollie()->maybe_decode_json( $request_response['body'], true );

		if ( 500 === $request['status'] ) {

			// if it was already removed from dollie
			if ( 'undeploy' === $action ) {
				wp_trash_post( $post_id );
				Log::add( $site . ' was removed locally, no match found on dollie.io', '', 'undeploy' );
			}

			Log::add( 'Container action could not be completed for ' . $current_query->slug, print_r( $request_trigger_container, true ), 'error' );

			return;
		}

		if ( 'start' === $action ) {
			delete_post_meta( $post_id, 'wpd_stop_container_at' );
			delete_post_meta( $post_id, 'wpd_scheduled_for_removal' );
			delete_post_meta( $post_id, 'wpd_undeploy_container_at' );
			delete_post_meta( $post_id, 'wpd_scheduled_for_undeployment' );
			// Update the site status so it counts as an active site
			wp_update_post(
				[
					'ID'          => $post_id,
					'post_status' => 'publish',
				]
			);

			Log::add_front( Log::WP_SITE_STARTED, $current_query, $site );
		} elseif ( 'stop' === $action ) {
			// Get today's timestamp.
			$today        = mktime( 0, 0, 0, date( 'm' ), date( 'd' ), date( 'Y' ) );
			$trigger_date = get_post_meta( $post_id, 'wpd_stop_container_at', true );

			// If our "stop" time has passed our current time, it's time to flip the switch and stop the container.
			if ( $trigger_date < $today ) {
				$delay_in_days = 7;

				// Calculate the "stop" date and set it 3 days into the future.
				$trigger_date = mktime( 0, 0, 0, date( 'm' ), date( 'd' ) + $delay_in_days, date( 'Y' ) );
				$this->set_status( $post_id, 'stopped' );
				update_post_meta( $post_id, 'wpd_scheduled_for_undeployment', 'yes' );
				update_post_meta( $post_id, 'wpd_undeploy_container_at', $trigger_date );

				// Update the site status so it won't count as an active site.
				wp_update_post(
					[
						'ID'          => $post_id,
						'post_status' => 'draft',
					]
				);
			}

			Log::add_front( Log::WP_SITE_REMOVAL_SCHEDULED, $current_query, $site );
		} elseif ( 'undeploy' === $action ) {
			delete_post_meta( $post_id, 'wpd_stop_container_at' );
			delete_post_meta( $post_id, 'wpd_scheduled_for_removal' );
			delete_post_meta( $post_id, 'wpd_undeploy_container_at' );
			delete_post_meta( $post_id, 'wpd_scheduled_for_undeployment' );
			wp_trash_post( $post_id );

			Log::add_front( Log::WP_SITE_UNDEPLOYED, $current_query, $site );
		}

		$this->set_status( $post_id, $action );
		Log::add( $site . ' status changed to: ' . $action );
	}

	/**
	 * Start container after untrash
	 *
	 * @param $post_id
	 */
	public function run_untrash_action( $post_id ) {
		$post_type = get_post( $post_id )->post_type;
		if ( 'container' === $post_type ) {
			$this->trigger( 'start', $post_id );
		}
	}

	/**
	 * Container author box
	 */
	public function rename_author_box_title() {
		remove_meta_box( 'authordiv', 'container', 'core' );
		add_meta_box( 'authordiv', __( 'Assigned Customer to this Site', 'wpse39446_domain' ), 'post_author_meta_box', 'container', 'normal', 'high' );
	}

	/**
	 * Seperate Blueprints from regular sites.
	 *
	 * @param $query
	 */
	public function filter_blueprint_from_sites( $query ) {

		if ( is_admin() and $query->query['post_type'] === 'container' ) {
			$qv               = &$query->query_vars;
			$qv['meta_query'] = [];

			if ( ! empty( $_GET['blueprint'] ) ) {
				$qv['meta_query'][] = array(
					'relation' => 'OR',
					array(
						'key'   => 'wpd_is_blueprint',
						'value' => 'yes',
					)
				);
			} else {
				$qv['meta_query'][] = array(
					'relation' => 'OR',
					[
						'key'     => 'wpd_is_blueprint',
						'compare' => 'NOT EXISTS'
                    ],
					[
						'key'     => 'wpd_is_blueprint',
						'value' => 'no'
					]
				);
			}
		}
	}

	/**
	 * Add a new column to show more info about sites.
	 *
	 * @param $columns
	 *
	 * @return array
	 */
	public function replace_container_title_column( $columns ) {

		$new = [];

		foreach ( $columns as $key => $title ) {
			if ( $key === 'title' ) {
				$new['site-title'] = 'Site'; // Our New Colomn Name
			}

			$new[ $key ] = $title;
		}

		unset( $new['title'] );

		return $new;
	}

	/**
	 * Replace the title in the new column
	 *
	 * @param $column_name
	 * @param $post_id
	 */
	public function add_new_container_title_content( $column_name, $post_ID ) {
		if ( $column_name === 'site-title' ) {
			$oldtitle = get_the_title();
			$newtitle = '<a href="' . get_edit_post_link( $post_ID ) . '">' . get_the_title() . '</a></h4><br><span class="url-box"><a target="_blank" href="' . dollie()->get_container_url( $post_ID ) . '">' . dollie()->get_container_url( $post_ID ) . '</span></a>';
			$title    = $newtitle;
			echo $title;
		}
	}

	/**
	 * Add custom actions for the site listing, and remove default ones.
	 *
	 * @param $actions
	 * @param $page_object
	 */
	public function add_container_title_actions( $actions, $page_object ) {
		if ( get_post_type() === 'container' && empty( $_GET['blueprint'] ) ) {
			$id = $page_object->ID;
			unset( $actions['trash'] );
			unset( $actions['view'] );
			unset( $actions['inline hide-if-no-js'] );
			unset( $actions['edit'] );
			$actions['manage_site'] = '<a href="' . get_the_permalink( $id ) . '" class="manage_site"><span class="dashicons dashicons-admin-tools"></span>' . __( 'Manage Site' ) . '</a>';
			$actions['google_link'] = '<a href="' . dollie()->get_customer_login_url( $id ) . '" class="login_admin"><span class="dashicons dashicons-privacy"></span>' . __( 'Login to Installation' ) . '</a>';
		} elseif ( get_post_type() === 'container' ) {
			$id = $page_object->ID;
			unset( $actions['trash'] );
			unset( $actions['view'] );
			unset( $actions['inline hide-if-no-js'] );
			unset( $actions['edit'] );
			$actions['manage_site'] = '<a href="' . get_the_permalink( $id ) . '/blueprints" class="manage_site"><span class="dashicons dashicons-admin-tools"></span>' . __( 'Manage Blueprint' ) . '</a>';
			$actions['google_link'] = '<a href="' . dollie()->get_customer_login_url( $id ) . '" class="login_admin"><span class="dashicons dashicons-privacy"></span>' . __( 'Login to Installation' ) . '</a>';
			$actions['google_link'] = '<a href="' . dollie()->get_customer_login_url( $id ) . '" class="login_admin"><span class="dashicons dashicons-privacy"></span>' . __( 'Login to Installation' ) . '</a>';
		}

		return $actions;
	}


	/**
	 * Container author box
	 */
	public function rename_author_box_column( $columns ) {

		$columns['author'] = __( 'Customer', 'dollie' );

		return $columns;
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

		if ( 'default' === $role ) {
			$role = get_field( 'wpd_client_site_permission', 'options' );
		}

		$last_role = get_user_meta( $user_id, 'wpd_client_last_changed_role', true );

		if ( $last_role !== $role ) {
			update_user_meta( $user_id, 'wpd_client_last_changed_role', $role );
		}

		if ( ! $role || $last_role === $role ) {
			return;
		}

		$query = new WP_Query(
			[
				'author'         => $user_id,
				'post_type'      => 'container',
				'posts_per_page' => - 1,
				'post_status'    => 'publish',
			]
		);

		$user_data = get_userdata( $user_id );

		if ( $query->have_posts() ) {
			$params = [
				'email' => $user_data->user_email,
			];

			foreach ( $query->posts as $post ) {
				$initial_username = $this->get_customer_username( $post->ID );

				$params['container_uri'] = dollie()->get_wp_site_data( 'uri', $post->ID );
				$params['username']      = $initial_username;
				$params['password']      = wp_generate_password();

				$action_id = as_enqueue_async_action(
					'dollie/jobs/single/change_container_customer_role',
					[
						'params'       => $params,
						'container_id' => $post->ID,
						'user_id'      => $user_id,
						'role'         => $role,
					]
				);

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
	public function get_customer_username( $container_id ) {
		$setup_data = get_post_meta( $container_id, '_wpd_setup_data', true ) ?: [];

		if ( isset( $setup_data['username'] ) ) {
			$initial_username = $setup_data['username'];
		}

		// Deprecated. Stored in _wpd_setup_data
		if ( empty( $initial_username ) ) {
			$initial_username = get_post_meta( $container_id, 'wpd_username', true );

			if ( ! empty( $initial_username ) ) {
				$setup_data['username'] = $initial_username;

				delete_post_meta( $container_id, 'wpd_username' );
				update_post_meta( $container_id, '_wpd_setup_data', $setup_data );
			}
		}

		if ( empty( $initial_username ) ) {
			$details = $this->get_info( $container_id );

			// If we have an admin
			if ( $details->Admin ) {
				$setup_data = [
					'username' => $details->Admin,
				];

				$initial_username = $details->Admin;
				update_post_meta( $container_id, '_wpd_setup_data', $setup_data );
			} else {
				return false;
			}
		}

		return $initial_username;
	}

	/**
	 * Update containers for all customers
	 *
	 * @param $post_id
	 */
	public function update_all_customers_role( $post_id ) {
		if ( 'options' !== $post_id ) {
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

	public function add_container_manager_notice() {
		if ( 'container' !== get_post_type() ) {
			return;
		}

		$container_id = $_GET['post'];
		?>
		<br>
		<div style="margin-left: 0; z-index: 0" class="dollie-notice dollie-notice-error">
			<div class="dollie-inner-message">
				<?php if ( dollie()->is_blueprint( $container_id ) ) { ?>

					<div class="dollie-message-center">
						<h3><?php esc_html_e( 'Notice - How To Manage & Update This Blueprint', 'dollie' ); ?> </h3>
						<p>
							<?php
							echo wp_kses_post( sprintf(
								__( '<a href="%s">You should manage this Blueprint using the front-end of your dashboard.</a> Only use this page to take advanced actions, like stopping or removing the blueprint completely', 'dollie' ),
								esc_url( trailingslashit( get_the_permalink( $container_id ) ) . 'blueprints' )
							) );
							?>
					</div>


				<?php } else { ?>
					<div class="dollie-message-center">
						<h3><?php esc_html_e( 'Notice - How To Manage This Site', 'dollie' ); ?> </h3>
						<p>
							<?php
							echo wp_kses_post (
							sprintf(
								__( 'We recommend managing this site on the front-end of your installation using the <a href="%s">Site Dashboard</a>. Some settings on this page should only be used by experienced Dollie Partners. If you are unsure what to do please reach out to our team.', 'dollie' ),
								esc_url( get_the_permalink( $container_id ) )
							) );
							?>
					</div>
				<?php } ?>
			</div>
		</div>
		<?php
	}


	/**
	 * Update deployment domain
	 *
	 * @param $post_id
	 */
	public function update_deployment_domain( $post_id ) {
		if ( 'options' !== $post_id ) {
			return;
		}

		$domain       = get_field( 'wpd_api_domain_custom', $post_id );
		$saved_domain = get_option( 'wpd_deployment_domain' );

		if ( ! empty( $domain ) ) {
			$domain = str_replace( [ 'https://', 'http://' ], '', $domain );
		}

		if ( $saved_domain && ! $domain ) {
			$request_remove_domain  = Api::post( Api::ROUTE_DOMAIN_REMOVE );
			$response_remove_domain = Api::process_response( $request_remove_domain );

			if ( ! $response_remove_domain['domain'] && ! $response_remove_domain['status'] ) {
				update_option( 'wpd_deployment_domain', false );
				update_option( 'deployment_domain_notice', false );
				delete_transient( 'wpd_deployment_domain_delay' );
				delete_option( 'wpd_deployment_delay_status' );
			}
		} elseif ( $domain && $domain !== $saved_domain && Helpers::instance()->is_valid_domain( $domain ) ) {
			Api::post( Api::ROUTE_DOMAIN_ADD, [ 'name' => $domain ] );

			update_option( 'wpd_deployment_domain', $domain );
			update_option( 'wpd_deployment_domain_status', false );
			update_option( 'deployment_domain_notice', false );
			delete_transient( 'wpd_deployment_domain_delay' );
			delete_option( 'wpd_deployment_delay_status' );
		}
	}

	/**
	 * Check domain availability
	 */
	public function check_deployment_domain_status() {
		if ( ! Api::get_auth_token() ) {
			return;
		}

		$domain        = get_option( 'wpd_deployment_domain' );
		$domain_status = get_option( 'wpd_deployment_domain_status' );
		$delay_status  = get_option( 'wpd_deployment_delay_status' );

		if ( $domain && ! $domain_status ) {
			if ( $delay_status && ! get_transient( 'wpd_deployment_domain_delay' ) ) {
				update_option( 'wpd_deployment_domain_status', true );
				delete_option( 'wpd_deployment_delay_status' );
			} elseif ( ! $delay_status ) {
				$request_check_domain  = Api::post( Api::ROUTE_DOMAIN_CHECK );
				$response_check_domain = Api::process_response( $request_check_domain );

				if ( $response_check_domain['domain'] === $domain && $response_check_domain['status'] ) {
					set_transient( 'wpd_deployment_domain_delay', true, 5 * MINUTE_IN_SECONDS );
					update_option( 'wpd_deployment_delay_status', true );
				}
			}
		}
	}

	/**
	 * Change role notice
	 */
	public function change_role_notice() {
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
	 * @param bool          $regenerate
	 *
	 * @return array|mixed|null
	 */
	public function get_screenshot( $container_uri, $regenerate = false ) {
		$request_screenshot = Api::post(
			API::ROUTE_CONTAINER_SCREENSHOT,
			[
				'container_uri' => $container_uri,
				'regenerate'    => $regenerate ? 'yes' : '',
			]
		);

		return Api::process_response( $request_screenshot );
	}

	/**
	 * Regenerate screenshots bulk
	 *
	 * @param array $containers
	 *
	 * @return mixed|null
	 */
	public function regenerate_screenshots( $containers = [] ) {
		$request_screenshot = Api::post(
			API::ROUTE_CONTAINER_SCREENSHOT_REGEN,
			[
				'containers' => $containers,
			]
		);

		return Api::process_response( $request_screenshot );
	}

	/**
	 * Set deploy job
	 *
	 * @param $container_id
	 * @param $job_uuid
	 *
	 * @return bool|int
	 */
	public function set_deploy_job( $container_id, $job_uuid ) {
		return update_post_meta( $container_id, 'wpd_container_deploy_job', $job_uuid );
	}

	/**
	 * Get deploy job
	 *
	 * @param $container_id
	 *
	 * @return mixed
	 */
	public function get_deploy_job( $container_id ) {
		return get_post_meta( $container_id, 'wpd_container_deploy_job', true );
	}

	/**
	 * Remove deploy job
	 *
	 * @param $container_id
	 *
	 * @return bool
	 */
	public function remove_deploy_job( $container_id ) {
		return delete_post_meta( $container_id, 'wpd_container_deploy_job' );
	}

	/**
	 * Set container status. start|stopped
	 *
	 * @param $container_id
	 * @param $status
	 *
	 * @return bool|int
	 */
	public function set_status( $container_id, $status ) {
		return update_post_meta( $container_id, 'wpd_container_status', $status );
	}

	/**
	 * Get container status
	 *
	 * @param $container_id
	 *
	 * @return mixed
	 */
	public function get_status( $container_id = null ) {
		$container = dollie()->get_current_object( $container_id );

		return get_post_meta( $container->id, 'wpd_container_status', true );
	}
}
