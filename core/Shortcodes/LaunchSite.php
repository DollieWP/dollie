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
final class LaunchSite extends Singleton implements Base {
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
		add_shortcode( 'dollie-launch-site', [ $this, 'shortcode' ] );
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
				'type'              => 'site',
				'advanced_settings' => '0',
				'button_text'       => '',

			],
			$atts
		);

		return dollie()->load_template( 'widgets/launch/launch-site', [ 'settings' => $settings ] );

	}

}
