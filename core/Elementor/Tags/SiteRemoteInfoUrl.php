<?php

namespace Dollie\Core\Elementor\Tags;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;


class SiteRemoteInfoUrl extends Tag
{

	private $wpd_data = [];

	public function __construct(array $data = [])
	{
		parent::__construct($data);

		$current_id = dollie()->get_current_site_id();

		//Get Items from Feed
		$this->wpd_data = \Dollie\Core\Modules\Container::instance()->get_container_details($current_id);
		$this->wpd_data['customer_data']["Support Link"] = dollie()->get_support_link();
		$this->wpd_data['site_data']["URL"] = dollie()->get_container_url($current_id);

	}

	public function get_name()
	{

		return 'dollie-site-info-url';
	}

	public function get_title()
	{
		return __('Dollie Site Remote Info URL', 'dynamic-tags');
	}


	public function get_group()
	{
		return 'dollie-tags';
	}

	public function get_categories()
	{
		return [\Elementor\Modules\DynamicTags\Module::URL_CATEGORY];
	}

	protected function _register_controls()
	{


		$keys = [];
		foreach ($this->wpd_data['site_data'] as $k => $data) {

			if (is_array($data) || $data === false) {
				continue;
			}

			if (
				! filter_var($data, FILTER_VALIDATE_URL)
			) {
				continue;
			}

			$keys[$k] = 'Site - ' . $k;
		}

		foreach ($this->wpd_data['customer_data'] as $k => $data) {

			if (is_array($data) || $data === false) {
				continue;
			}

			$keys[$k] = $k;
		}

		//Our Basic WP Feed

		// $extra_tags = array(
		// 	"New Data" => 'value',
		// );

		//$keys = array_merge($keys, $extra_tags);


		$this->add_control(
			'param_name',
			[
				'label'   => __('Choose Data', 'elementor-pro'),
				'type'    => Controls_Manager::SELECT,
				'options' => $keys,
			]
		);
	}

	public function render()
	{

		$param_name = $this->get_settings('param_name');

		if (!$param_name) {
			return '';
		}

		$data = $this->wpd_data['site_data'];

		if (!isset($data[$param_name])) {
			return '';
		}

		$value = $data[$param_name];

		echo wp_kses_post($value);
	}
}
