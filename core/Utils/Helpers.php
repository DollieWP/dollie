<?php

namespace Dollie\Core\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use WP_Query;

use Dollie\Core\Modules\Sites\WP;
use Dollie\Core\Singleton;

use Dollie\Core\Modules\Backups;
use Dollie\Core\Modules\Blueprints;
use Dollie\Core\Modules\Subscription;
use Dollie\Core\Modules\Container;
use Dollie\Core\Modules\SiteInsights;

/**
 * Class Helpers
 *
 * @package Dollie\Core
 */
class Helpers extends Singleton {

	/**
	 * Get current queried object data
	 *
	 * @param int $id
	 *
	 * @return \stdClass
	 */
	public function get_current_object( $id = null ) {
		if ( isset( $id ) && $id > 0 ) {
			$object = get_post( $id );
		} else {
			$object = get_queried_object();
		}

		$response = [
			'id'     => 0,
			'slug'   => '',
			'author' => 0,
		];

		if ( ! $object instanceof \WP_Post ) {
			return (object) $response;
		}

		if ( get_post_type( $object->ID ) !== 'container' ) {
			return (object) $response;
		}

		if ( isset( $object->ID ) ) {
			$response['id']     = $object->ID;
			$response['slug']   = $object->post_name;
			$response['author'] = $object->post_author;
		}

		return (object) $response;
	}

	/**
	 * An array of Supported Plugins
	 *
	 * @return array
	 */
	public function supported_plugins() {
		$supported_plugins = [
			'Cloudflare'       => [
				'reason' => esc_html__( 'We strongly recommend to keep this plugin active and installed at all times.', 'dollie' ),
				'info'   => 'foo',
			],
			'WP Fastest Cache' => [
				'reason' => esc_html__( 'Our SiteCache solution is powered partly by the WP Fastest Cache plugin. We recommend to keep this plugin activated.', 'dollie' ),
				'info'   => 'foo',
			],
		];

		return $supported_plugins;
	}

	/**
	 * An array of Unsupported Plugins
	 *
	 * @return array
	 */
	public function unsupported_plugins() {
		return [];
	}

	/**
	 * Get container URL
	 *
	 * @param null $container_id
	 * @param bool $temp_url
	 *
	 * @return mixed|string
	 */
	public function get_container_url( $container_id = null, $temp_url = false ) {
		if ( $container_id === null ) {
			$container_id = $this->get_current_object()->id;
		}

		$domain              = get_post_meta( $container_id, 'wpd_domains', true );
		$domain_url_migrated = get_post_meta( $container_id, 'wpd_domain_migration_complete', true );

		if ( '' !== $domain && 'yes' === $domain_url_migrated && ! $temp_url ) {
			$install = 'https://' . get_post_meta( $container_id, 'wpd_domains', true );
		} else {
			$install = dollie()->get_wp_site_data( 'uri', $container_id );
		}

		return strtolower( $install );
	}

	/**
	 * Get container status
	 *
	 * @param $container_id
	 *
	 * @return mixed
	 */
	public function get_container_status( $container_id ) {
		return Container::instance()->get_status( $container_id );
	}

	/**
	 * @param null $container_id
	 * @param null $container_location
	 *
	 * @return string
	 */
	public function get_customer_login_url( $container_id = null, $container_location = null ) {
		$container = dollie()->get_current_object( $container_id );
		$url       = home_url( 'site_login_redirect?site=' . $container->id );

		if ( ! empty( $container_location ) ) {
			$url = add_query_arg( 'location', $container_location, $url );
		}

		return wp_nonce_url( $url, 'get_site_login', '_nonce' );
	}

