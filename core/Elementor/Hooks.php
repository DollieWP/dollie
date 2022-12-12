<?php

namespace Dollie\Core\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Elementor\Library\Module;
use Dollie\Core\Singleton;
use Dollie\Core\Widgets\Widgets;
use Elementor\Core\Base\Document;
use Elementor\Plugin;

/**
 * Class ElementorHooks
 *
 * @package Dollie\Core
 */
class Hooks extends Singleton {
	/**
	 * Hooks constructor.
	 */
	public function __construct() {
		parent::__construct();

		// extra fallback
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return;
		}

		require_once DOLLIE_PATH . 'core/Elementor/Library/Documents/LibraryDocument.php';
		require_once DOLLIE_PATH . 'core/Elementor/Library/Documents/Dollie.php';
		require_once DOLLIE_PATH . 'core/Elementor/Library/Module.php';

		add_action( 'elementor/init', [ $this, 'init' ], 0 );

		if ( is_admin() ) {
			add_action(
				'elementor/admin/after_create_settings/' . \Elementor\Settings::PAGE_ID,
				[
					$this,
					'register_admin_fields',
				],
				20
			);
		}

		add_action( 'elementor/elements/categories_registered', [ $this, 'register_category' ] );
		add_action( 'elementor/widgets/widgets_registered', [ $this, 'widgets_registered' ] );

		add_action(
			'elementor/dynamic_tags/register_tags',
			function() {

				/** @var \Elementor\Core\DynamicTags\Manager $module */
				$module = \Elementor\Plugin::$instance->dynamic_tags;

				$module->register_group(
					'dollie-tags',
					[
						'title' => 'Dollie',
					]
				);

				// Finally register the tag
				$module->register_tag( '\Dollie\Core\\Elementor\\Tags\\SiteRemoteInfo' );
				$module->register_tag( '\Dollie\Core\\Elementor\\Tags\\SiteRemoteInfoUrl' );
				$module->register_tag( '\Dollie\Core\\Elementor\\Tags\\SiteImageRemoteInfo' );
				$module->register_tag( '\Dollie\Core\\Elementor\\Tags\\SiteScreenshot' );
				$module->register_tag( '\Dollie\Core\\Elementor\\Tags\\SiteBackups' );
			}
		);
	}

	/**
	 * Adds actions after Elementor init.
	 */
	public function init() {
		// Register modules.
		new Module();

		// Register Dollie source.
		Plugin::instance()->templates_manager->register_source( __NAMESPACE__ . '\DollieSource' );
	}

	/**
	 * Add Dollie tab to Elementor settings page
	 *
	 * @param $settings
	 */
	public function register_admin_fields( $settings ) {
		$settings->add_tab(
			'dollie-templates',
			[
				'label' => esc_html__( 'Dollie', 'dollie' ),
			]
		);
	}

	/**
	 * Register custom elementor category
	 *
	 * @param $elements_manager
	 */
	public function register_category( $elements_manager ) {
		$elements_manager->add_category(
			'dollie-category',
			[
				'title' => __( 'Dollie', 'dollie' ),
				'icon'  => 'fa fa-plug',
			]
		);
	}

	/**
	 * Register elementor widgets
	 */
	public function widgets_registered() {
		$elementor = \Elementor\Plugin::instance();

		if ( isset( $elementor->widgets_manager ) && method_exists( $elementor->widgets_manager, 'register_widget_type' ) ) {
			foreach ( Widgets::instance()->get() as $widget ) {
				if ( $template_file = $this->get_element_path( $widget['path'] ) ) {
					require_once $template_file;
					$elementor->widgets_manager->register( new $widget['class']() );
				}
			}
		}
	}

	/**
	 * Get widget template path
	 *
	 * @param $file_path
	 *
	 * @return bool|string
	 */
	public function get_element_path( $file_path ) {
		$template_file = $file_path . '.php';
		if ( $template_file && is_readable( $template_file ) ) {
			return $template_file;
		}

		return false;
	}

	/**
	 * If we are viewing a dollie template in front-end
	 *
	 * @return bool
	 */
	public function is_front_library() {
		if ( ! isset( $_GET['elementor_library'] ) ) {
			return false;
		}

		$tpl_id = (int) get_the_ID();

		if ( 0 === $tpl_id ) {
			$slug = sanitize_text_field( $_GET['elementor_library'] );
			$tpl  = get_page_by_path( $slug, OBJECT, 'elementor_library' );
			if ( $tpl ) {
				$tpl_id = $tpl->ID;
			}
		}

		return $this->is_doc_type( $tpl_id );
	}

	/**
	 * Check if we are on preview iframe in edit mode and a dollie template
	 *
	 * @return bool
	 */
	public function is_edit_frame() {
		if ( ! isset( $_GET['elementor-preview'] ) ) {
			return false;
		}

		$post_id = (int) $_GET['elementor-preview'];

		if ( 0 === $post_id || ! $this->is_doc_type( $post_id ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if the post is a dollie document type
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	public function is_doc_type( $id = 0 ) {
		if ( ! $id ) {
			return false;
		}

		return get_post_meta( $id, Document::TYPE_META_KEY, true ) === 'dollie-templates';
	}

}
