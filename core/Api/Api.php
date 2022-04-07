<?php

namespace Dollie\Core\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Log;
use Dollie\Core\Services\AuthService;

trait Api {
	/**
	 * @var string
	 */
	protected $api_url = DOLLIE_API_URL;

	/**
	 * @var string
	 */
	protected $partners_url = DOLLIE_PARTNERS_URL;

	/**
	 * @var string|null
	 */
	protected $last_call = null;

	/**
	 * @var integer
	 */
	protected $request_timeout = 30;

	/**
	 * Get request
	 *
	 * @param string $endpoint
	 *
	 * @return \WP_Error|array
	 */
	public function get_request( string $endpoint = '' ) {
		do_action( "dollie/api/{$endpoint}/before", 'get' );

		$this->last_call = $endpoint;

		$response = $this->process_request(
			wp_remote_request(
				$this->api_url . $endpoint,
				[
					'method'  => 'GET',
					'timeout' => $this->request_timeout,
					'headers' => [
						'Accept'        => 'application/json',
						'Authorization' => AuthService::instance()->get_token(),
					],
				]
			)
		);

		$response = apply_filters( 'dollie/api/after/get', $response, $endpoint );

		do_action( "dollie/api/{$endpoint}/after", 'get', $response );

		return $response;
	}

	/**
	 * Post request
	 *
	 * @param string $endpoint
	 * @param array  $data
	 *
	 * @return \WP_Error|array
	 */
	public function post_request( string $endpoint = '', array $data = [] ) {
		do_action( "dollie/api/{$endpoint}/before", 'post', $data );

		$this->last_call = $endpoint;

		$response = $this->process_request(
			wp_remote_request(
				$this->api_url . $endpoint,
				[
					'method'  => 'POST',
					'timeout' => $this->request_timeout,
					'body'    => $data,
					'headers' => [
						'Accept'        => 'application/json',
						'Authorization' => AuthService::instance()->get_token(),
					],
				]
			)
		);

		$response = apply_filters( 'dollie/api/after/post', $response, $endpoint, $data );

		do_action( "dollie/api/{$endpoint}/after", 'post', $response, $data );

		return $response;
	}

	/**
	 * Delete request
	 *
	 * @param string $endpoint
	 *
	 * @return \WP_Error|array
	 */
	public function delete_request( string $endpoint = '' ) {
		do_action( "dollie/api/{$endpoint}/before", 'delete' );

		$this->last_call = $endpoint;

		$response = $this->process_request(
			wp_remote_request(
				$this->api_url . $endpoint,
				[
					'method'  => 'DELETE',
					'timeout' => $this->request_timeout,
					'headers' => [
						'Accept'        => 'application/json',
						'Authorization' => AuthService::instance()->get_token(),
					],
				]
			)
		);

		$response = apply_filters( 'dollie/api/after/delete', $response, $endpoint );

		do_action( "dollie/api/{$endpoint}/after", 'delete', $response );

		return $response;
	}

	/**
	 * Process request
	 *
	 * @param $response
	 *
	 * @return \WP_Error|array
	 */
	private function process_request( $request ) {
		if ( is_wp_error( $request ) ) {
			Log::add( "Api error on {$this->last_call}", $request->get_error_message() );
			return new \WP_Error( 500, __( 'Internal server error.', 'dollie' ) );
		}

		$response_code = wp_remote_retrieve_response_code( $request );

		if ( 500 <= $response_code ) {
			Log::add( "Api 500 error on {$this->last_call}", print_r( $request, true ) );
			return new \WP_Error( 500, __( 'Internal server error. Please try again or contact us if the problem persits.', 'dollie' ) );
		}

		if ( 400 === $response_code ) {
			Log::add( "Api 400 error on {$this->last_call}", print_r( $request, true ) );
			return new \WP_Error( 400, __( 'Bad request. Please try again.', 'dollie' ) );
		}

		if ( 401 === $response_code ) {
			( new AuthService() )->delete_token();

			Log::add( "Api 401 error on {$this->last_call}", print_r( $request, true ) );
			return new \WP_Error( 401, __( 'Unauthorized.', 'dollie' ) );
		}

		if ( 422 === $response_code ) {
			Log::add( "Api 422 error on {$this->last_call}", print_r( $request, true ) );
			return new \WP_Error( 422, __( 'Validation failed. Please fix the issues and try again.', 'dollie' ), @json_decode( wp_remote_retrieve_body( $request ), true ) );
		}

		if ( 423 === $response_code ) {
			Log::add( "Api 423 error on {$this->last_call}", print_r( $request, true ) );
			return new \WP_Error( 423, __( 'Container is locked, cannot perform request.', 'dollie' ) );
		}

		$body = wp_remote_retrieve_body( $request );

		if ( empty( $body ) ) {
			return [];
		}

		$response = @json_decode( $body, true );

		if ( ! is_array( $response ) ) {
			Log::add( "Api error on {$this->last_call}", 'Invalid body data: ' . print_r( $response, true ) );
			return new \WP_Error( 424, __( 'Invalid response data.', 'dollie' ) );
		}

		return $response;
	}
}
