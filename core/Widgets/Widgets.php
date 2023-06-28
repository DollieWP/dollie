<?php

namespace Dollie\Core\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;

/**
 * Class Widgets
 *
 * @package Dollie\Core\Widgets
 */
class Widgets extends Singleton {

	/**
	 * @var array
	 */
	private $widgets = array();

	/**
	 * Widgets constructor.
	 */
	public function __construct() {
		parent::__construct();

		// Layout Widgets
		$this->widgets[] = array(
			'path'  => 'Layout/LayoutSidebarContent',
			'class' => 'Layout\LayoutSidebarContent',
			'name'  => 'dollie-layout-sidebar-content',
		);

		// Dashboard Widgets

		$this->widgets[] = array(
			'path'  => 'Dashboard/LatestNews',
			'class' => 'Dashboard\LatestNews',
			'name'  => 'dollie-dashboard-latest-news',
		);

		$this->widgets[] = array(
			'path'  => 'Dashboard/LaunchSite',
			'class' => 'Dashboard\LaunchSite',
			'name'  => 'dollie-dashboard-launch-site',
		);

		// WooCommerce Specific
		if ( class_exists( 'WooCommerce' ) ) {

			$this->widgets[] = array(
				'path'  => 'Dashboard/RecentOrders',
				'class' => 'Dashboard\RecentOrders',
				'name'  => 'dollie-dashboard-recent-orders',
			);

			$this->widgets[] = array(
				'path'  => 'Dashboard/AccessDetails',
				'class' => 'Dashboard\AccessDetails',
				'name'  => 'dollie-dashboard-subscription-details',
			);

		}

		// Sites Widgets

		$this->widgets[] = array(
			'path'  => 'Sites/SitesList',
			'class' => 'Sites\SitesList',
			'name'  => 'dollie-sites-list',
		);

		$this->widgets[] = array(
			'path'  => 'Customers/CustomersList',
			'class' => 'Customers\CustomersList',
			'name'  => 'dollie-customers-list',
		);

		// General & Nav Widgets

		$this->widgets[] = array(
			'path'  => 'General/Avatar',
			'class' => 'General\Avatar',
			'name'  => 'dollie-avatar',
		);

		$this->widgets[] = array(
			'path'  => 'General/TopNavigation',
			'class' => 'General\TopNavigation',
			'name'  => 'dollie-top-navigation',
		);

		$this->widgets[] = array(
			'path'  => 'General/MainNavigation',
			'class' => 'General\MainNavigation',
			'name'  => 'dollie-main-navigation',
		);

		$this->widgets[] = array(
			'path'  => 'General/SitesNavigation',
			'class' => 'General\SitesNavigation',
			'name'  => 'dollie-sites-navigation',
		);

		if ( class_exists( 'WooCommerce' ) ) {

			$this->widgets[] = array(
				'path'  => 'General/WooNavigation',
				'class' => 'General\WooNavigation',
				'name'  => 'dollie-woo-navigation',
			);

		}

		$this->widgets[] = array(
			'path'  => 'General/Login',
			'class' => 'General\Login',
			'name'  => 'dollie-login',
		);

		// Site Widgets

		$this->widgets[] = array(
			'path'  => 'Site/SiteContent',
			'class' => 'Site\SiteContent',
			'name'  => 'dollie-site-content',
		);

		$this->widgets[] = array(
			'path'  => 'Site/SiteNavigation',
			'class' => 'Site\SiteNavigation',
			'name'  => 'dollie-site-navigation',
		);

		$this->widgets[] = array(
			'path'  => 'Site/SiteScreenshot',
			'class' => 'Site\SiteScreenshot',
			'name'  => 'dollie-site-screenshot',
		);

		// Launch Widgets

		$this->widgets[] = array(
			'path'  => 'Launch/LaunchSite',
			'class' => 'Launch\LaunchSite',
			'name'  => 'dollie-launch-site',
		);

		foreach ( $this->widgets as &$widget ) {
			$widget['path']  = DOLLIE_WIDGETS_PATH . $widget['path'];
			$widget['class'] = '\Dollie\Core\Widgets\\' . $widget['class'];
		}
	}

	/**
	 * Get widgets
	 *
	 * @return array
	 */
	public function get() {
		return $this->widgets;
	}
}
