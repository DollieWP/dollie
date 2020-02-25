<?php

namespace Dollie\Core\Utils;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;

/**
 * Class Api
 * @package Dollie\Core\Utils
 */
class Api extends Singleton {

	/**
	 * Api constructor.
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Post request Dollie API
	 *
	 * @param string $endpoint
	 * @param array $data
	 * @param null $timeout
	 *
	 * @return array|\WP_Error
	 */
	public static function postRequestDollie( $endpoint, $data = [], $timeout = null ) {
		$requestData = [
			'method'  => 'POST',
			'headers' => [
				'Authorization' => 'Basic ' . base64_encode( DOLLIE_S5_USER . ':' . DOLLIE_S5_PASSWORD ),
				'Content-Type'  => 'application/json',
			],
			'body'    => json_encode( $data )
		];

		if ( $timeout !== null && is_numeric( $timeout ) ) {
			$requestData['timeout'] = abs( $timeout );
		}

		return wp_remote_post( DOLLIE_INSTALL . '/s5Api/v1/sites/' . $endpoint, $requestData );
	}

	/**
	 * Get request Dollie API
	 *
	 * @param string $endpoint
	 * @param null $timeout
	 *
	 * @return array|\WP_Error
	 */
	public static function getRequestDollie( $endpoint = '', $timeout = null ) {
		$requestData = [
			'headers' => [
				'Authorization' => 'Basic ' . base64_encode( DOLLIE_S5_USER . ':' . DOLLIE_S5_PASSWORD )
			]
		];

		if ( $timeout !== null && is_numeric( $timeout ) ) {
			$requestData['timeout'] = abs( $timeout );
		}

		return wp_remote_get( DOLLIE_INSTALL . '/s5Api/v1/sites/' . $endpoint, $requestData );
	}

	/**
	 * Post request Worker API
	 *
	 * @param string $endpoint
	 * @param array $data
	 * @param string $method
	 * @param null $timeout
	 *
	 * @return array|\WP_Error
	 */
	public static function postRequestWorker( $endpoint, $data = [], $method = 'POST', $timeout = null ) {
		$requestData = [
			'method'  => $method,
			'headers' => [
				'X-Rundeck-Auth-Token' => DOLLIE_WORKER_TOKEN,
				'Content-Type'         => 'application/json',
			],
			'body'    => json_encode( $data )
		];

		if ( $timeout !== null && is_numeric( $timeout ) ) {
			$requestData['timeout'] = abs( $timeout );
		}

		return wp_remote_post( DOLLIE_WORKER_URL . '/api/' . $endpoint, $requestData );
	}

}
