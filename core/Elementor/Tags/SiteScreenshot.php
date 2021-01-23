<?php

namespace Dollie\Core\Elementor\Tags;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Data_Tag;

class SiteScreenshot extends Data_Tag {

	public function get_name() {

		return 'dollie-site-screenshot';
	}

	public function get_title() {
		return __( 'Dollie Site Screenshot', 'dynamic-tags' );
	}

	public function get_group() {
		return 'dollie-tags';
	}

	public function get_categories() {
		return [ \Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY ];
	}

	public function get_value( array $options = [] ) {

		$current_id = dollie()->get_current_site_id();

		return [
			'id'  => '',
			'url' => dollie()->get_site_screenshot( $current_id, false )
		];

	}

}
