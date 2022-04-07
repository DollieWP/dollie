<?php

namespace Dollie\Core\Hooks;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Services\DnsService;

/**
 * Class Domain
 *
 * @package Dollie\Core\Hooks
 */
class Domain extends Singleton {
	/**
	 * Domain constructor
	 */
	public function __construct() {
		parent::__construct();

		$dns_service = DnsService::instance();

		add_action( 'template_redirect', [ $dns_service, 'validate_domain' ] );
		add_action( 'template_redirect', [ $dns_service, 'remove_route' ] );

		add_action( 'wp_ajax_dollie_create_record', [ $dns_service, 'create_record' ] );
		add_action( 'wp_ajax_dollie_remove_record', [ $dns_service, 'remove_record' ] );
	}
}
