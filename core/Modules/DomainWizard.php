<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;
use Dollie\Core\Log;
use Dollie\Core\Utils\Tpl;
use GFFormDisplay;
use GFFormsModel;
use RGFormsModel;

/**
 * Class DomainWizard
 * @package Dollie\Core\Modules
 */
class DomainWizard extends Singleton {

	/**
	 * DomainWizard constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'template_redirect', [ $this, 'continue_domain_setup' ] );
		add_filter( 'gform_pre_render', [ $this, 'gform_skip_page' ] );
		add_action( 'gform_admin_pre_render', [ $this, 'gform_add_merge_tags' ] );
		add_filter( 'gform_validation', [ $this, 'gfcf_validation' ] );

		//$this->register_confirmation_fields( $domain_forms, [ 55, 60 ] );
	}

	public function continue_domain_setup() {
		if ( isset( $_GET['page'] ) && ! isset( $_GET['form_page'] ) && $_GET['page'] === 'domain' && is_singular( 'container' ) ) {
			$currentQuery = dollie()->get_current_object();

			$has_domain     = get_post_meta( $currentQuery->id, 'wpd_domains', true );
			$has_cloudflare = get_post_meta( $currentQuery->id, 'wpd_cloudflare_email', true );
			$has_analytics  = get_post_meta( $currentQuery->id, 'wpd_cloudflare_zone_id', true );
			$has_le         = get_post_meta( $currentQuery->id, 'wpd_letsencrypt_enabled', true );

			if ( $has_cloudflare && ! $has_analytics ) {
				wp_redirect( get_site_url() . '/site/' . $currentQuery->slug . '?page=domain&form_page=3' );
				exit;
			}

			if ( $has_domain && $has_le ) {
				wp_redirect( get_site_url() . '/site/' . $currentQuery->slug . '?page=domain&form_page=4' );
				exit;
			}

			if ( $has_domain && ! $has_analytics ) {
				wp_redirect( get_site_url() . '/site/' . $currentQuery->slug . '?page=domain&form_page=2' );
				exit;
			}

			if ( $has_analytics ) {
				wp_redirect( get_site_url() . '/site/' . $currentQuery->slug . '?page=domain&form_page=4' );
				exit;
			}
		}
	}

	public function gform_skip_page( $form ) {
		if ( ! rgpost( "is_submit_{$form['id']}" ) && rgget( 'form_page' ) && is_user_logged_in() ) {
			GFFormDisplay::$submission[ $form['id'] ]['page_number'] = rgget( 'form_page' );
		}

		return $form;
	}


	public function gfcf_validation( $validation_result ) {
		global $gfcf_fields;

		$form          = $validation_result['form'];
		$confirm_error = false;

		if ( ! isset( $gfcf_fields[ $form['id'] ] ) ) {
			return $validation_result;
		}

		foreach ( $gfcf_fields[ $form['id'] ] as $confirm_fields ) {
			$values = [];

			// loop through form fields and gather all field values for current set of confirm fields
			foreach ( $form['fields'] as &$field ) {
				if ( ! in_array( $field['id'], $confirm_fields ) ) {
					continue;
				}

				$values[] = rgpost( "input_{$field['id']}" );
			}
			unset( $field );

			// filter out unique values, if greater than 1, a value was different
			if ( count( array_unique( $values ) ) <= 1 ) {
				continue;
			}

			$confirm_error = true;

			foreach ( $form['fields'] as &$field ) {
				if ( ! in_array( $field['id'], $confirm_fields ) ) {
					continue;
				}

				// fix to remove phone format instruction
				if ( RGFormsModel::get_input_type( $field ) === 'phone' ) {
					$field['phoneFormat'] = '';
				}

				$field['failed_validation']  = true;
				$field['validation_message'] = 'Your domain names do not match.';
			}
			unset( $field );
		}

		$validation_result['form']     = $form;
		$validation_result['is_valid'] = ! $validation_result['is_valid'] ? false : ! $confirm_error;

		return $validation_result;
	}

	public function register_confirmation_fields( $form_id, $fields ) {
		global $gfcf_fields;
		$form_id = $form_id[0];

		if ( ! $gfcf_fields ) {
			$gfcf_fields = [];
		}

		if ( ! isset( $gfcf_fields[ $form_id ] ) ) {
			$gfcf_fields[ $form_id ] = [];
		}

		$gfcf_fields[ $form_id ][] = $fields;
	}

}
