<?php

namespace Dollie\Core\Jobs;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Log;
use Dollie\Core\Utils\Api;

/**
 * Class ChangeUserRole
 * @package Dollie\Core\Jobs
 */
class ChangeUserRole extends \WP_Background_Process {

	/**
	 * @var string
	 */
	protected $action = 'change_user_role_process';

	/**
	 * Define task
	 *
	 * @param mixed $item
	 *
	 * @return bool|mixed
	 */
	protected function task( $item ) {
		if ( ! isset( $item['params'] ) ) {
			return false;
		}

		$role = isset( $item['role'] ) ? $item['role'] : null;

		if ( ! isset( $item['user_id'] ) ) {
			$user_id = get_current_user_id();
		}

		if ( ! isset( $item['role'] ) ) {
			$role = dollie()->get_customer_user_role( $user_id );
		}

		$params = $item['params'];

		if ( ! is_array( $params ) || ! isset( $params['container_uri'], $params['email'], $params['password'], $params['username'] ) || ! $role ) {
			Log::add( 'Client user role change failed due to missing param.' );

			return false;
		}

		$data = [
			'container_uri'  => $params['container_uri'],
			'email'          => $params['email'],
			'password'       => $params['password'],
			'username'       => $params['username'],
			'super_email'    => get_option( 'admin_email' ),
			'super_password' => wp_generate_password(),
			'super_username' => get_option( 'options_wpd_admin_user_name' ),
			'switch_to'      => $role
		];

		Api::post( Api::ROUTE_CHANGE_USER_ROLE, $data );

		Log::add( $params['container_uri'] . ' client access was set to ' . $role );

		return false;
	}

	/**
	 * On complete
	 */
	protected function complete() {
		parent::complete();

		Log::add( 'Client access role changed successfully.' );
	}

}
