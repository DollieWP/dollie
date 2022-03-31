<?php

namespace Dollie\Core\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use WP_Query;

/**
 * Class Blueprints
 *
 * @package Dollie\Core\Shortcodes
 */
final class Blockquote extends Singleton implements Base {
	/**
	 * Blockquote constructor.
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
		add_shortcode( 'dollie-blockquote', [ $this, 'shortcode' ] );
	}

	/**
	 * Shortcode logic
	 *
	 * @param $atts
	 *
	 * @return false|mixed|string
	 */
	public function shortcode( $atts ) {
		$atts = shortcode_atts(
			[
				'icon'  => 'fas fa-info-circle',
				'type'  => 'info',
				'title' => '',
			],
			$atts,
		);

		return dollie()->load_template(
			'notice',
			[
				'type'         => $atts['type'],
				'icon'         => $atts['icon'],
				'title'        => $atts['title'],
				'message'      => $content,
				'bottom_space' => true,
			]
		);
	}
}
