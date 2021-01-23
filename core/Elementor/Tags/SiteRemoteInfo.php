<?php

namespace Dollie\Core\Elementor\Tags;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;


class SiteRemoteInfo extends Tag {

	private $wpd_data = [];

	public function __construct( array $data = [] ) {
		parent::__construct( $data );

		$current_id = dollie()->get_current_site_id();

		$this->wpd_data = \Dollie\Core\Modules\Container::instance()->get_container_details( $current_id );

	}

	public function get_name() {

		return 'dollie-site-info';
	}

	public function get_title() {
		return __( 'Dollie Site Remote Info', 'dynamic-tags' );
	}


	public function get_group() {
		return 'dollie-tags';
	}

	public function get_categories() {
		return [ \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY ];
	}

	protected function _register_controls() {

		$keys = [];
		foreach ( $this->wpd_data['site_data'] as $k => $data ) {

			if ( is_array( $data ) || $data === false ) {
				continue;
			}

			if ( strpos( $data, '.png' ) ||
			     strpos( $data, '.jpg' ) ||
			     strpos( $data, '.jpeg' ) ||
			     strpos( $data, '.gif' ) ) {

				continue;
			}

			$keys[ $k ] = $k;
		}

		$this->add_control(
			'param_name',
			[
				'label'   => __( 'Choose Data', 'elementor-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $keys,
			]
		);
	}

	public function render() {

		$param_name = $this->get_settings( 'param_name' );

		if ( ! $param_name ) {
			return '';
		}

		$data = $this->wpd_data['site_data'];

		if ( ! isset( $data[ $param_name ] ) ) {
			return '';
		}

		$value = $data[ $param_name ];

		echo wp_kses_post( $value );

	}

}