	/**
	 * @param null $container_id
	 * @param null $container_location
	 *
	 * @return string
	 */
	public function final_customer_login_url( $container_id = null, $container_location = null ) {
		$container     = $this->get_current_object( $container_id );
		$container_url = $this->get_container_url( $container->id );

		if ( empty( $container_url ) ) {
			return '';
		}

		$url                 = $this->get_container_url( $container->id ) . '/wp-login.php';
		$pending_role_action = get_post_meta( $container->id, '_wpd_user_role_change_pending', true );

		// User role change is not yet complete.
		if ( $pending_role_action ) {
			return '';
		}

		if ( null !== $container_location ) {
			$location = '&location=' . $container_location;
		} else {
			$location = '';
		}

		// Generate different login links based on user access control.
		$client_user_id = get_post_field( 'post_author', $container->id );
		$user_role      = dollie()->get_customer_user_role( $client_user_id );

		// If we are admin and visiting client site
		if ( 'administrator' !== $user_role && current_user_can( 'manage_options' ) ) {
			$username = get_option( 'options_wpd_admin_user_name' );
		} else {
			$username = Container::instance()->get_customer_username( $container->id );
		}

		$token_details = Container::instance()->get_login_token( $container->id, $username );

		if ( empty( $token_details ) ) {
			return $url;
		}

		if ( is_object( $token_details ) && isset( $token_details->Token ) && $token_details->Token ) {
			$url .= '?s5token=' . $token_details->Token . $location;
		} else {
			// keep old functionality for fallback
			$details = Container::instance()->get_info( $container->id );
			$url     .= '?s5token=' . $details->Token . $location;
		}

		return $url;
	}

	/**
	 * @return string
	 */
	public function get_customer_admin_url() {
		return $this->get_container_url() . '/wp-admin/';
	}

	/**
	 * @return string
	 */
	public function get_customer_secret_url() {
		$secret = get_post_meta( $this->get_current_object()->id, 'wpd_container_secret', true );

		return $this->get_container_url() . '/' . $secret;
	}

	/**
	 * @return int
	 */
	public function get_container_id_by_string() {
		return get_page_by_path( $_GET['site'], OBJECT, 'container' )->ID;
	}

	/**
	 * @return mixed
	 */
	public function get_container_slug_by_string() {
		return $_GET['site'];
	}

	/**
	 * @param null $container_id
	 *
	 * @return string
	 */
	public function get_site_screenshot( $container_id = null, $html = true ) {
		$post_id = $this->get_current_object( $container_id )->id;

		$deploying = 'pending' === \Dollie\Core\Modules\Container::instance()->get_status( $post_id );

		$site  = $this->get_container_url( $post_id );
		$image = DOLLIE_ASSETS_URL . 'img/generating-preview.png';

		if ( ! empty( get_field( 'default_screenshot', 'option' ) ) ) {
			$image = get_field( 'default_screenshot', 'option' );
		}

		if ( ! $deploying && ! empty( $site ) ) {
			$screenshot = get_transient('wpd_container_ss_'. $post_id);

			if ( ! is_array( $screenshot ) || empty( $screenshot ) || ! isset( $screenshot['desktop'] ) || ! $screenshot['desktop'] ) {
				$screenshot = $this->container_screenshot( $site );
			}

			if ( is_array( $screenshot ) && ! empty( $screenshot ) && isset( $screenshot['desktop'] ) && $screenshot['desktop'] ) {
				$image = $screenshot['desktop'] . '?ver=' . current_time( 'timestamp' );

				set_transient('wpd_container_ss_' . $post_id, $screenshot, MINUTE_IN_SECONDS * 60 );
			}
		}

		if ( ! $html ) {
			return $image;
		}

		$image_tag = '<img width="700" class="dol-block dol-object-cover" alt="' . esc_attr( $site ) . '" src="' . $image . '" />';

		if ( $deploying ) {
			return $image_tag;
		}

		$screenshot = '<a class="dol-block dol-leading-none" target ="_blank" href="' . esc_url( $site ) . '">' . $image_tag . '</a>';
		update_post_meta( $post_id, 'wpd_site_screenshot', $image );

		return $screenshot;
	}

