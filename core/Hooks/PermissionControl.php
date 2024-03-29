<?php

namespace Dollie\Core\Hooks;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Services\PermissionService;

/**
 * Class PermissionControl
 *
 * @package Dollie\Core\Hooks
 */
class PermissionControl extends Singleton {
	/**
	 * PermissionControl constructor.
	 */
	public function __construct() {
		parent::__construct();

		$permission_service = PermissionService::instance();

		add_action( 'template_redirect', array( $permission_service, 'logged_in_only' ) );
		add_action( 'template_redirect', array( $permission_service, 'protect_launch_site' ) );
		add_action( 'template_redirect', array( $permission_service, 'protect_container_access' ), 1 );
		add_action( 'template_redirect', array( $permission_service, 'disable_blueprint_domain_access' ), 1 );
		add_action( 'admin_init', array( $permission_service, 'admin_access_only' ), 100 );
		add_action( 'user_has_cap', array( $permission_service, 'restrict_form_delete' ), 10, 3 );

		add_filter( 'wp_dropdown_users_args', array( $permission_service, 'allow_all_authors' ), 10, 2 );

		add_action( 'init', array( $permission_service, 'sites_capabilities' ) );
		add_filter( 'acf/save_post', array( $permission_service, 'acf_remove_cap_option' ), 10, 1 );

		add_filter( 'acf/load_field/name=wpd_view_sites_permission', array( $permission_service, 'acf_set_roles' ) );
		add_filter( 'acf/load_field/name=manage_sites_permission', array( $permission_service, 'acf_set_roles' ) );
		add_filter( 'acf/load_field/name=delete_sites_permission', array( $permission_service, 'acf_set_roles' ) );
		add_action( 'acf/render_field_settings', array( $permission_service, 'acf_field_admin_access' ) );
		add_filter( 'acf/prepare_field', array( $permission_service, 'acf_field_admin_prepare_access' ) );

		add_filter( 'pre_get_posts', array( $permission_service, 'sites_for_current_author' ) );
		add_filter( 'body_class', array( $permission_service, 'add_permissions_body_class' ) );
	}
}
