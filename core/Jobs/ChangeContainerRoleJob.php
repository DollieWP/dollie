<?php

namespace Dollie\Core\Jobs;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;
use Dollie\Core\Log;

/**
 * Class ChangeContainerRoleJob
 *
 * @package Dollie\Core\Modules
 */
class ChangeContainerRoleJob extends Singleton {
	/**
	 * Jobs constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'dollie/jobs/single/change_container_customer_role', [ $this, 'run' ], 10, 4 );
	}

	/**
	 * Change customer role task
	 *
	 * @param $params
	 * @param null   $container_id
	 * @param null   $user_id
	 * @param null   $role
	 *
	 * @return bool
	 */
	public function run( $params, $container_id, $user_id, $role = null ) {
		$role = $role ?: dollie()->get_customer_user_role( $user_id );

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
			'switch_to'      => $role,
		];

		Api::post( Api::ROUTE_CONTAINER_CHANGE_USER_ROLE, $data );

		sleep( 5 );

		delete_post_meta( $container_id, '_wpd_user_role_change_pending' );

		Log::add( $params['container_uri'] . ' client access was set to ' . $role );

		return false;
	}

}
