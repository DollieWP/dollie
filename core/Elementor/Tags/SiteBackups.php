<?php

namespace Dollie\Core\Elementor\Tags;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;


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
		return [ \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY ];
	}

	public function render() {

		$available_backups = dollie()->get_site_total_backups( dollie()->get_current_site_id() );
		if ( ! $available_backups ) {
			$available_backups = 0;
		}
		echo wp_kses_post( $available_backups );

	}

}
