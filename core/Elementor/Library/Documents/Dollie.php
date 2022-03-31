<?php

namespace Dollie\Core\Elementor\Library\Documents;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Utils\Helpers;

/**
 * Class Dollie
 *
 * @package Dollie\Core\Extras\Library\Documents
 */
class Dollie extends Library_Document {

	/**
	 * Document sub type meta key.
	 */
	const REMOTE_CATEGORY_META_KEY   = '_dollie_page';
	const SET_AS_ACTIVE_CATEGORY_KEY = '_dollie_set_as_active';

	/**
	 * Get document properties.
	 *
	 * Retrieve the document properties.
	 *
	 * @return array Document properties.
	 * @since 1.0.0
	 * @access public
	 * @static
	 */
	public static function get_properties() {
		$properties = parent::get_properties();

		$properties['admin_tab_group']           = true;
		$properties['library_view']              = 'list';
		$properties['show_in_library']           = true;
		$properties['support_kit']               = true;
		$properties['support_wp_page_templates'] = true;

		return $properties;
	}

	/**
	 * Get document name
	 *
	 * @return string
	 */
	public function get_name() {
		return 'dollie-templates';
	}

	/**
	 * Get document title
	 *
	 * @return string|void
	 */
	public static function get_title() {
		return __( 'Dollie', 'dollie' );
	}

	/**
	 * @since  2.0.6
	 * @access public
	 */
	public function save_template_type() {
		parent::save_template_type();

		// Save template sub type
		if ( ! empty( $_REQUEST[ self::REMOTE_CATEGORY_META_KEY ] )
			 && array_key_exists( $_REQUEST[ self::REMOTE_CATEGORY_META_KEY ], dollie()->get_elementor_template_types() ) ) {

			$sub_type = sanitize_text_field( $_REQUEST[ self::REMOTE_CATEGORY_META_KEY ] );

			$this->update_meta( self::REMOTE_CATEGORY_META_KEY, $sub_type );

		}

		if ( ! $this->get_meta( '_wp_page_template' ) ) {
			$this->update_meta( '_wp_page_template', 'elementor_header_footer' );
		}

		// Save it for export/import
		if ( ! defined( 'DOING_AUTOSAVE' ) && $this->get_meta( self::REMOTE_CATEGORY_META_KEY ) ) {
			$settings = $this->get_settings();
			if ( empty( $settings ) ) {
				$settings = [];
			}

			if ( ! isset( $settings['dollie_type'] ) ) {
				$settings['dollie_type'] = $this->get_meta( self::REMOTE_CATEGORY_META_KEY );
				$this->save_settings( $settings );
			}
		}
	}

	/**
	 * Library config
	 *
	 * @return mixed
	 */
	protected function get_remote_library_config() {
		$config = parent::get_remote_library_config();

		$category  = $this->get_meta( self::REMOTE_CATEGORY_META_KEY );
		$templates = dollie()->get_elementor_template_types();

		if ( $category && isset( $templates[ $category ] ) ) {
			$config['category'] = 'dollie ' . str_replace( '-', ' ', $category );
		}

		return $config;
	}

}
