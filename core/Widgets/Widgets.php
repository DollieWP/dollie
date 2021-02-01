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
	private $widgets = [];

	/**
	 * Widgets constructor.
	 */
	public function __construct() {
		parent::__construct();

		// Layout Widgets
		$this->widgets[] = [
			'path'  => 'Layout/LayoutSidebarContent',
			'class' => 'Layout\LayoutSidebarContent',
			'name'  => 'dollie-layout-sidebar-content',
		];

		// Dashboard Widgets

		$this->widgets[] = [
			'path'  => 'Dashboard/LatestNews',
			'class' => 'Dashboard\LatestNews',
			'name'  => 'dollie-dashboard-latest-news',
		];

		$this->widgets[] = [
			'path'  => 'Dashboard/LaunchSite',
			'class' => 'Dashboard\LaunchSite',
			'name'  => 'dollie-dashboard-launch-site',
		];

		$this->widgets[] = [
			'path'  => 'Dashboard/RecentOrders',
			'class' => 'Dashboard\RecentOrders',
			'name'  => 'dollie-dashboard-recent-orders',
		];

		$this->widgets[] = [
			'path'  => 'Dashboard/SubscriptionDetails',
			'class' => 'Dashboard\SubscriptionDetails',
			'name'  => 'dollie-dashboard-subscription-details',
		];

		// Sites Widgets

		$this->widgets[] = [
			'path'  => 'Sites/SitesList',
			'class' => 'Sites\SitesList',
			'name'  => 'dollie-sites-list',
		];

		// General & Nav Widgets

		$this->widgets[] = [
			'path'  => 'General/Avatar',
			'class' => 'General\Avatar',
			'name'  => 'dollie-avatar',
		];

		$this->widgets[] = [
			'path'  => 'General/TopNavigation',
			'class' => 'General\TopNavigation',
			'name'  => 'dollie-top-navigation',
		];

		$this->widgets[] = [
			'path'  => 'General/MainNavigation',
			'class' => 'General\MainNavigation',
			'name'  => 'dollie-main-navigation',
		];

		$this->widgets[] = [
			'path'  => 'General/SitesNavigation',
			'class' => 'General\SitesNavigation',
			'name'  => 'dollie-sites-navigation',
		];

		$this->widgets[] = [
			'path'  => 'General/WooNavigation',
			'class' => 'General\WooNavigation',
			'name'  => 'dollie-woo-navigation',
		];

		$this->widgets[] = [
			'path'  => 'General/Login',
			'class' => 'General\Login',
			'name'  => 'dollie-login',
		];

		// Site Widgets

		$this->widgets[] = [
			'path'  => 'Site/SiteContent',
			'class' => 'Site\SiteContent',
			'name'  => 'dollie-site-content',
		];

		$this->widgets[] = [
			'path'  => 'Site/SiteNavigation',
			'class' => 'Site\SiteNavigation',
			'name'  => 'dollie-site-navigation',
		];

		$this->widgets[] = [
			'path'  => 'Site/SiteScreenshot',
			'class' => 'Site\SiteScreenshot',
			'name'  => 'dollie-site-screenshot',
		];

		// Launch Widgets

		$this->widgets[] = [
			'path'  => 'Launch/LaunchSite',
			'class' => 'Launch\LaunchSite',
			'name'  => 'dollie-launch-site',
		];

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
