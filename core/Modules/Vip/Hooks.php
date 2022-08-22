<?php

namespace Dollie\Core\Modules\Vip;

use Dollie\Core\Singleton;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Hooks
 *
 * @package Dollie\Core\Modules\Vip
 */
class Hooks extends Singleton {
	/**
	 * Hooks constructor.
	 */
	public function __construct() {
		parent::__construct();

		$services = Services::instance();

		add_filter( 'acf/prepare_field', [ $services, 'acf_field_vip_prepare_access' ] );
		add_action( 'acf/render_field_settings', [ $services, 'acf_field_vip_access' ] );
		add_filter( 'dollie/launch_site/form_deploy_data', [ $services, 'add_vip_form_data' ] );

		add_filter( 'acf/prepare_field_group_for_import', [ $services, 'add_acf_fields' ] );

	}
}
