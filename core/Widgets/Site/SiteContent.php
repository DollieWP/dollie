<?php

namespace Dollie\Core\Widgets\Site;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Utils\Tpl;

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

	protected function _register_controls() {

	}

	protected function render() {
		if ( isset( $_GET['dollie_db_update'] ) ) {
			return false;
		}

		$current_id        = get_the_ID();
		$elementor_builder = \Elementor\Plugin::instance()->editor->is_edit_mode()
		                     || \Elementor\Plugin::instance()->preview->is_preview()
		                     || isset( $_GET['elementor_library'] );

		if ( $elementor_builder ) {

			$my_sites = get_posts( [
				'post_type'      => 'container',
				'author'         => get_current_user_id(),
				'posts_per_page' => 1
			] );

			if ( ! empty( $my_sites ) ) {
				$current_id = $my_sites[0]->ID;
			}
		}

		$data = [
			'settings'   => $this->get_settings_for_display(),
			'current_id' => $current_id
		];

		Tpl::load( 'widgets/site/site-content', $data, true );
	}

}
