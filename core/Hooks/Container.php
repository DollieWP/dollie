<?php

namespace Dollie\Core\Hooks;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Services\NoticeService;
use Dollie\Core\Services\ContainerService;

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
		add_action( 'wp_footer', [ NoticeService::instance(), 'container_demo' ] );

		add_filter( 'init', [ $container_service, 'rewrite_rules_sub_pages' ], 20 );
		add_filter( 'query_vars', [ $container_service, 'query_vars' ] );
		add_filter( 'single_template', [ $container_service, 'container_template' ] );
		add_filter( 'document_title_parts', [ $container_service, 'update_page_title' ], 10, 1 );
	}
}