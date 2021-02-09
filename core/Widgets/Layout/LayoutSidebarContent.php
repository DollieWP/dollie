<?php

namespace Dollie\Core\Widgets\Layout;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Utils\Tpl;

use Elementor\Controls_Manager;

/**
 * Class LayoutSidebarContent
 *
 * @package Dollie\Core\Widgets\Dashboard
 */
class LayoutSidebarContent extends \Elementor\Widget_Base {

	private static $section_templates = null;

	public function __construct($data = [], $args = null)
	{
		parent::__construct($data, $args);

		wp_register_script(
			'dollie-layout-alpine',
			'https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js',
			[],
			DOLLIE_VERSION,
			true
		);
	}

	public function get_name() {
		return 'dollie-layout-sidebar-content';
	}

	public function get_title() {
		return esc_html__( 'Layout - Sidebar / Content', 'dollie' );
	}

	public function get_icon() {
		return 'eicon-inner-section';
	}

	public function get_script_depends()
	{
		return ['dollie-layout-alpine'];
	}

	public function get_categories() {
		return [ 'dollie-category' ];
	}

	protected function _register_controls() {

		$this->start_controls_section(
			'header_section',
			[
				'label' => __('Header Area', 'dollie'),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'header',
			[
				'label'   => __('Header template', 'dollie'),
				'type' => Controls_Manager::SELECT2,
				'options' => $this::get_saved_data('section'),
				'default' => 'Select',
			]
		);


		$this->end_controls_section();


		$this->start_controls_section(
			'sidebar_section',
			[
				'label' => __('Sidebar Area', 'dollie'),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'sidebar',
			[
				'label'   => __('Sidebar template', 'dollie'),
				'type' => Controls_Manager::SELECT2,
				'options' => $this::get_saved_data('section'),
				'default' => 'Select',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'content_section',
			[
				'label' => __('Content Area', 'dollie'),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'content',
			[
				'label'   => __('Content Template', 'dollie'),
				'type' => Controls_Manager::SELECT2,
				'options' => $this::get_saved_data('section'),
				'default' => 'Select',
			]
		);

		$this->end_controls_section();


	}

	private static function get_saved_data($type = 'page')
    {

        $template_type = $type . '_templates';

        $templates_list = array();

            $posts = get_posts(
                array(
                    'post_type' => 'elementor_library',
                    'orderby' => 'title',
                    'order' => 'ASC',
                    'posts_per_page' => '-1',
                )
            );

            foreach ($posts as $post) {

                $templates_list[] = array(
                    'id' => $post->ID,
                    'name' => $post->post_title,
                );
            }

            self::${$template_type}[-1] = __('Select', 'dollie');

            if (count($templates_list)) {
                foreach ($templates_list as $saved_row) {

                    $content_id = $saved_row['id'];
                    $content_id = apply_filters('wpml_object_id', $content_id);
                    self::${$template_type}[$content_id] = $saved_row['name'];

                }
            } else {
                self::${$template_type}['no_template'] = __('Sorry, No Elementor templates have been found.', 'dollie');
            }
        return self::${$template_type};
	}

	protected function render() {
		$data = [
			'settings' => $this->get_settings_for_display(),
		];

		Tpl::load( 'widgets/layout/sidebar-content-layout', $data, true );
	}

}
