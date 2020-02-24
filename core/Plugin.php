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

	/**
	 * Access AccessControl class instance
	 *
	 * @return \Dollie\Core\Modules\AccessControl
	 */
	public function access_control() {
		return \Dollie\Core\Modules\AccessControl::instance();
	}

	/**
	 * Access Backups class instance
	 *
	 * @return \Dollie\Core\Modules\Backups
	 */
	public function backups() {
		return \Dollie\Core\Modules\Backups::instance();
	}

	/**
	 * Access Blueprints class instance
	 *
	 * @return \Dollie\Core\Modules\Blueprints
	 */
	public function blueprints() {
		return \Dollie\Core\Modules\Blueprints::instance();
	}

	/**
	 * Access CheckSubscription class instance
	 *
	 * @return \Dollie\Core\Modules\CheckSubscription
	 */
	public function subscription() {
		return \Dollie\Core\Modules\CheckSubscription::instance();
	}

	/**
	 * Access ContainerManagement class instance
	 *
	 * @return \Dollie\Core\Modules\ContainerManagement
	 */
	public function container() {
		return \Dollie\Core\Modules\ContainerManagement::instance();
	}

	/**
	 * Access Blueprints class instance
	 *
	 * @return \Dollie\Core\Modules\SiteInsights
	 */
	public function insights() {
		return \Dollie\Core\Modules\SiteInsights::instance();
	}


	public function add_timestamp_body( $classes ) {
		global $wp_query;
		$post_id   = $wp_query->get_queried_object_id();
		$post_slug = get_queried_object()->post_name;

		$timestamp = get_transient( 'dollie_site_screenshot_' . Helpers::instance()->get_container_url( $post_id, $post_slug ) );

		if ( empty( $timestamp ) ) {
			$classes[] = 'wf-site-screenshot-not-set';
		}

		return $classes;
	}

}
