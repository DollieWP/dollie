<?php

namespace Dollie\Core\Utils;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use WP_Query;

/**
 * Class Helpers
 * @package Dollie\Core
 */
class Helpers extends Singleton {

	/**
	 * Helpers constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'template_redirect', [ $this, 'remove_customer_domain' ] );
		add_action( 'template_redirect', [ $this, 'redirect_to_new_container' ] );
		add_action( 'init', [ $this, 'set_default_view_time_total_containers' ] );
	}

	/**
	 * Get container URL
	 *
	 * @param null $container_id
	 * @param null $container_slug
	 *
	 * @return mixed|string
	 */
	public function get_container_url( $container_id = null, $container_slug = null ) {
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
		global $wp_query;
		$post_id   = $wp_query->get_queried_object_id();
		$post_slug = get_queried_object()->post_name;

		if ( $container_id === null ) {
			$container_id = $post_id;
		}
		if ( $container_slug === null ) {
			$container_slug = $post_slug;
		}

		if ( $container_location !== null ) {
			$location = '&location=' . $container_location;
		} else {
			$location = '';
		}

		$details = get_transient( 'dollie_container_api_request_' . $container_slug . '_get_container_wp_info' );

		return $this->get_container_url( $container_id, $container_slug ) . '/wp-login.php?s5token=' . $details->Token . '&string=' . $details->{'Customer ID'} . '&user=' . $details->Admin . $location;
	}

	public function get_customer_admin_url() {
		global $wp_query;
		$post_id   = $wp_query->get_queried_object_id();
		$post_slug = get_queried_object()->post_name;

		return $this->get_container_url( $post_id, $post_slug ) . '/wp-admin/';
	}

	public function get_customer_secret_url() {
		global $wp_query;
		$post_id   = $wp_query->get_queried_object_id();
		$post_slug = get_queried_object()->post_name;
		$secret    = get_post_meta( $post_id, 'wpd_container_secret', true );

		return $this->get_container_url( $post_id, $post_slug ) . '/' . $secret;
	}

	public function get_container_id_by_string() {
		return get_page_by_path( $_GET['site'], OBJECT, 'container' )->ID;
	}

	public function get_container_slug_by_string() {
		return $_GET['site'];
	}

	public function get_site_screenshot( $container_id = null ) {
		global $wp_query;

		if ( $container_id === null ) {
			$post_id   = $wp_query->get_queried_object_id();
			$post_slug = get_queried_object()->post_name;
		} else {
			$post_id   = $container_id;
			$post_slug = get_post_field( 'post_name', $post_id );
		}

		if ( false === ( get_transient( 'dollie_site_screenshot_' . $this->get_container_url( $post_id, $post_slug ) ) ) ) {
			$site = $this->get_container_url( $post_id, $post_slug ) . '/?time=' . $this->random_string( 10 );
			set_transient( 'dollie_site_screenshot_' . $this->get_container_url( $post_id, $post_slug ), $site, HOUR_IN_SECONDS * 24 );
		} else {
			$site = get_transient( 'dollie_site_screenshot_' . $this->get_container_url( $post_id, $post_slug ) );
		}

		$width      = '700';
		$query_url  = 'https://s.wordpress.com/mshots/v1/' . $site . '?w=' . $width;
		$image_tag  = '<img class="ss_screenshot_img img-fluid" alt="' . $site . '" width="' . $width . '" src="' . $query_url . '" />';
		$screenshot = '<a class="ss_screenshot_link img-fluid" target ="_blank" href="' . $site . '">' . $image_tag . '</a>';
		update_post_meta( $post_id, 'wpd_site_screenshot', $query_url );

		return $screenshot;
	}

	public function flush_container_details() {
		global $wp_query;
		$post_id   = $wp_query->get_queried_object_id();
		$post_slug = get_queried_object()->post_name;
		delete_transient( 'dollie_container_api_request_' . $post_slug . '_get_container_wp_info' );
		delete_transient( 'dollie_container_api_request_' . $post_slug . '_get_container_site_info' );
		delete_transient( 'dollie_site_users_' . $post_slug );
		delete_transient( 'dollie_site_news_' . $post_slug );
		delete_transient( 'dollie_site_screenshot_' . $this->get_container_url( $post_id, $post_slug ) );
	}

