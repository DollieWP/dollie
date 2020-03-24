<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;

/**
 * Class AccessControl
 * @package Dollie\Core\Modules
 */
class AccessControl extends Singleton {

	/**
	 * AccessControl constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'template_redirect', [ $this, 'logged_in_only' ] );
		add_action( 'template_redirect', [ $this, 'protect_launch_site' ] );
		add_action( 'template_redirect', [ $this, 'protect_container_access' ], 1 );

		add_filter( 'wp_dropdown_users_args', [ $this, 'allow_all_authors' ], 10, 2 );
		add_action( 'admin_init', [ $this, 'no_admin_access' ], 100 );

		// TODO Maybe remove it completly
		// add_action( 'admin_init', [ $this, 'add_hidden_fields' ] );

		add_action( 'wp', [ $this, 'block_access' ] );

		if ( ! defined( 'DOLLIE_DEV' ) || ! DOLLIE_DEV ) {
			add_action( 'admin_init', [ $this, 'restrict_gravity_form_edit' ] );
		}

	}

	public function get_available_sections() {
		$available_sections_array = get_field( 'available_sections', 'option' );

		if ( get_field( 'wpd_enable_blueprints_for', 'option' ) === 'all' && ! current_user_can( 'manage_options' ) ) {
			$available_sections_array = dollie()->removeElementWithValue( $available_sections_array, 'value', 'blueprint' );
		}

		return $available_sections_array;
	}

	public function logged_in_only() {
		if ( ! is_user_logged_in() && ( is_singular( 'container' ) || is_page( 'dashboard' ) ) ) {
			wp_redirect( get_permalink( dollie()->get_login_page_id() ) );
			exit;
		}
	}

	public function protect_launch_site() {
		if ( ! is_user_logged_in() && is_page( dollie()->get_launch_page_id() ) ) {
			wp_redirect( get_permalink( dollie()->get_dashboard_page_id() ) );
			exit();
		}
	}

	// Todo - Demo functionality can be removed
	public function protect_container_access() {
		if ( ! current_user_can( 'manage_options' ) ) {

			if ( is_post_type_archive( 'container' ) ) {
				wp_redirect( get_site_url() . '/' );
				exit();
			}

			global $post, $current_user;

			// Is site owner?
			if ( $post->post_author !== $current_user->ID && is_singular( 'container' ) ) {
				wp_redirect( get_site_url() . '/' );
				exit();
			}

			// Has access to the specific section?
			if ( isset( $_GET['page'] ) && is_singular( 'container' ) && ! dollie()->in_array_r( $_GET['page'], $this->get_available_sections() ) ) {
				wp_redirect( get_permalink() );
				exit();
			}
		}
	}

	public function allow_all_authors( $query_args, $r ) {
		$query_args['who'] = '';

		return $query_args;
	}

	public function no_admin_access() {
		$redirect = home_url( '/dashboard' );
		if ( ! current_user_can( 'manage_options' ) && ! wp_doing_ajax() ) {
			wp_redirect( $redirect );
			exit();
		}
	}

	public function restrict_gravity_form_edit() {

		$dollie_form_ids = dollie()->get_dollie_gravity_form_ids();

		$restrictions = [];
		foreach ( $dollie_form_ids as $id ) {
			$restrictions[] = [ 'page' => 'gf_edit_forms', 'id' => $id ];
			$restrictions[] = [ 'page' => 'gf_edit_forms', 'id' => $id, 'view' => 'settings' ];
			$restrictions[] = [
				'page'    => 'gf_edit_forms',
				'id'      => $id,
				'view'    => 'settings',
				'subview' => 'settings'
			];
			$restrictions[] = [
				'page'    => 'gf_edit_forms',
				'id'      => $id,
				'view'    => 'settings',
				'subview' => 'confirmation'
			];
			$restrictions[] = [
				'page'    => 'gf_edit_forms',
				'id'      => $id,
				'view'    => 'settings',
				'subview' => 'gravityformshelpscout'
			];
		}

		parse_str( $_SERVER['QUERY_STRING'], $query_string );

		foreach ( $restrictions as $restriction ) {
			if ( $query_string === $restriction ) {
				wp_die( '<h1>This Dollie form can not be edited.</h1><p>This Gravity Form controls crucial Dollie functionality and can not be edited directly. If you need to make changes or are looking for a custom solution, please reach out to our Team through our Partner Slack so we can assist you.', 'This Dollie form can not be edited.</p>' );
			}
		}
	}

	public function add_hidden_fields() {
		if ( ! class_exists( \GFAPI::class ) ) {
			return;
		}

		$hidden_fields = [
			'14' => 'dollie-launch',
			'5'  => 'dollie-support',
			'17' => 'dollie-updates',
			'11' => 'dollie-list-backups',
			'12' => 'dollie-create-backup',
			'13' => 'dollie-blueprint',
			'6'  => 'dollie-wizard',
			'10' => 'dollie-domain',
		];
		/* TODO MAP existing form IDS from Panel */
		$hidden_fields = apply_filters( 'dollie_gravity_forms_hidden_fields', $hidden_fields );

		foreach ( $hidden_fields as $form_id => $field_label ) {
			// Get the form object.
			$form = \GFAPI::get_form( $form_id );

			if ( ! $form ) {
				continue;
			}

			// Get next available field ID.
			$new_field_id = 0;
			$field_exists = 0;
			foreach ( $form['fields'] as $field ) {
				if ( $field->id > $new_field_id ) {
					$new_field_id = $field->id;
				}

				if ( $field->label == $field_label ) {
					$field_exists = 1;
				}
			}
			$new_field_id ++;

			// Check if the field exist already then don't add again.
			if ( $field_exists ) {
				continue;
			}

			// Create new field object.
			$properties['type']  = 'hidden';
			$properties['id']    = $new_field_id;
			$properties['label'] = $field_label;
			$field               = \GF_Fields::create( $properties );

			// Add the new field to the form and update form.
			$form['fields'][] = $field;
			\GFAPI::update_form( $form );
		}
	}

	public function block_access() {
		if ( function_exists( 'bp_is_active' ) && ! current_user_can( 'edit_published_posts' ) ) {
			if ( bp_is_members_directory() || bp_is_groups_directory() || bp_is_current_action( 'members' ) ) {
				wp_redirect( get_site_url() );
				exit();
			}
		}
	}

}
