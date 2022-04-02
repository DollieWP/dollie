<?php

namespace Dollie\Core\Forms;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Modules\Forms;
use Dollie\Core\Singleton;

/**
 * Class PluginUpdates
 *
 * @package Dollie\Core\Forms
 */
class PluginUpdates extends Singleton {
	/**
	 * @var string
	 */
	private $form_key = 'form_dollie_plugin_updates';

	/**
	 * PluginUpdates constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'acf/init', [ $this, 'acf_init' ] );
	}

	/**
	 * Init ACF
	 */
	public function acf_init() {
		// Restrictions
		add_filter( 'af/form/restriction/key=' . $this->form_key, [ $this, 'restrict_form' ], 10 );

		// Placeholders/Change values
		add_filter( 'acf/prepare_field/name=plugins_to_update', [ $this, 'populate_plugins' ] );

		// Form args
		add_filter( 'af/form/args/key=' . $this->form_key, [ $this, 'change_form_args' ] );

		// Form submission action.
		add_action( 'af/form/before_submission/key=' . $this->form_key, [ $this, 'submission_callback' ], 10, 3 );
		add_action( 'af/form/success_message/key=' . $this->form_key, [ $this, 'success_message' ], 10, 3 );
	}

	/**
	 * Callback
	 */
	public function submission_callback() {
		$container = Forms::get_form_container();

		if ( false === $container ) {
			return;
		}

		$response = $container->update_plugins( af_get_field( 'plugins_to_update' ) );

		AF()->submission['extra']['is_success'] = is_wp_error( $response );
	}

	/**
	 * Success message
	 *
	 * @param $message
	 * @param $form
	 * @param $args
	 *
	 * @return string
	 */
	public function success_message( $message, $form, $args ) {
		$message .= '<div class="alert alert-info mt-30">';

		$success = AF()->submission['extra']['is_success'];

		if ( $success ) {
			$message .= __( 'Your plugins have been updated successfully.', 'dollie' );
		} else {
			$message .= __( 'Sadly we can not complete updating your plugins. Please login to your WordPress admin and update your plugins from there.', 'dollie' );
		}

		$message .= '</div>';

		return $message;
	}

	/**
	 * If no updates, restrict the form and show a message
	 *
	 * @param bool $restriction
	 *
	 * @return bool|string
	 */
	public function restrict_form( $restriction = false ) {
		if ( $restriction ) {
			return $restriction;
		}

		$container = dollie()->get_container();

		if ( is_wp_error( $container ) ) {
			return $restriction;
		}

		$plugins = array_filter(
			$container->get_plugins(),
			function( $v ) {
				return true === $v['update'];
			}
		);

		if ( empty( $plugins ) ) {
			return dollie()->load_template( 'parts/no-updates-required' );
		}

		return $restriction;
	}

	/**
	 * Populate plugins
	 *
	 * @param $field
	 *
	 * @return mixed
	 */
	public function populate_plugins( $field ) {
		$container = dollie()->get_container();

		if ( is_wp_error( $container ) ) {
			return $field;
		}

		$choices = [];

		$plugins = array_filter(
			$container->get_plugins(),
			function( $v ) {
				return true === $v['update'];
			}
		);

		foreach ( $plugins as $plugin ) {
			$choices[ $plugin['slug'] ] = "{$plugin['name']} v{$plugin['version']}";
		}

		if ( ! empty( $choices ) ) {
			$field['choices'] = $choices;
		}

		return $field;
	}

	/**
	 * Change form args
	 *
	 * @param $args
	 *
	 * @return mixed
	 */
	public function change_form_args( $args ) {
		$args['submit_text'] = __( 'Update Plugins!', 'dollie' );

		return $args;
	}
}