	public function remove_customer_domain() {
		if ( isset( $_POST['remove_customer_domain'] ) ) {
			global $wp_query;
			$post_id      = $wp_query->get_queried_object_id();
			$post_slug    = get_queried_object()->post_name;
			$container_id = get_post_meta( $post_id, 'wpd_container_id', true );
			$route_id     = get_post_meta( $post_id, 'wpd_domain_id', true );
			$www_route_id = get_post_meta( $post_id, 'wpd_www_domain_id', true );

			// Take output buffer for our body in our POST request
			$url     = DOLLIE_INSTALL . '/s5Api/v1/sites/' . $container_id . '/routes/' . $route_id;
			$www_url = DOLLIE_INSTALL . '/s5Api/v1/sites/' . $container_id . '/routes/' . $www_route_id;

			// Set up the request
			wp_remote_post(
				$url,
				array(
					'method'  => 'DELETE',
					'headers' => array(
						'Authorization' => 'Basic ' . base64_encode( DOLLIE_S5_USER . ':' . DOLLIE_S5_PASSWORD ),
						'Content-Type'  => 'application/json',
					),
				)
			);

			// Set up the request
			wp_remote_post(
				$www_url,
				array(
					'method'  => 'DELETE',
					'headers' => array(
						'Authorization' => 'Basic ' . base64_encode( DOLLIE_S5_USER . ':' . DOLLIE_S5_PASSWORD ),
						'Content-Type'  => 'application/json',
					),
				)
			);

			$this->flush_container_details();

			delete_post_meta( $post_id, 'wpd_domain_migration_complete' );
			delete_post_meta( $post_id, 'wpd_cloudflare_zone_id' );
			delete_post_meta( $post_id, 'wpd_cloudflare_id' );
			delete_post_meta( $post_id, 'wpd_cloudflare_active' );
			delete_post_meta( $post_id, 'wpd_cloudflare_api' );
			delete_post_meta( $post_id, 'wpd_domain_id' );
			delete_post_meta( $post_id, 'wpd_letsencrypt_setup_complete' );
			delete_post_meta( $post_id, 'wpd_letsencrypt_enabled' );
			delete_post_meta( $post_id, 'wpd_domains' );
			delete_post_meta( $post_id, 'wpd_www_domain_id' );
			delete_post_meta( $post_id, 'wpd_cloudflare_email' );

			wp_redirect( get_site_url() . '/site/' . $post_slug . '/?get-details' );
			exit();
		}
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

	public function redirect_to_new_container() {
		if ( isset( $_GET['site'] ) && $_GET['site'] === 'new' ) {
			$url = $this->get_latest_container_url();

			if ( $url ) {
				wp_redirect( $url );
				exit();
			}
		}
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

	public function convertToReadableSize( $size ) {
		$base   = log( $size ) / log( 1024 );
		$suffix = [ '', 'KB', 'MB', 'GB', 'TB' ];
		$f_base = floor( $base );

		return round( 1024 ** ( $base - floor( $base ) ), 1 ) . $suffix[ $f_base ];
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

	public function set_default_view_time_total_containers() {
		$query = new WP_Query( [
			'post_type'     => 'container',
			'post_status'   => 'publish',
			'post_per_page' => 9999999,
			'meta_query'    => [
				[
					'key'     => 'wpd_last_viewed',
					'compare' => 'NOT EXISTS'
				]
			]
		] );

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				update_post_meta( get_the_ID(), 'wpd_last_viewed', '1' );
			}
		}

		wp_reset_postdata();
		wp_reset_query();
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

	public function get_dollie_gravity_form_ids( $label = 'dollie-' ) {
		if ( ! class_exists( \GFAPI::class ) ) {
			return [];
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

		return $dollie_form_ids;
	}

	public function in_array_r( $needle, $haystack, $strict = false ) {
		foreach ( $haystack as $item ) {
			if ( ( $strict ? $item === $needle : $item == $needle ) || ( is_array( $item ) && in_array_r( $needle, $item, $strict ) ) ) {
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

	public function formatSizeUnits( $bytes ) {
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
