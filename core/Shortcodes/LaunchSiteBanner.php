<?php

namespace Dollie\Core\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use WP_Query;

/**
 * Class Sites
 *
 * @package Dollie\Core\Shortcodes
 */
final class LaunchSiteBanner extends Singleton implements Base {
	/**
	 * Sites constructor.
	 */
	public function __construct() {
		parent::__construct();
		add_action( 'init', [ $this, 'register' ] );
	}

	/**
	 * Add shortcode
	 *
	 * @return mixed|void
	 */
	public function register() {
		add_shortcode( 'dollie-launch-site-banner', [ $this, 'shortcode' ] );
	}

	/**
	 * Shortcode logic
	 *
	 * @param $atts
	 *
	 * @return bool|false|mixed|string
	 */
	public function shortcode( $atts ) {

		$settings = shortcode_atts(
			[
				'icon_enabled'      => 'yes',
				'icon' => 'fas fa-rocket',
				'title'       => __( 'LAUNCH YOUR SITE', 'dollie' ),
				'subtitle' => __( 'Get started and launch your site within minutes.', 'dollie' ),
				'button' => __( 'Launch', 'dollie' ),

			],
			$atts
		);
		return dollie()->load_template( 'widgets/dashboard/launch-site', $settings );

	}

}
