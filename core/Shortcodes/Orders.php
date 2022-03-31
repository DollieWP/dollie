<?php

namespace Dollie\Core\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;

/**
 * Class Orders
 *
 * @package Dollie\Core\Shortcodes
 */
final class Orders extends Singleton implements Base {
	/**
	 * Orders constructor.
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
		add_shortcode( 'my_orders', [ $this, 'shortcode' ] );
	}

	/**
	 * Shortcode logic
	 *
	 * @param $atts
	 *
	 * @return bool|false|mixed|string
	 */
	public function shortcode( $atts ) {
		$a = shortcode_atts(
			[
				'order_count' => 5,
			],
			$atts
		);

		ob_start();
		wc_get_template(
			'myaccount/my-orders.php',
			array(
				'current_user' => get_user_by( 'id', get_current_user_id() ),
				'order_count'  => $a['order_count'],
			)
		);

		return ob_get_clean();
	}
}