	/**
	 * @param null $container_id
	 */
	public function flush_container_details( $container_id = null ) {
		$container = $this->get_current_object( $container_id );

		delete_transient( 'dollie_container_api_request_' . $container->slug . '_get_container_wp_info' );
		delete_transient( 'dollie_container_api_request_' . $container->slug . '_get_container_site_info' );
		delete_transient( 'dollie_site_users_' . $container->slug );
		delete_transient( 'dollie_site_news_' . $container->slug );
		delete_transient( 'dollie_site_new_screenshot_' . $this->get_container_url( $container_id ) );

		$this->regenerate_containers_screenshot( [ $this->get_container_url( $container_id ) ] );
	}

	/**
	 * @return bool
	 */
	public function get_latest_container_url() {
		$query = new WP_Query(
			[
				'post_status'    => [ 'publish', 'draft' ],
				'author'         => get_current_user_id(),
				'post_type'      => 'container',
				'posts_per_page' => 1,
			]
		);

		$output = '';

		while ( $query->have_posts() ) {
			$query->the_post();
			$output = get_the_permalink();
		}

		wp_reset_postdata();

		return $output ?: false;
	}

	/**
	 * @param $length
	 * @param string $keyspace
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function random_string( $length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ' ) {
		$str = '';
		$max = mb_strlen( $keyspace, '8bit' ) - 1;
		for ( $i = 0; $i < $length; ++ $i ) {
			$str .= $keyspace[ random_int( 0, $max ) ];
		}

		return $str;
	}

	/**
	 * @return string
	 */
	public function secret_admin_key() {
		return '?' . dollie()->random_string( 12 );
	}

	/**
	 * @param $size
	 *
	 * @return string
	 */
	public function convert_to_readable_size( $size ) {
		if ( ! $size ) {
			return $size;
		}

		$base   = log( $size ) / log( 1024 );
		$suffix = [ '', 'KB', 'MB', 'GB', 'TB' ];
		$f_base = floor( $base );

		return round( 1024 ** ( $base - floor( $base ) ), 1 ) . $suffix[ $f_base ];
	}

	/**
	 * @param $size
	 *
	 * @return string
	 * @deprecated use convert_to_readable_size()
	 * @uses convert_to_readable_size()
	 */
	public function convertToReadableSize( $size ) {
		return $this->convert_to_readable_size( $size );
	}

	/**
	 * @return int
	 */
	public function count_customer_containers($user_id = null) {
		if ( $user_id === null ) {
			$user_id = get_current_user_id();
		}
		$query = new WP_Query(
			[
				'author'        => $user_id,
				'post_type'     => 'container',
				'post_per_page' => 1000,
				'post_status'   => 'publish',
			]
		);

		$total = $query->found_posts;

		wp_reset_postdata();

		return $total;
	}

	/**
	 * @return int
	 */
	public function count_total_containers() {
		$query = new WP_Query(
			[
				'post_type'     => 'container',
				'post_per_page' => 1000,
			]
		);

		$total = $query->found_posts;

		wp_reset_postdata();

		return $total;
	}

	/**
	 * @return int
	 */
	public function count_total_blueprints() {
		$query = new WP_Query(
			[
				'post_type'     => 'container',
				'post_per_page' => 1000,
				'meta_query'    => [
					'relation' => 'AND',
					[
						'key'   => 'wpd_blueprint_created',
						'value' => 'yes',
					],
					[
						'key'     => 'wpd_installation_blueprint_title',
						'compare' => 'EXISTS',
					],
				],
			]
		);

		$total = $query->found_posts;

		wp_reset_postdata();

		return $total;
	}

	/**
	 * @return bool
	 */
	public function is_live() {
		return (bool) get_option( 'options_wpd_api_domain' ) && (bool) \Dollie\Core\Utils\Api::get_auth_token();
	}

	/**
	 * @return mixed|void
	 */
	public function get_launch_page_id() {
		if ( function_exists('pll_get_post') ) {
			return (int) pll_get_post(get_option('options_wpd_launch_page_id'));
		} else {
			return (int) get_option('options_wpd_launch_page_id');
		}
	}

