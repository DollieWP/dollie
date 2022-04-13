<?php

namespace Dollie\Core\Widgets\Site;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class SiteContent
 *
 * @package Dollie\Core\Widgets\General
 */
class SiteContent extends \Elementor\Widget_Base {

	public function __construct( $data = [], $args = null ) {
		parent::__construct( $data, $args );

		wp_register_script(
			'dollie-site-content',
			DOLLIE_ASSETS_URL . 'js/widgets/site-content.js',
			[],
			DOLLIE_VERSION,
			true
		);
		wp_register_script( 'dollie-custom-launch', DOLLIE_ASSETS_URL . 'js/custom-launch.js', [ 'jquery' ], DOLLIE_VERSION, true );
	}

	public function get_name() {
		return 'dollie-site-content';
	}

	public function get_title() {
		return esc_html__( 'Site Content', 'dollie' );
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
		if ( isset( $_GET['dollie_db_update'] ) ) {
			return false;
		}

		$data = [
			'settings'   => $this->get_settings_for_display(),
			'current_id' => dollie()->get_current_post_id(),
		];

		if ( get_post_type() !== 'container' && ! dollie()->is_elementor_editor() ) {
			esc_html_e( 'This widget will only show content when you visit a Single Dollie Site.', 'dollie' );
		} else {
			dollie()->load_template( 'widgets/site/site-content', $data, true );
		}
	}

}
