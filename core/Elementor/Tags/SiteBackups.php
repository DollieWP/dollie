<?php

namespace Dollie\Core\Elementor\Tags;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;


class SiteBackups extends Tag {

	public function get_name() {

		return 'dollie-site-backups';
	}

	public function get_title() {
		return __( 'Dollie Backups', 'dynamic-tags' );
	}

	public function get_group() {
		return 'dollie-tags';
	}

	public function get_categories() {
		return [ Module::TEXT_CATEGORY ];
	}

	public function render() {
		$current_id = dollie()->get_current_post_id();
		$container = dollie()->get_container( $current_id );

		if ( is_wp_error( $container ) ) {
			echo 0;
		}

		$details = $container->get_details();

		if ( is_wp_error( $details ) ) {
			echo 0;
		}

		if ( empty( $details['backups'] ) ) {
			$available_backups = 0;
		} else {
			$available_backups = $details['backups'];
		}

		echo wp_kses_post( $available_backups );
	}

}
