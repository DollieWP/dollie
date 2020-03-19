<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Modules\Forms\AfterLaunchWizard;
use Dollie\Core\Modules\Forms\ListBackups;
use Dollie\Core\Singleton;

/**
 * Class Forms
 * @package Dollie\Core\Modules
 */
class Forms extends Singleton {

	/**
	 * Forms constructor.
	 */
	public function __construct() {
		parent::__construct();

		AfterLaunchWizard::instance();
		ListBackups::instance();

		add_action( 'init', [ $this, 'init' ] );
		add_action( 'acf/init', [ $this, 'acf_init' ] );

		add_filter( 'acf/load_field', [ $this, 'localize_strings' ] );

		add_filter( 'acf/load_field/name=site_blueprint', [ $this, 'populate_blueprints' ] );


	}

	public function init() {
		add_shortcode( 'dollie_form', [ $this, 'form_shortcode' ] );
	}

	public function form_shortcode( $atts = [] ) {

		return do_shortcode( '[advanced_form form="' . esc_attr( $atts['form'] ) . '"]' );

	}

	public function acf_init() {

		add_filter( 'af/merge_tags/resolve', array( $this, 'resolve_fields_tag' ), 9, 3 );


	}

	/**
	 * Add our custom logic to parse placeholders
	 *
	 * @param $output
	 * @param $tag
	 * @param $fields
	 *
	 * @return false|mixed|string
	 */
	public function resolve_fields_tag( $output, $tag, $fields ) {

		if ( ! preg_match_all( '/field:(.*)/', $tag, $matches ) ) {
			return $output;
		}

		$field_name = $matches[1][0];

		$field = af_get_field_object( $field_name, $fields );

		if ( is_array( $field['value'] ) && $field['return_format'] === 'array'
		     && ( $field['type'] === 'radio' || $field['type'] === 'select' ) ) {

			$value = $field['value']['label'];
			if ( strtotime( $value ) ) {
				$value = date( 'd F y \a\t g:i a', strtotime( $value ) );
			}

			return $value;
		}

		return _af_render_field_include( $field );

	}

	public function register_forms() {

		af_register_form( array(
			'key'            => 'form_dollie_launch_site',
			'title'          => 'Launch Site',
			'display'        => array(
				'description'     => 'Desc',
				'success_message' => '<p>' . esc_html__( 'Success message', 'dollie' ) . '</p>',
			),
			'create_entries' => true,
			'restrictions'   => array(
				'entries'  => false,
				'user'     => false,
				'schedule' => false,
			),
			'emails'         => false,
		) );
	}


	public function localize_strings( $field ) {

		if ( $field['label'] === 'Choose Your URL' ) {
			$field['label'] = esc_html__( 'Choose Your URL', 'dollie' );
		}

		if ( $field['instructions'] === 'Please choose a temporary URL for your site. This will be the place where you can work on your site used until you are ready to go live and connect your own domain.' ) {
			$field['instructions'] = esc_html__( 'Please choose a temporary URL for your site. This will be the place where you can work on your site used until you are ready to go live and connect your own domain.', 'dollie' );
		}

		if ( $field['label'] === 'Admin Email' ) {
			$field['label'] = esc_html__( 'Admin Email', 'dollie' );
		}

		if ( $field['instructions'] === 'This is the email address you use to login to your WordPress admin.' ) {
			$field['instructions'] = esc_html__( 'This is the email address you use to login to your WordPress admin.', 'dollie' );
		}

		if ( $field['label'] === 'Select a Blueprint (optional)' ) {
			$field['label'] = esc_html__( 'Select a Blueprint (optional)', 'dollie' );
		}
		if ( $field['instructions'] === 'Carefully crafted site designs made by our team which you can use as a starting point for your new site.' ) {
			$field['instructions'] = esc_html__( 'Carefully crafted site designs made by our team which you can use as a starting point for your new site.', 'dollie' );
		}

		return $field;
	}

	public function populate_blueprints( $field ) {
		$query = new \WP_Query( [
			'post_type'      => 'container',
			'posts_per_page' => 1000,
			'meta_query'     => [
				'relation' => 'AND',
				[
					'key'   => 'wpd_blueprint_created',
					'value' => 'yes',
				],
				[
					'key'   => 'wpd_is_blueprint',
					'value' => 'yes',
				],
				[
					'key'     => 'wpd_installation_blueprint_title',
					'compare' => 'EXISTS',
				]
			],
			'p'              => isset( $_COOKIE['dollie_blueprint_id'] ) ? $_COOKIE['dollie_blueprint_id'] : '',
		] );

		$choices = [];

		// Build field options array.
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$private = get_field( 'wpd_private_blueprint' );

				if ( $private === 'yes' && ! current_user_can( 'manage_options' ) ) {
					continue;
				}

				if ( get_field( 'wpd_blueprint_image' ) === 'custom' ) {
					$image = get_field( 'wpd_blueprint_custom_image' );
				} elseif ( get_field( 'wpd_blueprint_image' ) === 'theme' ) {
					$image = wpthumb( get_post_meta( get_the_ID(), 'wpd_installation_site_theme_screenshot', true ), 'width=900&crop=0' );
				} else {
					$image = get_post_meta( get_the_ID(), 'wpd_site_screenshot', true );
				}

				$choices[ get_the_ID() ] = '<img data-toggle="tooltip" data-placement="bottom" ' .
				                           'title="' . get_post_meta( get_the_ID(), 'wpd_installation_blueprint_description', true ) . '" ' .
				                           'class="fw-blueprint-screenshot" src=' . $image . '>' .
				                           get_post_meta( get_the_ID(), 'wpd_installation_blueprint_title', true );

			}
		}

		$field['choices'] = $choices;

		// return the field
		return $field;

	}
}
