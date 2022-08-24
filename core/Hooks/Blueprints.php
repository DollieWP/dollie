<?php

namespace Dollie\Core\Hooks;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Services\BlueprintService;

/**
 * Class Blueprints
 *
 * @package Dollie\Core\Hooks
 */
class Blueprints extends Singleton {
	/**
	 * Blueprints constructor.
	 */
	public function __construct() {
		parent::__construct();

		$blueprint_service = BlueprintService::instance();

		add_action( 'init', [ $blueprint_service, 'set_cookie' ], -99999 );
		add_filter( 'pre_get_document_title', [ $blueprint_service, 'change_site_title_to_blueprint_title' ] );
		add_action( 'wp_footer', [ $blueprint_service, 'notice' ] );
		add_filter( 'dollie/launch_site/form_deploy_data', [ $blueprint_service, 'get_dynamic_fields' ], 10 );
		add_action( 'wp_ajax_dollie_launch_site_blueprint_data', [ $blueprint_service, 'ajax_get_dynamic_fields' ] );
		add_action( 'wp_ajax_dollie_check_dynamic_fields', [ $blueprint_service, 'ajax_check_dynamic_fields' ] );
	}
}