	/**
	 * @return false|string
	 */
	public function get_launch_page_url() {
		return get_permalink( $this->get_launch_page_id() );
	}

	/**
	 * @return false|string
	 */
	public function get_launch_page_title()
	{
		return get_the_title($this->get_launch_page_id());
	}

	/**
	 * @return mixed|void
	 */
	public function get_launch_blueprint_page_id() {
		if (function_exists('pll_get_post')) {
			return (int) pll_get_post(get_option('options_wpd_launch_blueprint_page_id'));
		} else {
			return (int) get_option('options_wpd_launch_blueprint_page_id');
		}
	}

	/**
	 * @return false|string
	 */
	public function get_launch_blueprint_page_url() {
		return get_permalink( $this->get_launch_blueprint_page_id() );
	}

	/**
	 * @return false|string
	 */
	public function get_launch_blueprint_page_title()
	{
		return get_the_title($this->get_launch_blueprint_page_id());
	}

	/**
	 * @return mixed|void
	 */
	public function get_dashboard_page_id() {
		if (function_exists('pll_get_post')) {
			return (int) pll_get_post(get_option('options_wpd_dashboard_page_id'));
		} else {
			return (int) get_option('options_wpd_dashboard_page_id');
		}
	}

	/**
	 * @return false|string
	 */
	public function get_dashboard_page_url() {
		return get_permalink( $this->get_dashboard_page_id() );
	}

	/**
	 * @return false|string
	 */
	public function get_dashboard_page_title()
	{
		return get_the_title($this->get_dashboard_page_id());
	}

	/**
	 * @return mixed|void
	 */
	public function get_login_page_id() {
		if (function_exists('pll_get_post')) {
			return (int) pll_get_post(get_option('options_wpd_login_page_id'));
		} else {
			return (int) get_option('options_wpd_login_page_id');
		}
	}

	public function get_login_page_url()
	{
		return get_permalink($this->get_login_page_id());
	}

	/**
	 * @return false|string
	 */
	public function get_login_page_title()
	{
		return get_the_title($this->get_login_page_id());
	}

	/**
	 * @return mixed|void
	 */
	public function get_sites_page_id() {
		if (function_exists('pll_get_post')) {
			return (int) pll_get_post(get_option('options_wpd_sites_page_id'));
		} else {
			return (int) get_option('options_wpd_sites_page_id');
		}
	}

	/**
	 * @return false|string
	 */
	public function get_sites_page_url() {
		return get_permalink( $this->get_sites_page_id() );
	}

	/**
	 * @return false|string
	 */
	public function get_sites_page_title()
	{
		return get_the_title($this->get_sites_page_id());
	}

	/**
	 * @return mixed|void
	 */
	public function get_site_template_id() {
		return (int) get_option( 'options_wpd_site_template_id' );
	}

	/**
	 * @param $site_id
	 * @param string $page
	 *
	 * @return string
	 */
	public function get_site_url( $site_id, $page = '' ) {
		$site = get_post( $site_id );

		if ( ! $site ) {
			return '';
		}

		return $page ? get_the_permalink( $site_id ) . trailingslashit( $page ) : get_the_permalink( $site_id );
	}

