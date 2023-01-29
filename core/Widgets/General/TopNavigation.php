<?php

namespace Dollie\Core\Widgets\General;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class TopNavigation
 *
 * @package Dollie\Core\Widgets\General
 */
class TopNavigation extends \Elementor\Widget_Base {

	public function get_name() {
		return 'dollie-top-navigation';
	}

	public function get_title() {
		return esc_html__( 'Top Navigation', 'dollie' );
	}

	public function get_icon() {
		return 'eicon-nav-menu';
	}

	public function get_categories() {
		return [ 'dollie-category' ];
	}

	protected function register_controls() {

	}

	protected function render() {
		do_action( 'dollie/before/top_navigation' );

		// Todo: fix menu template

		if ( is_user_logged_in() ) {
			wp_nav_menu(
				[
					'theme_location'  => 'primary',
					'container'       => false,
					'container_class' => 'nav-top',
					'container_id'    => 'navbarNavDropdown',
					'menu_class'      => 'dol-nav-top dol-flex dol-flex-wrap dol-items-center dol-list-none dol-p-0 dol-m-0',
					'fallback_cb'     => '',
					'menu_id'         => 'main-menu',
					'depth'           => 2,
					'walker'          => new \Dollie_WP_Bootstrap_Navwalker(),
				]
			);
		} else {
			wp_nav_menu(
				[
					'theme_location'  => 'landing',
					'container'       => false,
					'container_class' => 'nav-main-header',
					'container_id'    => 'navbarNavDropdown',
					'menu_class'      => 'dol-nav-top dol-flex dol-flex-wrap dol-items-center dol-list-none dol-p-0 dol-m-0',
					'fallback_cb'     => '',
					'menu_id'         => 'landing-menu',
					'depth'           => 2,
					'walker'          => new \Dollie_WP_Bootstrap_Navwalker(),
				]
			);
		}

		do_action( 'dollie/after/top_navigation' );
	}

}
