<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;
use Dollie\Core\Log;

/**
 * Class Domain
 *
 * @package Dollie\Core\Modules
 */
class Domain extends Singleton {

	/**
	 * Domain constructor
	 */
	public function __construct() {
		add_action( 'template_redirect', [ $this, 'validate_domain' ] );
		add_action( 'template_redirect', [ $this, 'remove_domain' ] );

		add_action( 'wp_ajax_dollie_create_record', [ $this, 'create_record' ] );
		add_action( 'wp_ajax_dollie_remove_record', [ $this, 'remove_record' ] );
	}

	/**
	 * Validate pending zone
	 *
	 * @return void
	 */
	public function validate_domain() {
		$dns_manager_status = get_post_meta( get_the_ID(), 'wpd_domain_dns_manager', true );
		$domain_zone        = get_post_meta( get_the_ID(), 'wpd_domain_zone', true );

		if ( 'pending' === $dns_manager_status && $domain_zone ) {
			$zone_check_request = Api::post(
				Api::ROUTE_DOMAIN_CHECK_ZONE,
				[
					'container_uri' => dollie()->get_wp_site_data( 'uri', get_the_ID() ),
				]
			);

			$zone_response = Api::process_response( $zone_check_request );

			if ( is_array( $zone_response ) ) {
				if ( isset( $zone_response['container_uri'] ) && ! $zone_response['container_uri'] ) {
					delete_post_meta( get_the_ID(), 'wpd_domain_dns_manager' );
					delete_post_meta( get_the_ID(), 'wpd_domain_pending' );
					delete_post_meta( get_the_ID(), 'wpd_domain_zone' );
				} elseif ( isset( $zone_response['status'] ) && $zone_response['status'] ) {
					$domain_pending = get_post_meta( get_the_ID(), 'wpd_domain_pending', true );

					$container = dollie()->get_current_object();
					$this->add_container_routes( $container, $domain_pending );

					update_post_meta( get_the_ID(), 'wpd_domain_dns_manager', 'active' );
					delete_post_meta( get_the_ID(), 'wpd_domain_pending' );
				}
			}
		}
	}

