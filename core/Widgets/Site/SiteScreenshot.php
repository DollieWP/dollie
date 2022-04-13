<?php

namespace Dollie\Core\Widgets\Site;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class SiteNavigation
 *
 * @package Dollie\Core\Widgets\Site
 */
class SiteScreenshot extends \Elementor\Widget_Base {

	public function get_name() {
		return 'dollie-site-screenshot';
	}

	public function get_title() {
		return esc_html__( 'Site Screenshot', 'dollie' );
	}

	public function get_icon() {
		return 'eicon-bullet-list';
	}

	public function get_categories() {
		return [ 'dollie-category' ];
	}

	protected function render() {
		$data = [
			'settings'   => $this->get_settings_for_display(),
			'current_id' => get_the_ID(),
		];

		if ( dollie()->is_elementor_editor() ) {
			$my_sites = get_posts(
				[
					'post_type'      => 'container',
					'author'         => get_current_user_id(),
					'posts_per_page' => 1,
				]
			);

			if ( ! empty( $my_sites ) ) {
				$data['current_id'] = $my_sites[0]->ID;
			}
		}

		if ( get_post_type() !== 'container' && ! dollie()->is_elementor_editor() ) {
			esc_html_e( 'This widget will only show content when you visit a Single Dollie Site.', 'dollie' );
		} else {
			dollie()->load_template( 'widgets/site/site-screenshot', $data, true );
		}
	}

}