	/**
	 *
	 */
	public function could_not_connect_message() {
		?>
        <div class="dol-border dol-border-solid dol-border-primary-100 dol-rounded dol-overflow-hidden">
            <div class="dol-flex dol-items-center dol-bg-red-600">
                <div class="dol-p-4 lg:dol-px-8 lg:dol-py-4 dol-bg-red-700 dol-flex dol-items-center dol-justify-center">
                    <i class="fas fa-exclamation-circle dol-text-white dol-text-2xl"></i>
                </div>
                <h4 class="dol-px-4 lg:dol-px-8 lg:dol-py-4 dol-m-0 dol-p-0 dol-font-bold dol-text-white dol-text-base md:dol-text-xl">
					<?php esc_html_e( 'Sorry, we could not retrieve your site details', 'dollie' ); ?>
                </h4>
            </div>
            <div class="dol-px-4 dol-py-2 lg:dol-px-8 lg:dol-py-6 dol-bg-gray-100">
                <div class="dol-mb-4">
					<?php esc_html_e( 'We could not connect to your site to retrieve its details. This is usually caused by your WordPress site being unavailable or having a site-breaking error.', 'dollie' ); ?>
                </div>

                <div>
                    <a href="<?php echo esc_url( get_permalink() . '?get-details' ); ?>"
                       class="dol-text-sm dol-text-white hover:dol-text-white dol-inline-block dol-px-4 dol-py-2 dol-bg-gray-800 hover:dol-bg-gray-900 dol-rounded">
						<?php esc_html_e( 'Retry', 'dollie' ); ?>
                    </a>

                    <a href="<?php echo esc_url( get_site_url() . '/support' ); ?>"
                       class="dol-text-sm dol-text-white hover:dol-text-white dol-inline-block dol-px-4 dol-py-2 dol-bg-gray-500 hover:dol-bg-gray-600 dol-rounded">
						<?php esc_html_e( 'Create a support ticket', 'dollie' ); ?>
                    </a>
                </div>
            </div>
        </div>
		<?php
	}

	/**
	 * Get all registered settings groups by name and key
	 *
	 * @return array
	 */
	public function acf_get_database_field_group_keys() {
		$keys   = [];
		$groups = acf_get_field_groups();

		foreach ( $groups as $group ) {
			$keys[ $group['title'] ] = $group['key'];
		}

		return $keys;
	}

	/**
	 * Get customer total backups
	 *
	 * @return mixed
	 * @deprecated
	 *
	 * @use get_site_total_backups
	 */
	public function get_customer_total_backups() {
		return Backups::instance()->count();
	}

	/**
	 * Get site total backups
	 *
	 * @param null $container_id
	 *
	 * @return mixed
	 */
	public function get_site_total_backups( $container_id = null ) {
		return Backups::instance()->count( $container_id );
	}

	/**
	 * Get restored sites
	 *
	 * @return mixed
	 */
	public function get_site_restores() {
		return Backups::instance()->get_site_restores();
	}

	/**
	 * Get available blueprints
	 *
	 * @return mixed
	 */
	public function get_available_blueprints() {
		return Blueprints::instance()->get_available();
	}

	/**
	 * Return subscription name
	 *
	 * @return string
	 */
	public function subscription_name() {
		return Subscription::instance()->subscription_name();
	}

	/**
	 * Return sites available to install
	 *
	 * @return integer
	 */
	public function sites_available() {
		return Subscription::instance()->sites_available();
	}

	/**
	 * Return storage available based on the subscription
	 *
	 * @return string
	 */
	public function storage_available() {
		return Subscription::instance()->storage_available();
	}

	/**
	 * @return mixed
	 */
	public function size_limit_reached() {
		return Subscription::instance()->size_limit_reached();
	}

	/**
	 * @return mixed
	 */
	public function site_limit_reached() {
		return Subscription::instance()->site_limit_reached();
	}

	/**
	 * @return bool
	 */
	public function staging_limit_reached() {
		return $this->count_total_containers() >= 3 && ! $this->is_live();
	}

	/**
	 * @return mixed
	 */
	public function has_subscription() {
		return Subscription::instance()->has_subscription();
	}

	/**
	 * @return boolean
	 */
	public function has_partner_subscription() {
		return Subscription::instance()->has_partner_subscription();
	}

	/**
	 * @return boolean
	 */
	public function is_partner_subscription_trial() {
		return Subscription::instance()->is_partner_subscription_trial();
	}

	/**
	 * @return int
	 */
	public function get_partner_subscription_credits() {
		return Subscription::instance()->get_partner_subscription_credits();
	}

	/**
	 * @param int $user_id
	 *
	 * @return mixed
	 */
	public function has_bought_product( $user_id = 0 ) {
		return Subscription::instance()->has_bought_product( $user_id );
	}

