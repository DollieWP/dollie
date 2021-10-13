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

		$request = Api::post(
			Api::ROUTE_DOMAIN_RECORDS_ADD,
			[
				'type'     => $params['type'],
				'hostname' => $params['hostname'],
				'content'  => $params['content'],
				'priority' => isset( $params['priority'] ) ? $params['priority'] : '',
				'ttl'      => $params['ttl'],
			]
		);

		// todo: Handle response
		$response = Api::process_response( $request );

		$records = dollie()->get_domain_records( dollie()->get_wp_site_data( 'uri', get_the_ID() ) );

		wp_send_json_success( dollie()->load_template( 'widgets/site/pages/domain/records', [ 'records' => $records ], false ) );
	}

	/**
	 * Remove record
	 *
	 * @return void
	 */
	public function remove_records() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'dollie_remove_record' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid request.', 'dollie' ) ] );
		}

		wp_send_json_success();
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

			Container::instance()->update_url_with_domain( $domain, $container->id );
			Backups::instance()->make();

			Log::add_front( Log::WP_SITE_DOMAIN_LINKED, $container, [ $domain, $container->slug ] );

			// Update our container details so that the new domain will be used to make container HTTP requests.
			dollie()->flush_container_details();
		}

		return true;
	}

}
