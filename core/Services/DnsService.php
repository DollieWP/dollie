<?php

namespace Dollie\Core\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Log;

final class DnsService extends Singleton {
	/**
	 * Validate pending zone
	 *
	 * @return void
	 */
	public function validate_domain() {
		$dns_manager_status = get_post_meta( get_the_ID(), 'wpd_domain_dns_manager', true );
		$domain_zone        = get_post_meta( get_the_ID(), 'wpd_domain_zone', true );

		if ( 'pending' === $dns_manager_status && $domain_zone ) {
			$container = dollie()->get_container();

			$zone_response = Api::post(
				Api::ROUTE_DOMAIN_CHECK_ZONE,
				[
					'container_uri' => $container->get_original_url(),
				]
			);

			if ( is_array( $zone_response ) ) {
				if ( isset( $zone_response['container_uri'] ) && ! $zone_response['container_uri'] ) {
					delete_post_meta( get_the_ID(), 'wpd_domain_dns_manager' );
					delete_post_meta( get_the_ID(), 'wpd_domain_pending' );
					delete_post_meta( get_the_ID(), 'wpd_domain_zone' );
				} elseif ( isset( $zone_response['status'] ) && $zone_response['status'] ) {
					$domain_pending = get_post_meta( get_the_ID(), 'wpd_domain_pending', true );

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

		if ( 201 === $response['status'] ) {
			$records = dollie()->get_domain_records( $container_uri );

			wp_send_json_success(
				dollie()->load_template(
					'widgets/site/pages/domain/records',
					[
						'records'      => $records,
						'container_id' => $params['container_id'],
					],
				)
			);
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
		return Api::post(
			Api::ROUTE_DOMAIN_RECORDS_GET,
			[
				'container_uri' => $container_uri,
			]
		);
	}

	/**
	 * Get domain existing records
	 *
	 * @param string $domain
	 * @return array|bool
	 */
	public function get_existing_records( $domain ) {
		return Api::post(
			Api::ROUTE_DOMAIN_RECORDS_EXISTING,
			[
				'domain' => $domain,
			]
		);
	}

	/**
	 * Add container routes
	 *
	 * @param obj    $container
	 * @param string $domain
	 * @return bool
	 */
	public function add_container_routes( $container, $domain ) {
		$response_data = Api::post(
			Api::ROUTE_DOMAIN_ROUTES_ADD,
			[
				'container_id' => $container->get_hash(),
				'route'        => $domain,
			]
		);

		// Show an error if API can't add the Route.
		if ( ! $response_data || ! isset( $response_data['id'] ) ) {
			return false;
		}

		$response_data_www = Api::post(
			Api::ROUTE_DOMAIN_ROUTES_ADD,
			[
				'container_id' => $container->get_hash(),
				'route'        => 'www.' . $domain,
			]
		);

		if ( ! $response_data_www || ! isset( $response_data_www['id'] ) ) {
			return false;
		}

		$saved_success = false;

		if ( is_array( $response_data ) && isset( $response_data['id'] ) ) {
			$saved_success = true;
			update_post_meta( $container->get_id(), 'wpd_domain_id', $response_data['id'] );
		}

		if ( is_array( $response_data_www ) && isset( $response_data_www['id'] ) ) {
			update_post_meta( $container->get_id(), 'wpd_www_domain_id', $response_data_www['id'] );
		}

		if ( $saved_success ) {
			update_post_meta( $container->get_id(), 'wpd_domains', $domain );
			update_post_meta( $container->get_id(), 'wpd_letsencrypt_enabled', 'yes' );

			$this->update_url_with_domain( $domain, $container->get_id() );
			// Backups::instance()->make();

			Log::add_front( Log::WP_SITE_DOMAIN_LINKED, $container, [ $domain, $container->slug ] );

			// Update our container details so that the new domain will be used to make container HTTP requests.
			$container->flush_cache();
		}

		return true;
	}

	/**
	 * Remove customer domain
	 */
	public function remove_domain() {
		if ( isset( $_REQUEST['remove_customer_domain'] ) ) {
			if ( ! is_user_logged_in() ) {
				return;
			}

			$container = dollie()->get_container();

			// Prevent unauthorized access.
			if ( ! current_user_can( 'manage_options' ) && $container->is_owned_by_current_user() ) {
				return;
			}

			$this->remove_route( $container->get_id() );

			wp_redirect( get_site_url() . '/site/' . $container->get_slug() . '/?get-details' );
			exit();
		}
	}

	/**
	 * Remove domain
	 *
	 * @param null|int $post_id
	 */
	public function remove_route( $post_id = null ) {
		$container = dollie()->get_container( $post_id );
		$post_id   = $container->get_id();

		$container_id = get_post_meta( $post_id, 'wpd_container_id', true );
		$route_id     = get_post_meta( $post_id, 'wpd_domain_id', true );
		$www_route_id = get_post_meta( $post_id, 'wpd_www_domain_id', true );

		if ( $route_id ) {
			Api::post(
				Api::ROUTE_DOMAIN_ROUTES_DELETE,
				[
					'container_id' => $container_id,
					'route_id'     => $route_id,
				]
			);

		}

		if ( $www_route_id ) {
			Api::post(
				Api::ROUTE_DOMAIN_ROUTES_DELETE,
				[
					'container_id' => $container_id,
					'route_id'     => $www_route_id,
				]
			);
		}

		// Change the site URL back to temporary domain.
		$old_domain = get_post_meta( $post_id, 'wpd_domains', true );

		if ( $old_domain ) {
			$old_url = str_replace(
				[
					'http://',
					'https://',
				],
				'',
				$old_domain
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
		}

		$zone_id = get_post_meta( $post_id, 'wpd_domain_zone', true );

		if ( $zone_id ) {
			Api::post(
				Api::ROUTE_DOMAIN_REMOVE,
				[
					'container_uri' => dollie()->get_wp_site_data( 'uri', $post_id ),
					'normal'        => 'yes',
				]
			);
		}

		$container->flush_cache();
	}

	/**
	 * Update WP site url option
	 *
	 * @param $new_url
	 * @param string  $old_url
	 * @param null    $post_id
	 *
	 * @return bool|mixed
	 */
	public function update_url( $new_url, $old_url = '', $post_id = null ) {
		if ( empty( $new_url ) ) {
			return false;
		}

		$container = dollie()->get_container( $post_id );

		if ( empty( $old_url ) ) {
			$old_url = str_replace(
				[
					'http://',
					'https://',
				],
				'',
				$container->get_url()
			);
		}

		return Api::post(
			Api::ROUTE_DOMAIN_UPDATE,
			[
				'container_uri' => $container->get_original_url(),
				'route'         => $new_url,
				'install'       => $old_url,
			]
		);
	}

	/**
	 * @param null $domain
	 * @param null $post_id
	 *
	 * @return bool
	 */
	public function update_url_with_domain( $domain = null, $post_id = null ) {
		$container = dollie()->get_container( $post_id );

		if ( empty( $domain ) ) {
			$domain = get_post_meta( $container->get_id(), 'wpd_domains', true );
		}

		$old_url = str_replace(
			[
				'http://',
				'https://',
			],
			'',
			$container->get_url()
		);

		$response_data = $this->update_url( $domain, $old_url, $container->get_id() );

		if ( false === $response_data ) {
			update_post_meta( $container->get_id(), 'wpd_domain_migration_complete', 'no' );
			Log::add( 'Search and replace ' . $container->slug . ' to update URL to ' . $domain . ' has failed' );

			return false;
		}

		Log::add( 'Search and replace ' . $container->slug . ' to update URL to ' . $domain . ' has started', $response_data );

		// Mark domain URL migration as complete.
		update_post_meta( $container->id, 'wpd_domain_migration_complete', 'yes' );

		return true;
	}
}