	/**
	 * @param null $container_id
	 *
	 * @return mixed
	 */
	public function get_customer_container_details( $container_id = null ) {
		return Container::instance()->get_customer_details( $container_id );
	}

	/**
	 * @param $url
	 * @param $transient_id
	 * @param $user_auth
	 * @param null $user_pass
	 *
	 * @return mixed
	 */
	public function container_api_request( $url, $transient_id, $user_auth, $user_pass = null ) {
		return Container::instance()->do_api_request( $url, $transient_id, $user_auth, $user_pass );
	}

	/**
	 * @param $container_uri
	 * @param bool $regenerate
	 *
	 * @return mixed
	 */
	public function container_screenshot( $container_uri, $regenerate = false ) {
		return Container::instance()->get_screenshot( $container_uri, $regenerate );
	}

	public function regenerate_containers_screenshot( $containers = [] ) {
		return Container::instance()->regenerate_screenshots( $containers );
	}

	public function get_total_container_size() {
		return SiteInsights::instance()->get_total_container_size();
	}

	/**
	 * @return mixed
	 */
	public function get_site_posts() {
		return SiteInsights::instance()->get_posts();
	}

	/**
	 * @return mixed
	 */
	public function get_latest_container_posts() {
		return SiteInsights::instance()->get_latest_container_posts();
	}

	/**
	 * @return mixed
	 */
	public function get_support_link() {
		return get_field( 'wpd_support_link', 'options' );
	}

