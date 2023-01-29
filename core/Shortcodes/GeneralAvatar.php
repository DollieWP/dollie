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
final class GeneralAvatar extends Singleton implements Base {
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
		add_shortcode( 'dollie-general-avatar', [ $this, 'shortcode' ] );
	}

	/**
	 * Shortcode logic
	 *
	 * @param $atts
	 *
	 * @return bool|false|mixed|string
	 */
	public function shortcode( $atts ) {

		return dollie()->load_template( 'widgets/general/avatar' );

	}

}
