<?php

namespace Dollie\Core\Modules\Forms;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Modules\Forms;
use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;

/**
 * Class PluginUpdates
 *
 * @package Dollie\Core\Modules\Forms
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

		$container_uri = dollie()->get_wp_site_data( 'uri', $container->id );

		$value = af_get_field( 'plugins_to_update' );
		if ( is_array( $value ) ) {
			$value = implode( ' ', $value );
		}

		$update_plugins = str_replace( ',', ' ', $value );

		$update = Api::post(
			Api::ROUTE_PLUGINS_UPDATES_APPLY,
			[
				'container_uri' => $container_uri,
				'plugins'       => $update_plugins,
			]
		);

		AF()->submission['extra']['is_success'] = API::process_response( $update );
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

		$plugins = dollie()->get_container_plugins();

		$needs_upgrade = false;
		if ( false !== $plugins ) {
			foreach ( $plugins as $plugin ) {
				if ( 'available' === $plugin['update'] ) {
					$needs_upgrade = true;
					break;
				}
			}
		}

		if ( false === $needs_upgrade ) {
			$data = '';
			ob_start();
			?>
			<div class="dol-border dol-border-solid dol-border-primary-100 dol-rounded dol-overflow-hidden dol-my-6">
				<div class="dol-flex dol-items-center dol-bg-green-500">
					<div class="dol-p-4 lg:dol-px-8 lg:dol-py-4 dol-bg-green-600 dol-flex dol-items-center dol-justify-center">
						<?php echo dollie()->get_icon_updates( 'dol-text-white dol-text-2xl' ); ?>
					</div>
					<h4 class="dol-px-4 lg:dol-px-8 lg:dol-py-4 dol-m-0 dol-p-0 dol-text-white dol-text-base md:dol-text-xl">
						<?php esc_html_e( 'Everything is up to date', 'dollie' ); ?>
					</h4>
				</div>
				<div class="dol-px-4 dol-py-2 lg:dol-px-8 lg:dol-py-6 dol-bg-gray-100">
					<div>
						<?php esc_html_e( 'There are no plugins available to update. Good job!', 'dollie' ); ?>
					</div>
				</div>
			</div>
			<?php
			$data .= ob_get_clean();

			return $data;
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
		$plugins = dollie()->get_container_plugins();

		$choices = [];

		if ( ! $plugins ) {
			return $field;
		}

		foreach ( $plugins as $plugin ) {
			if ( 'none' === $plugin['update'] ) {
				continue;
			}

			$choices[ $plugin['name'] ] = $plugin['title'] . ' ' . $plugin['version'];
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
