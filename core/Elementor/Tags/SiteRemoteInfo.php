<?php

namespace Dollie\Core\Elementor\Tags;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;

class SiteRemoteInfo extends Tag {

	private $container;
	private $wpd_data = array();

	public function __construct( array $data = array() ) {
		parent::__construct( $data );

		$this->wpd_data['site_data']     = array();
		$this->wpd_data['customer_data'] = array();

		$current_id = dollie()->get_current_post_id();

		$this->container = dollie()->get_container( $current_id );

		if ( is_wp_error( $this->container ) ) {
			return;
		}

		$details = $this->container->get_details();

		if ( is_wp_error( $details ) ) {
			return;
		}

		if ( isset( $details['site'] ) ) {
			$this->wpd_data['site_data'] = $details['site'];
		}

		$access = dollie()->access();
		$user   = dollie()->get_user();

		// Add custom items
		$this->wpd_data['customer_data']['Customer - Total Sites Launched']     = $user->count_containers();
		$this->wpd_data['customer_data']['Customer Access - Sites Available']   = $access->sites_available();
		$this->wpd_data['customer_data']['Customer Access - Storage Available'] = $access->storage_available();
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
		return array( \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY );
	}

	protected function register_controls() {
		$keys = array();

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
			array(
				'label'   => __( 'Choose Data', 'dollie' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $keys,
			)
		);
	}

	public function render() {
		$param_name = $this->get_settings( 'param_name' );

		if ( ! $param_name ) {
			echo '';
		}

		$data = $this->wpd_data['site_data'];

		if ( ! isset( $data[ $param_name ] ) ) {
			$data = $this->wpd_data['customer_data'];
		}

		if ( ! isset( $data[ $param_name ] ) ) {
			echo '';
			return;
		}

		$value = $data[ $param_name ];

		echo wp_kses_post( $value );
	}
}
