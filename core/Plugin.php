<?php

namespace Dollie\Core;

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
