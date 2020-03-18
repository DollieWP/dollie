<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

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

		add_filter( 'acf/load_field', [ $this, 'localize_strings' ] );

		add_action( 'af/register_forms', [ $this, 'register_forms' ] );

		//add_filter( 'af/merge_tags/resolve', 'add_hello_merge_tag', 10, 2 );
		//add_filter( 'af/merge_tags/custom', 'register_hello_merge_tag', 10, 2 );
		/*
		 function add_hello_merge_tag( $output, $tag ) {
			  if ( 'hello' != $tag ) {
			    return $output;
			  }

			  return "Hello " . af_get_field( 'first_name' );
			}

		function register_hello_merge_tag( $tags, $form ) {
		  $tags[] = array(
		    'value' => 'hello',
		    'label' => "Hello!",
		  );

		  return $tags;
		}
		 */


	}

	public function register_forms() {

		af_register_form( array(
			'key'            => 'form_dollie_launch_site',
			'title'          => 'Launch Site',
			'display'        => array(
				'description'     => 'Desc',
				'success_message' => '<p>test</p>',
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

		// var_dump($field);
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

}
