<?php

namespace Dollie\Core\Utils;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Modules\AccessControl;
use Dollie\Core\Modules\Backups;
use Dollie\Core\Modules\Blueprints;
use Dollie\Core\Modules\CheckSubscription;
use Dollie\Core\Modules\ContainerManagement;
use Dollie\Core\Modules\SiteInsights;
use Dollie\Core\Singleton;
use WP_Query;

/**
 * Class Helpers
 * @package Dollie\Core
 */
class Helpers extends Singleton {

	/**
	 * Get current queried object data
	 *
	 * @return \stdClass
	 */
	public function get_current_object() {
		$object   = get_queried_object();
		$response = new \stdClass();

		if ( isset( $object->ID ) ) {
			$response->id   = $object->ID;
			$response->slug = $object->post_name;
		} else {
			$response->id   = 0;
			$response->slug = '';
		}

		return $response;
	}

	/**
	 * An array of Supported Plugins
	 *
	 * @return array
	 */
	public function supported_plugins() {
		$supported_plugins = array(
			'Cloudflare'       => array(
				'reason' => esc_html__( 'We strongly recommend to keep this plugin active and installed at all times.', 'dollie' ),
				'info'   => 'foo',
			),
			'WP Fastest Cache' => array(
				'reason' => esc_html__( 'Our SiteCache solution is powered partly by the WP Fastest Cache plugin. We recommend to keep this plugin activated.', 'dollie' ),
				'info'   => 'foo',
			),
		);

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
	 *
	 * @return mixed|string
	 */
	public function get_container_url( $container_id = null ) {
		if ( $container_id === null ) {
			$container_id = $this->get_current_object()->id;
		}

		$domain     = get_post_meta( $container_id, 'wpd_domains', true );
		$cloudflare = get_post_meta( $container_id, 'wpd_domain_migration_complete', true );

		if ( $domain !== '' && $cloudflare === 'yes' ) {
			$install = 'https://' . get_post_meta( $container_id, 'wpd_domains', true );
		} else {
			$install = get_post_meta( $container_id, 'wpd_container_uri', true );
		}

		return $install;
	}

	public function get_customer_login_url( $container_id = null, $container_slug = null, $container_location = null ) {
		if ( $container_slug === null ) {
			$container_slug = $this->get_current_object()->slug;
		}

		if ( $container_location !== null ) {
			$location = '&location=' . $container_location;
		} else {
			$location = '';
		}

		$details = get_transient( 'dollie_container_api_request_' . $container_slug . '_get_container_wp_info' );

		return $this->get_container_url( $container_id ) . '/wp-login.php?s5token=' . $details->Token . '&string=' . $details->{'Customer ID'} . '&user=' . $details->Admin . $location;
	}

	public function get_customer_admin_url() {
		return $this->get_container_url() . '/wp-admin/';
	}

	public function get_customer_secret_url() {
		$secret = get_post_meta( $this->get_current_object()->id, 'wpd_container_secret', true );

		return $this->get_container_url() . '/' . $secret;
	}

	public function get_container_id_by_string() {
		return get_page_by_path( $_GET['site'], OBJECT, 'container' )->ID;
	}

	public function get_container_slug_by_string() {
		return $_GET['site'];
	}

	public function get_site_screenshot( $container_id = null ) {
		$post_id = $container_id ?: $this->get_current_object()->id;

		if ( false === ( get_transient( 'dollie_site_screenshot_' . $this->get_container_url( $post_id ) ) ) ) {
			$site = $this->get_container_url( $post_id ) . '/?time=' . $this->random_string( 10 );
			set_transient( 'dollie_site_screenshot_' . $this->get_container_url( $post_id ), $site, HOUR_IN_SECONDS * 24 );
		} else {
			$site = get_transient( 'dollie_site_screenshot_' . $this->get_container_url( $post_id ) );
		}

		$width      = '700';
		$query_url  = 'https://s.wordpress.com/mshots/v1/' . $site . '?w=' . $width;
		$image_tag  = '<img class="ss_screenshot_img img-fluid" alt="' . $site . '" width="' . $width . '" src="' . $query_url . '" />';
		$screenshot = '<a class="ss_screenshot_link img-fluid" target ="_blank" href="' . $site . '">' . $image_tag . '</a>';
		update_post_meta( $post_id, 'wpd_site_screenshot', $query_url );

		return $screenshot;
	}

	public function flush_container_details() {
		delete_transient( 'dollie_container_api_request_' . $this->get_current_object()->slug . '_get_container_wp_info' );
		delete_transient( 'dollie_container_api_request_' . $this->get_current_object()->slug . '_get_container_site_info' );
		delete_transient( 'dollie_site_users_' . $this->get_current_object()->slug );
		delete_transient( 'dollie_site_news_' . $this->get_current_object()->slug );
		delete_transient( 'dollie_site_screenshot_' . $this->get_container_url() );
	}

	public function get_latest_container_url() {
		$query = new WP_Query( [
			'author'         => get_current_user_id(),
			'post_type'      => 'container',
			'posts_per_page' => 1,
		] );

		$output = '';

		while ( $query->have_posts() ) {
			$query->the_post();
			$output = get_the_permalink();
		}

		wp_reset_postdata();

		return $output ?: false;
	}

	public function random_string( $length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ' ) {
		$str = '';
		$max = mb_strlen( $keyspace, '8bit' ) - 1;
		for ( $i = 0; $i < $length; ++ $i ) {
			$str .= $keyspace[ random_int( 0, $max ) ];
		}

		return $str;
	}

	public function secret_admin_key() {
		if ( isset( $_COOKIE['wordpress_user_sw_olduser_wefoster-cookie-hash'] ) ) {
			return '?G1HoCIM4VYCbmpk!0GOgSO01=yes';
		}

		return '';
	}


	/**
	 * @param $size
	 *
	 * @return string
	 */
	public function convert_to_readable_size( $size ) {
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

	public function count_customer_containers() {
		$query = new WP_Query( [
			'author'        => get_current_user_id(),
			'post_type'     => 'container',
			'post_per_page' => 1000,
		] );

		$total = $query->found_posts;

		wp_reset_postdata();

		return $total;
	}

	public function count_total_containers() {
		$query = new WP_Query( [
			'post_type'     => 'container',
			'post_per_page' => 1000,
		] );

		$total = $query->found_posts;

		wp_reset_postdata();

		return $total;
	}

	public function count_total_blueprints() {
		$query = new WP_Query( [
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
			]
		] );

		$total = $query->found_posts;

		wp_reset_postdata();

		return $total;
	}

	public function is_live() {
		return (bool) get_option( 'options_wpd_dollie_status' );
	}

	public function get_launch_page_id() {
		return get_option( 'options_wpd_launch_page_id' );
	}

	public function get_launch_page_url() {
		return get_permalink( $this->get_launch_page_id() );
	}

	public function get_dashboard_page_id() {
		return get_option( 'options_wpd_dashboard_page_id' );
	}

	public function get_login_page_id() {
		return get_option( 'options_wpd_login_page_id' );
	}

	public function could_not_connect_message() {
		?>
        <div class="container">
            <div class="row">
                <div class="blockquote-box blockquote-warning clearfix mt-50">
                    <div class="square pull-right">
                        <i class="fal fa-exclamation-circle"></i>
                    </div>
                    <h4>
						<?php esc_html_e( 'Sorry, we could not retrieve your site details.', 'dollie' ); ?>
                    </h4>
                    <p>
						<?php esc_html_e( 'We could not connect to your site to retrieve its details. This is usually caused by your WordPress site being unavailable or having a site-breaking error.', 'dollie' ); ?>
                        <a href="<?php echo esc_url( get_permalink() . '?get-details' ); ?>"><?php esc_html_e( 'Click here to try retrieving your site details again.', 'dollie' ); ?></a>
                    </p>
                    <p>
						<?php esc_html_e( 'Still no success?', 'dollie' ); ?>
                        <a href="<?php echo esc_url( get_site_url() . '/support' ); ?>"><?php esc_html_e( 'Create a support ticket', 'dollie' ); ?></a>
                    </p>
                </div>
            </div>
        </div>
		<?php
	}

	public function acf_get_database_field_group_keys() {
		$keys         = [];
		$field_groups = get_posts( [
			'post_type'              => 'acf-field-group',
			'posts_per_page'         => 99,
			'orderby'                => 'menu_order title',
			'order'                  => 'asc',
			'suppress_filters'       => false,
			'post_status'            => [ 'publish', 'acf-disabled' ],
			'update_post_meta_cache' => false,
		] );

		if ( ! empty( $field_groups ) ) {
			// Build array for the post name and IDs.
			$keys = wp_list_pluck( $field_groups, 'ID', 'post_title' );
		}

		return $keys;
	}

	public function get_dollie_gravity_form_ids( $label = 'dollie-', $cached = true ) {
		if ( ! class_exists( \GFAPI::class ) ) {
			return [];
		}

		// Get cached data
		$transient = get_transient( 'dollie_gform_ids' ) ?: [];

		if ( $cached && isset( $transient[ $label ] ) ) {
			return $transient[ $label ];
		}

		$forms           = \GFAPI::get_forms();
		$dollie_form_ids = [];

		foreach ( $forms as $form ) {
			$dollie = false;

			foreach ( $form['fields'] as $fields ) {
				if ( strpos( $fields->label, $label ) !== false ) {
					$dollie = true;
				}
			}

			if ( $dollie ) {
				$dollie_form_ids[] = $form['id'];
			}
		}

		$transient[ $label ] = $dollie_form_ids;
		set_transient( 'dollie_gform_ids', $transient );

		return $dollie_form_ids;
	}

	/**
	 * Get available sections
	 * @return mixed
	 */
	public function get_available_sections() {
		return AccessControl::instance()->get_available_sections();
	}

	/**
	 * Get customer total backups
	 * @return mixed
	 */
	public function get_customer_total_backups() {
		return Backups::instance()->get_customer_total_backups();
	}

	/**
	 * Get restored sites
	 *
	 * @return void
	 */
	public function list_site_restores() {
		Backups::instance()->list_site_restores();
	}

	/**
	 * List available blueprints
	 *
	 * @return void
	 */
	public function list_available_blueprints() {
		Blueprints::instance()->list_available_blueprints();
	}

	/**
	 * Return subscription name
	 *
	 * @return string
	 */
	public function subscription_name() {
		return CheckSubscription::instance()->subscription_name();
	}

	/**
	 * Return sites available to install
	 * @return integer
	 */
	public function sites_available() {
		return CheckSubscription::instance()->sites_available();
	}

	/**
	 * Return storage available based on the subscription
	 *
	 * @return string
	 */
	public function storage_available() {
		return CheckSubscription::instance()->storage_available();
	}

	public function size_limit_reached() {
		return CheckSubscription::instance()->size_limit_reached();
	}

	public function site_limit_reached() {
		return CheckSubscription::instance()->site_limit_reached();
	}

	public function staging_limit_reached() {
		return $this->count_total_containers() >= 3 && ! $this->is_live();
	}

	public function has_subscription() {
		return CheckSubscription::instance()->has_subscription();
	}

	public function has_bought_product( $user_id = 0 ) {
		return CheckSubscription::instance()->has_bought_product( $user_id );
	}

	public function get_customer_container_details() {
		return ContainerManagement::instance()->get_customer_container_details();
	}

	public function container_api_request( $url, $transient_id, $user_auth, $user_pass ) {
		return ContainerManagement::instance()->container_api_request( $url, $transient_id, $user_auth, $user_pass );
	}


	public function get_total_container_size() {
		return SiteInsights::instance()->get_total_container_size();
	}

	public function get_site_posts() {
		return SiteInsights::instance()->get_site_posts();
	}

	public function get_latest_container_posts() {
		return SiteInsights::instance()->get_latest_container_posts();
	}

	public function in_array_r( $needle, $haystack, $strict = false ) {
		foreach ( $haystack as $item ) {
			if ( ( $strict ? $item === $needle : $item == $needle ) || ( is_array( $item ) && $this->in_array_r( $needle, $item, $strict ) ) ) {
				return true;
			}
		}

		return false;
	}

	public function removeElementWithValue( $array, $key, $value ) {
		foreach ( $array as $subKey => $subArray ) {
			if ( $subArray[ $key ] === $value ) {
				unset( $array[ $subKey ] );
			}
		}

		return $array;
	}

	public function format_size_units( $bytes ) {
		if ( $bytes >= 1073741824 ) {
			$bytes = number_format( $bytes / 1073741824, 2 );
		} elseif ( $bytes >= 1048576 ) {
			$bytes = '0.' . number_format( $bytes / 1048576 );
		} elseif ( $bytes >= 1024 ) {
			$bytes = number_format( $bytes / 1024, 2 );
		} elseif ( $bytes > 1 ) {
			$bytes = $bytes;
		} elseif ( $bytes === 1 ) {
			$bytes = $bytes;
		} else {
			$bytes = '0 bytes';
		}

		return $bytes;
	}

}