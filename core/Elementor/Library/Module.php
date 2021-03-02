<?php

namespace Dollie\Core\Elementor\Library;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Elementor\Library\Documents\Dollie;
use Dollie\Core\Utils\Helpers;

use Elementor\Core\Base\Module as BaseModule;
use Elementor\Plugin;

/**
 * Class Module
 *
 * @package Dollie\Core\Extras\Library
 */
class Module extends BaseModule {

	/**
	 * Module constructor.
	 */
	public function __construct() {
		Plugin::$instance->documents
			->register_document_type( 'dollie-templates', Dollie::get_class_full_name() );

		add_filter( 'manage_elementor_library_posts_columns', [ $this, 'add_column_head' ], 99 );

		if ( ! class_exists( 'ElementorPro\Plugin' ) ) {
			add_action( 'manage_elementor_library_posts_custom_column', [ $this, 'add_column_content' ], 10, 2 );
		}

		if ( ! shortcode_exists( 'elementor-template' ) ) {
			add_shortcode( 'elementor-template', [ $this, 'register_shortcode' ] );
		}

		add_action( 'elementor/template-library/create_new_dialog_fields', [ $this, 'template_options' ] );

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_script' ] );
	}

	/**
	 * Get module name
	 *
	 * @return string
	 */
	public function get_name() {
		return 'library';
	}

	/**
	 * Enqueue scripts
	 *
	 * @param $hook
	 */
	public function enqueue_admin_script( $hook ) {
		if ( 'edit.php' !== $hook || ! isset( $_GET['post_type'] ) || sanitize_text_field( $_GET['post_type'] ) !== 'elementor_library' ) {
			return;
		}

		wp_enqueue_script( 'dollie-admin-library', DOLLIE_ASSETS_URL . 'js/admin-library.js', [], DOLLIE_VERSION );
	}

	/**
	 * Add shortcode column head
	 *
	 * @param $defaults
	 *
	 * @return mixed
	 */
	public function add_column_head( $defaults ) {
		if ( isset( $defaults['shortcode'] ) ) {
			return $defaults;
		}

		$defaults['shortcode'] = __( 'Shortcode', 'dollie' );

		return $defaults;
	}

	/**
	 * Add shortcode column content
	 *
	 * @param $column_name
	 * @param $post_ID
	 */
	public function add_column_content( $column_name, $post_ID ) {
		if ( 'shortcode' !== $column_name ) {
			return;
		}

		echo '<input class="elementor-shortcode-input" style="width: 100%;" type="text" readonly="" onfocus="this.select()" value="[elementor-template id=&quot;' . $post_ID . '&quot;]">';
	}

	/**
	 * Register shortcode
	 *
	 * @param $atts
	 *
	 * @return string
	 */
	public function register_shortcode( $atts ) {
		if ( empty( $atts['id'] ) ) {
			return '';
		}

		$content = \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $atts['id'] );

		return $content;
	}

	/**
	 * Template options
	 */
	public function template_options() {
		?>
		<div id="elementor-new-template__form__template-dol__wrapper" class="elementor-form-field">
			<label for="elementor-new-template__form__template-dol"
				   class="elementor-form-field__label"><?php echo esc_html__( 'Select template type', 'dollie' ); ?></label>
			<div class="elementor-form-field__select__wrapper">
				<select id="elementor-new-template__form__template-dol" class="elementor-form-field__select"
						name="<?php echo Dollie::REMOTE_CATEGORY_META_KEY; ?>">
					<option value=""><?php echo __( 'Select', 'elementor' ); ?>...</option>
					<?php
					foreach ( Helpers::instance()->get_elementor_template_types() as $type => $title ) {
						$selected = ( 'container' === $type ) ? ' selected="selected"' : '';
						printf( '<option%1$s value="%2$s">%3$s</option>', $selected, $type, $title );
					}
					?>
				</select>
			</div>
		</div>
		<?php
	}

}
