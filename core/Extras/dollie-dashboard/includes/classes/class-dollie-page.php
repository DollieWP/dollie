<?php

/**
 * The Dollie Dashboard Page Base class
 *
 * @package Dollie Dashboard
 * @subpackage Pages
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Dollie_DBPage' ) ) :
/**
 * The dollie Licenses class
 *
 * @since 1.0.0
 */
abstract class Dollie_DBPage {

	/**
	 * Page constructor
	 *
	 * @since 1.0.0
	 *
	 * @uses Dollie_DBPage::setup_actions()
	 */
	public function __construct( $args = array() ) {

		// Merge with defaults
		$this->args = wp_parse_args( $args, array(
			'title'       => '',
			'slug'        => '',
			'parent'      => 'dollie-dashboard',
			'capability'  => 'manage_options',
			'labels'      => array(),
			'priority'    => 10,
			'icon'        => '',
			'iframe_src'  => '',
			'callback'    => '',
			'acf'         => false,
			'_builtin'    => false
		) );

		// When init was already fired, add the page
		if ( did_action( 'Dollie_DBinit' ) ) {
			$this->add_page();
		} else {
			add_action( 'Dollie_DBinit', array( $this, 'add_page' ) );
		}

		$this->includes();
		$this->setup_actions();
	}

	/**
	 * Magic getter for returning page args
	 *
	 * @since 1.0.0
	 */
	public function __get( $key ) {
		if ( in_array( $key, array_keys( $this->args ) ) ) {
			return $this->args[ $key ];
		} else {
			return $this->{$key};
		}
	}

	/**
	 * Include required files
	 *
	 * @since 1.0.0
	 */
	protected function includes() {
		/* Overwrite this method in your sub class */
	}

	/**
	 * Register page actions
	 *
	 * @since 1.0.0
	 */
	protected function setup_actions() {

		add_action( 'admin_enqueue_scripts', array( $this, '_enqueue_scripts' ) );

		// For ACF pages
		if ( $this->acf ) {
			add_action( 'Dollie_DBinit', array( $this, 'register_fields' ), 20 );
		}
	}

	/**
	 * Add this page to the collection
	 *
	 * @since 1.0.0
	 *
	 * @uses Dollie_DBPages::add_page()
	 */
	public function add_page() {

		// Get Pages object
		$pages = dollie()->pages;

		// Register page
		$pages->add_page( $this->args );

		// Get the new page details
		$page = $pages->get_pages( array( 'slug' => $this->slug ) );
		if ( $page ) {
			$this->args = (array) $page[ $this->slug ];
		}
	}

	/**
	 * Return whether the current page is this page
	 *
	 * @since 1.0.0
	 *
	 * @uses Dollie_DBPages::is_dashboard_page()
	 * @return bool This is this page
	 */
	public function is_this_page() {
		return dollie()->pages->is_dashboard_page( $this->slug );
	}

	/**
	 * Return the admin url of this page
	 *
	 * @since 1.0.0
	 *
	 * @uses Dollie_DBPages::get_page_url()
	 * @return string Admin page url
	 */
	public function get_url() {
		return dollie()->pages->get_page_url( $this->slug );
	}

	/**
	 * Enqueue scripts on this page
	 *
	 * @since 1.0.0
	 *
	 * @uses self::enqueue_scripts()
	 */
	public function _enqueue_scripts() {

		// Bail if this is not this page
		if ( ! $this->is_this_page() )
			return;

		// Call script method when present
		if ( is_callable( array( $this, 'enqueue_scripts' ) ) ) {
			$this->enqueue_scripts();
		}
	}

	/** ACF *************************************************************/

	/**
	 * Register ACF fields
	 *
	 * @since 1.0.0
	 */
	public function register_fields() {
		/* Overwrite this method in a sub class for ACF pages */
	}

	/**
	 * Shortcut for registering an ACF local field group on this page
	 *
	 * @since 1.0.0
	 *
	 * @uses acf_add_local_field_group()
	 * @param array $args ACF field group arguments
	 */
	public function acf_add_local_field_group( $args = array() ) {
		acf_add_local_field_group( wp_parse_args( $args, array(

			// Provide location for $this page
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => $this->slug,
					),
				),
			)
		) ) );
	}
}

endif; // class_exists
