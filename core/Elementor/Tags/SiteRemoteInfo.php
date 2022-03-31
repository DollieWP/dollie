<?php

namespace Dollie\Core\Elementor\Tags;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;


class SiteRemoteInfo extends Tag {

	private $wpd_data = [];

	public function __construct( array $data = [] ) {
		parent::__construct( $data );

		$current_id = dollie()->get_current_post_id();

		// Get Items from Feed
		$this->wpd_data = \Dollie\Core\Modules\Container::instance()->get_container_details( $current_id );

		// Add custom items
		if ( isset( $this->wpd_data['container_details'] ) ) {
			if ( isset( $this->wpd_data['container_details']['Name'] ) ) {
				$this->wpd_data['site_data']['Name'] = $this->wpd_data['container_details']['Name'];
			}
			if ( isset( $this->wpd_data['container_details']['Description'] ) ) {
				$this->wpd_data['site_data']['Description'] = $this->wpd_data['container_details']['Description'];
			}
		}

		$this->wpd_data['customer_data']['Customer - Total Sites Launched']           = dollie()->count_customer_containers( get_current_user_id() );
		$this->wpd_data['customer_data']['Customer Subscription - Sites Available']   = dollie()->sites_available();
		$this->wpd_data['customer_data']['Customer Subscription - Storage Available'] = dollie()->storage_available();

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

			if ( is_array( $data ) || false === $data ) {
				continue;
			}

			if ( strpos( $data, '.png' ) ||
				 strpos( $data, '.jpg' ) ||
				 strpos( $data, '.jpeg' ) ||
				 filter_var( $data, FILTER_VALIDATE_URL ) ||
				 strpos( $data, '.gif' ) ) {

				continue;
			}

			$keys[ $k ] = 'Site - ' . $k;
		}

		foreach ( $this->wpd_data['customer_data'] as $k => $data ) {

			if ( is_array( $data ) || false === $data ) {
				continue;
			}

			$keys[ $k ] = $k;
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
