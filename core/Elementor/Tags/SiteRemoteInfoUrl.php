<?php

namespace Dollie\Core\Elementor\Tags;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;

class SiteRemoteInfoUrl extends Tag {

	private $wpd_data = [];

	public function __construct( array $data = [] ) {
		parent::__construct( $data );

		$current_id = dollie()->get_current_post_id();
		$container  = dollie()->get_container( $current_id );

		if ( is_wp_error( $container ) ) {
			return;
		}
		$details = $container->get_details();

		return;

		if ( is_wp_error( $details ) ) {
			return;
		}

		$this->wpd_data                                  = $details;
		$this->wpd_data['customer_data']['Support Link'] = dollie()->get_support_link();
		$this->wpd_data['site_data']['URL']              = dollie()->get_container_url( $current_id );

	}

	public function get_name() {

		return 'dollie-site-info-url';
	}

	public function get_title() {
		return __( 'Dollie Site Remote Info URL', 'dollie' );
	}


	public function get_group() {
		return 'dollie-tags';
	}

	public function get_categories() {
		return [ \Elementor\Modules\DynamicTags\Module::URL_CATEGORY ];
	}

	protected function register_controls() {

		$keys = [];
		if ( isset( $this->wpd_data['site_data'] ) ) {
			foreach ( $this->wpd_data['site_data'] as $k => $data ) {

				if ( is_array( $data ) || false === $data ) {
					continue;
				}

				if ( ! filter_var( $data, FILTER_VALIDATE_URL ) ) {
					continue;
				}

				$keys[ $k ] = 'Site - ' . $k;
			}
		}

		if ( isset( $this->wpd_data['customer_data'] ) ) {
			foreach ( $this->wpd_data['customer_data'] as $k => $data ) {

				if ( is_array( $data ) || false === $data ) {
					continue;
				}

				$keys[ $k ] = $k;
			}
		}

		$this->add_control(
			'param_name',
			[
				'label'   => __( 'Choose Data', 'dollie' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $keys,
			]
		);
	}


	public function render() {

		$param_name = $this->get_settings( 'param_name' );

		if ( ! $param_name ) {
			return;
		}

		$data = $this->wpd_data['site_data'];

		if ( ! isset( $data[ $param_name ] ) ) {
			return;
		}

		$value = $data[ $param_name ];

		echo wp_kses_post( $value );
	}
}
