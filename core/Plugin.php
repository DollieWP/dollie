<?php

namespace Dollie\Core;

use Dollie\Core\Utils\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Plugin
 * @package Dollie\Core
 */
class Plugin extends Singleton {

	public function __construct() {
		parent::__construct();

		add_filter( 'body_class', [ $this, 'add_timestamp_body' ] );
	}

	/**
	 * Access Helpers class instance
	 *
	 * @return Helpers
	 */
	public function helpers() {
		return Helpers::instance();
	}

	public function add_timestamp_body( $classes ) {
		$currentQuery = $this->helpers()->get_current_object();
		$timestamp    = get_transient( 'dollie_site_screenshot_' . $this->helpers()->get_container_url( $currentQuery->id, $currentQuery->slug ) );

		if ( empty( $timestamp ) ) {
			$classes[] = 'wf-site-screenshot-not-set';
		}

		return $classes;
	}

}
