<?php

namespace Dollie\Core\Hooks;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Services\NoticeService;
use Dollie\Core\Services\ContainerService;
use Dollie\Core\Services\DeployService;

/**
 * Class Container
 *
 * @package Dollie\Core\Hooks
 */
class Container extends Singleton {
	/**
	 * Container constructor.
	 */
	public function __construct() {
		parent::__construct();

		$container_service = ContainerService::instance();

		add_action( 'wp', [ $container_service, 'add_acf_form_head' ], 9 );
		add_action( 'template_redirect', [ $container_service, 'fetch_container' ] );
		add_action( 'template_redirect', [ $container_service, 'restore' ] );
		add_action( 'template_redirect', [ DeployService::instance(), 'check_deploy_bulk' ] );

		add_filter( 'init', [ $container_service, 'rewrite_rules_sub_pages' ], 20 );
		add_filter( 'query_vars', [ $container_service, 'query_vars' ] );
		add_filter( 'single_template', [ $container_service, 'container_template' ] );
		add_filter( 'the_title', [ $container_service, 'remove_pending_from_title' ], 10, 2 );

		add_action( 'wp_ajax_dollie_check_deploy', [ $container_service, 'check_deploy' ] );
		add_action( 'wp_ajax_dollie_update_assets', [ $container_service, 'update_assets' ] );
		add_action( 'admin_post_dollie_action_start_container', [ $container_service, 'start' ] );
		add_action( 'admin_post_dollie_action_stop_container', [ $container_service, 'stop' ] );
		add_action( 'admin_post_dollie_set_container_owner', [ $container_service, 'update_owner' ] );
	}
}