	/**
	 * Create record
	 *
	 * @return void
	 */
	public function create_record() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'dollie_create_record' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid request.', 'dollie' ) ] );
		}

		if ( ! isset( $_POST['data'] ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid request.', 'dollie' ) ] );
		}

		$params = [];
		parse_str( $_REQUEST['data'], $params );

		$container_uri = dollie()->get_wp_site_data( 'uri', $params['container_id'] );

		$request = Api::post(
			Api::ROUTE_DOMAIN_RECORDS_ADD,
			[
				'container_uri' => $container_uri,
				'type'          => $params['type'],
				'hostname'      => $params['hostname'],
				'content'       => $params['content'],
				'priority'      => isset( $params['priority'] ) ? $params['priority'] : '',
				'ttl'           => $params['ttl'],
			]
		);

		$response = json_decode( wp_remote_retrieve_body( $request ), true );

		if ( 200 === $response['status'] ) {
			$records = dollie()->get_domain_records( $container_uri );

			wp_send_json_success( dollie()->load_template( 'widgets/site/pages/domain/records', [ 'records' => $records ], false ) );
			exit;
		}

		wp_send_json_error();
	}

	/**
	 * Remove record
	 *
	 * @return void
	 */
	public function remove_record() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'dollie_remove_record' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid request.', 'dollie' ) ] );
		}

		if ( ! isset( $_POST['record_id'] ) || ! isset( $_POST['container_id'] ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid request.', 'dollie' ) ] );
		}

		$params = $_POST;

		$container_uri = dollie()->get_wp_site_data( 'uri', $params['container_id'] );

		$request = Api::post(
			Api::ROUTE_DOMAIN_RECORDS_REMOVE,
			[
				'container_uri' => $container_uri,
				'record_id'     => $params['record_id'],
			]
		);

		$response = json_decode( wp_remote_retrieve_body( $request ), true );

		if ( 200 === $response['status'] ) {
			wp_send_json_success();
			exit;
		}

		wp_send_json_error();
	}

	/**
	 * Allowed caa tsgs
	 *
	 * @return array
	 */
	public function allowed_caa_tags() {
		return [
			'issue',
			'issuewild',
			'iodef',
		];
	}

	/**
	 * Get domain records
	 *
	 * @param string $container_uri
	 * @return array|bool
	 */
	public function get_records( $container_uri ) {
		$request = Api::post(
			Api::ROUTE_DOMAIN_RECORDS_GET,
			[
				'container_uri' => $container_uri,
			]
		);

		return Api::process_response( $request );
	}

	/**
	 * Get domain existing records
	 *
	 * @param string $domain
	 * @return array|bool
	 */
	public function get_existing_records( $domain ) {
		$request = Api::post(
			Api::ROUTE_DOMAIN_RECORDS_EXISTING,
			[
				'domain' => $domain,
			]
		);

		return Api::process_response( $request );
	}

	/**
	 * Add container routes
	 *
	 * @param obj    $container
	 * @param string $domain
	 * @return bool
	 */
	public function add_container_routes( $container, $domain ) {
		$request = dollie()->get_customer_container_details( $container->id );

		$request_route_add = Api::post(
			Api::ROUTE_DOMAIN_ROUTES_ADD,
			[
				'container_id' => $request->id,
				'route'        => $domain,
			]
		);

		$response_data = Api::process_response( $request_route_add );

		// Show an error if API can't add the Route.
		if ( ! $response_data || ! isset( $response_data['id'] ) ) {
			Log::add_front(
				Log::WP_SITE_DOMAIN_LINK_ERROR,
				$container,
				[
					$domain,
					$container->slug,
				],
				print_r( $request_route_add, true )
			);

			return false;
		}

		$request_route_add_www = Api::post(
			Api::ROUTE_DOMAIN_ROUTES_ADD,
			[
				'container_id' => $request->id,
				'route'        => 'www.' . $domain,
			]
		);

		$response_data_www = Api::process_response( $request_route_add_www );

		if ( ! $request_route_add_www || ! isset( $response_data_www['id'] ) ) {
			Log::add_front(
				Log::WP_SITE_DOMAIN_LINK_ERROR,
				$container,
				[
					'www. ' . $domain,
					$container->slug,
				],
				print_r( $request_route_add_www, true )
			);
		}

		$saved_success = false;

		if ( is_array( $response_data ) && isset( $response_data['id'] ) ) {
			$saved_success = true;
			update_post_meta( $container->id, 'wpd_domain_id', $response_data['id'] );
		}

		if ( is_array( $response_data_www ) && isset( $response_data_www['id'] ) ) {
			update_post_meta( $container->id, 'wpd_www_domain_id', $response_data_www['id'] );
		}

		if ( $saved_success ) {
			update_post_meta( $container->id, 'wpd_domains', $domain );
			update_post_meta( $container->id, 'wpd_letsencrypt_enabled', 'yes' );

			$this->update_url_with_domain( $domain, $container->id );
			Backups::instance()->make();

			Log::add_front( Log::WP_SITE_DOMAIN_LINKED, $container, [ $domain, $container->slug ] );

			// Update our container details so that the new domain will be used to make container HTTP requests.
			dollie()->flush_container_details();
		}

		return true;
	}

	/**
	 * Remove customer domain
	 */
	public function remove_domain() {
		if ( isset( $_REQUEST['remove_customer_domain'] ) ) {

			// Prevent unauthorized access.
			if ( ! is_user_logged_in() ) {
				return;
			}

			$current_query = dollie()->get_current_object();

			// Prevent unauthorized access.
			if ( ! current_user_can( 'manage_options' ) && ! $current_query->author != get_current_user_id() ) {
				return;
			}

			$this->remove_route( $current_query->id );
		}
	}

	/**
	 * Remove domain
	 *
	 * @param null|int $post_id
	 */
	public function remove_route( $post_id = null ) {
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

		// Change the site URL back to temporary domain.
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

		$zone_id = get_post_meta( $post_id, 'wpd_domain_zone', true );

		if ( $zone_id ) {
			Api::process_response(
				Api::post(
					Api::ROUTE_DOMAIN_REMOVE,
					[
						'container_uri' => dollie()->get_wp_site_data( 'uri', $post_id ),
						'normal'        => 'yes',
					]
				)
			);
		}

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
		delete_post_meta( $post_id, 'wpd_domain_dns_manager' );
		delete_post_meta( $post_id, 'wpd_domain_pending' );
		delete_post_meta( $post_id, 'wpd_domain_zone' );

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

		if ( false === $response_data ) {
			update_post_meta( $container->id, 'wpd_domain_migration_complete', 'no' );
			Log::add( 'Search and replace ' . $container->slug . ' to update URL to ' . $domain . ' has failed' );

			return false;
		}

		Log::add( 'Search and replace ' . $container->slug . ' to update URL to ' . $domain . ' has started', $response_data );

		// Mark domain URL migration as complete.
		update_post_meta( $container->id, 'wpd_domain_migration_complete', 'yes' );

		return true;
	}

}
