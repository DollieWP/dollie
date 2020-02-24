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
	 * @var \stdClass
	 */
	public $currentQuery;

	/**
	 * Helpers constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->currentQuery = $this->get_current_object();
	}

	/**
	 * Get current queried object data
	 *
	 * @return \stdClass
	 */
	public function get_current_object() {
		$object = get_queried_object();

		$response       = new \stdClass();
		$response->id   = $object->ID;
		$response->slug = $object->post_name;

		return $response;
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
			$container_id = $this->currentQuery->id;
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
			$container_slug = $this->currentQuery->slug;
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
		$secret = get_post_meta( $this->currentQuery->id, 'wpd_container_secret', true );

		return $this->get_container_url() . '/' . $secret;
	}

	public function get_container_id_by_string() {
		return get_page_by_path( $_GET['site'], OBJECT, 'container' )->ID;
	}

	public function get_container_slug_by_string() {
		return $_GET['site'];
	}

	public function get_site_screenshot( $container_id = null ) {
		$post_id = $container_id ?: $this->currentQuery->id;

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
		delete_transient( 'dollie_container_api_request_' . $this->currentQuery->slug . '_get_container_wp_info' );
		delete_transient( 'dollie_container_api_request_' . $this->currentQuery->slug . '_get_container_site_info' );
		delete_transient( 'dollie_site_users_' . $this->currentQuery->slug );
		delete_transient( 'dollie_site_news_' . $this->currentQuery->slug );
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

	public function staging_limit_reached() {
		return $this->count_total_containers() >= 3 && ! $this->is_live();
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