	/**
	 * Check if a domain is using CloudFlare
	 *
	 * @param $domain
	 *
	 * @return bool
	 */
	public function is_using_cloudflare( $domain ) {
		// Check NS record with Google DNS
		$ns_response = wp_remote_get( 'https://dns.google.com/resolve?name=' . $domain . '&type=NS' );
		if ( ! is_wp_error( $ns_response ) ) {
			$ns_record = wp_remote_retrieve_body( $ns_response );
			$ns_record = @json_decode( $ns_record, true );

			if ( is_array( $ns_record ) && isset( $ns_record['Answer'] ) ) {
				foreach ( $ns_record['Answer'] as $item ) {
					if ( strpos( $item['data'], 'cloudflare' ) !== false ) {

						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Get site preview link.
	 *
	 * @param string $type url|path
	 *
	 * @return mixed|string
	 */
	public function get_preview_url( $type = 'url' ) {
		$preview_path = get_option( 'options_wpd_site_preview_path', 'preview' );

		if ( 'url' === $type ) {
			return home_url( $preview_path );
		}

		return $preview_path;
	}

	/**
	 * Get the link for quick checkout
	 *
	 * @param $product_id
	 * @param $blueprint_id
	 *
	 * @return mixed
	 */
	public function get_woo_checkout_link( $product_id, $blueprint_id ) {
		return Subscription::instance()->get_checkout_link( $product_id, $blueprint_id );
	}

	/**
	 * Get customer user role
	 *
	 * @param null $user_id
	 *
	 * @return mixed|void
	 */
	public function get_customer_user_role( $user_id = null ) {
		$user_id = $user_id ?: get_current_user_id();
		$role    = get_user_meta( $user_id, 'wpd_client_site_permissions', true );

		if ( ! $role || 'default' === $role ) {
			$role = get_field( 'wpd_client_site_permission', 'options' );
		}

		return $role ?: 'administrator';
	}

	/**
	 * @param $needle
	 * @param $haystack
	 * @param bool $strict
	 *
	 * @return bool
	 */
	public function in_array_r( $needle, $haystack, $strict = false ) {
		if ( ! is_array( $haystack ) ) {
			return false;
		}

		foreach ( $haystack as $item ) {
			if ( ( $strict ? $item === $needle : $item == $needle ) || ( is_array( $item ) && $this->in_array_r( $needle, $item, $strict ) ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param $array
	 * @param $key
	 * @param $value
	 *
	 * @return mixed
	 */
	public function remove_element_with_value( $array, $key, $value ) {
		foreach ( $array as $subKey => $subArray ) {
			if ( $subArray[ $key ] === $value ) {
				unset( $array[ $subKey ] );
			}
		}

		return $array;
	}

	/**
	 * @param $bytes
	 *
	 * @return int|string
	 */
	public function format_size_units( $bytes ) {
		if ( $bytes >= 1073741824 ) {
			$bytes = number_format( $bytes / 1073741824, 2 );
		} elseif ( $bytes >= 1048576 ) {
			$bytes = '0.' . number_format( $bytes / 1048576 );
		} elseif ( $bytes >= 1024 ) {
			$bytes = number_format( $bytes / 1024, 2 );
		} elseif ( $bytes > 1 || 1 === $bytes ) {
			return $bytes;
		} else {
			$bytes = '0 bytes';
		}

		return $bytes;
	}

	/**
	 * Get Elementor template types
	 *
	 * @return array
	 */
	public function get_elementor_template_types() {
		return [
			'container' => __( 'Site View', 'dollie' ),
		];
	}

	/**
	 * @param $plugin_slug
	 *
	 * @return bool
	 */
	public function is_plugin_active( $plugin_slug ) {
		$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );

		foreach ( $active_plugins as $plugin ) {
			if ( $plugin === $plugin_slug ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param $plugin_path
	 *
	 * @return bool
	 */
	public function is_plugin_installed( $plugin_path ) {
		$plugins = get_plugins();

		return isset( $plugins[ $plugin_path ] );
	}


	/**
	 * @param $plugin_path
	 *
	 * @return bool
	 */
	public function has_dollie_layout_widget() {
		$template_id = dollie()->get_site_template_id();
		$meta        = get_post_meta( $template_id, '_elementor_data' );

		foreach ( $meta as $index => $string ) {
			if ( strpos( $string, 'dollie-layout-' ) !== false ) {
				return true;
			}
		}

		return false;

	}

	/**
	 * @param $id
	 *
	 * @return bool
	 */
	public function is_blueprint( $id ) {

		return get_post_meta( $id, 'wpd_is_blueprint', true ) === 'yes';
	}

	/**
	 * Check valid json.
	 *
	 * on check failure if $return_data is false, false is returned instead of the passed data
	 *
	 * @param $data
	 * @param bool $assoc
	 * @param bool $return_data
	 *
	 * @return array|bool
	 */
	public function maybe_decode_json( $data, $assoc = false, $return_data = true ) {

		$return = $return_data ? $data : false;

		if ( is_array( $data ) ) {
			return $data;
		}

		if ( ! is_string( $data ) ) {
			return $return;
		}

		$data = json_decode( $data, $assoc );

		if ( is_array( $data ) && ( json_last_error() === JSON_ERROR_NONE ) ) {
			return $data;
		}

		return $return;
	}

	/**
	 * @param $data
	 * @param null $container_id
	 *
	 * @return mixed
	 */
	public function get_wp_site_data( $data, $container_id = null ) {
		return WP::instance()->get_container_data( $data, $container_id );
	}

	/**
	 * @param $domain
	 *
	 * @return false|int
	 */
	public function is_valid_domain( $domain ) {
		return preg_match( '/^[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\.[a-zA-Z]{2,}$/i', $domain );
	}

	public function is_preview() {
		return get_query_var( 'dollie_route_name' ) === 'dollie_preview';
	}

	public function get_current_site_id() {

		$current_id = get_the_ID();
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return $current_id;
		}

		$elementor_builder = \Elementor\Plugin::instance()->editor->is_edit_mode()
		                     || \Elementor\Plugin::instance()->preview->is_preview()
		                     || isset( $_GET['elementor_library'] );

		if ( $elementor_builder ) {

			$my_sites = get_posts(
				[
					'post_type'      => 'container',
					'author'         => get_current_user_id(),
					'posts_per_page' => 1,
				]
			);

			if ( ! empty( $my_sites ) ) {
				$current_id = $my_sites[0]->ID;
			}
		}

		return $current_id;
	}
}
