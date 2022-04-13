<?php

namespace Dollie\Core\Elementor\Tags;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Data_Tag;

class SiteImageRemoteInfo extends Data_Tag {

	private $container;

	public function __construct( array $data = [] ) {
		parent::__construct( $data );

		$this->container = dollie()->get_container();
	}

	public function get_name() {

		return 'dollie-site-image';
	}

	public function get_title() {
		return __( 'Dollie Site Remote Info', 'dynamic-tags' );
	}

	public function get_group() {
		return 'dollie-tags';
	}

	public function get_categories() {
		return [ \Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY ];
	}

	protected function _register_controls() {
		$keys = [];

		if ( ! is_wp_error( $this->container ) ) {
			// foreach ( $this->wpd_data['site_data'] as $k => $data ) {

			// if ( is_array( $data ) || false === $data ) {
			// continue;
			// }

			// if ( strpos( $data, '.png' ) ||
			// strpos( $data, '.jpg' ) ||
			// strpos( $data, '.jpeg' ) ||
			// strpos( $data, '.gif' ) ) {

			// $keys[ $k ] = $k;
			// }
			// }
		}

		$this->add_control(
			'param_name',
			[
				'label'   => __( 'Choose Image', 'elementor-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $keys,
			]
		);
	}

	public function get_value( array $options = [] ) {
		// $param_name = $this->get_settings( 'param_name' );

		// if ( ! $param_name ) {
		// return '';
		// }

		// $data = $this->wpd_data['site_data'];

		// if ( ! isset( $data[ $param_name ] ) ) {
		// return '';
		// }

		// $value = $data[ $param_name ];

		return [
			'id'  => '',
			'url' => '',
		];
	}
}
