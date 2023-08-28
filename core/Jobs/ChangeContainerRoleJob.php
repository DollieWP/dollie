<?php

namespace Dollie\Core\Jobs;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Factories\Site;
use Dollie\Core\Singleton;
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

		add_action( 'dollie/jobs/single/change_container_customer_role', [ $this, 'run' ], 10, 2 );
	}

	/**
	 * Change customer role task
	 *
	 * @param Site   $container
	 * @param string $role
	 *
	 * @return boolean
	 */
	public function run( Site $container, string $role = '' ) {

		if ( $container->get_user_role() !== 'administrator' ) {
			$role = $container->get_user_role();
		} else {
			$user = $container->user();
			$role = $role ?: $user->get_container_user_role();
		}

		if ( ! $role || $role === 'administrator') {
			return false;
		}

		$container->set_role(
			[
				'email'          => $user->get_email(),
				'username'       => $container->get_username_by_role( 'administrator' ) ?: $container->get_details( 'site.admin.username' ),
				'password'       => wp_generate_password(),
				'super_email'    => get_option( 'admin_email' ),
				'super_username' => get_option( 'options_wpd_admin_user_name', 'sadmin' ),
				'super_password' => wp_generate_password(),
				'switch_to'      => $role,
			]
		);

		$container->mark_not_updated();
		$container->fetch_details();


		return false;
	}
}
