<?php

namespace Dollie\Core\Modules\Forms;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Log;
use Dollie\Core\Modules\Backups;
use Dollie\Core\Modules\Forms;
use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;

/**
 * Class PluginUpdates
 * @package Dollie\Core\Modules\Forms
 */
class PluginUpdates extends Singleton {

	private $form_key = 'form_dollie_plugin_updates';

	/**
	 * PluginUpdates constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'acf/init', [ $this, 'acf_init' ] );
	}

	public function acf_init() {

		// Restrictions
		add_filter( 'af/form/restriction/key=' . $this->form_key, [ $this, 'restrict_form' ], 10 );

		// Placeholders/Change values
		add_filter( 'acf/prepare_field/name=plugins_to_update', [ $this, 'populate_plugins' ] );

		// Form args
		add_filter( 'af/form/args/key=' . $this->form_key, [ $this, 'change_form_args' ] );

		// Form submission action.
		add_action( 'af/form/before_submission/key=' . $this->form_key, [ $this, 'submission_callback' ], 10, 3 );
	}

	public function submission_callback( $form, $fields, $args ) {

		$container = Forms::get_form_container();

		if ( $container === false ) {
			return;
		}

		$container_uri = get_post_meta( $container->id, 'wpd_container_uri', true );

		$value = af_get_field( 'plugins_to_update' );
		if ( is_array( $value ) ) {
			$value = implode( ' ', $value );
		}

		$update_plugins = str_replace( ',', ' ', $value );

		$update = Api::post( Api::ROUTE_PLUGINS_UPDATES_APPLY, [
			'container_uri' => $container_uri,
			'plugins'       => $update_plugins
		] );

		// Parse the JSON request
		$answer = API::process_response( $update );

		$xml   = simplexml_load_string( $answer, "SimpleXMLElement", LIBXML_NOCDATA );
		$json  = json_encode( $xml );
		$array = json_decode( $json, true );

		$execution_id = $array['executions']['execution']['@attributes']['id'];
		echo '<div class="alert alert-info mt-30">';

		sleep( 4 );

		// Set up the request
		$plugin_update = Api::post( Api::ROUTE_EXECUTE_JOB, [ 'job_id' => $execution_id ] );

		$headers = wp_remote_retrieve_headers( $plugin_update );

		// TODO - Add a looping completion request here.
		if ( $headers['x-rundeck-execoutput-completed'] === 'false' ) {
			echo 'Plugin update is still running... please hold on<br>';

			sleep( 4 );

			// Set up the request
			$new_plugin_update = Api::post( Api::ROUTE_EXECUTE_JOB, [ 'job_id' => $execution_id ] );

			// Parse the JSON request
			$new_headers = wp_remote_retrieve_headers( $new_plugin_update );

			if ( $new_headers['x-rundeck-execoutput-completed'] === 'true' ) {
				echo 'Plugin updates completed.<br>';
				echo '<pre>' . wp_remote_retrieve_body( $new_plugin_update ) . '<pre>';
			} else {
				sleep( 4 );

				// Set up the request
				$final_plugin_update = Api::post( Api::ROUTE_EXECUTE_JOB, [ 'job_id' => $execution_id ] );

				// Parse the JSON request
				$final_headers = wp_remote_retrieve_headers( $final_plugin_update );
				if ( $final_headers['x-rundeck-execoutput-completed'] === 'false' ) {
					echo 'Sadly we can not complete updating your plugins. Please login to your WordPress admin and update your plugins from there.';
				} else {
					echo 'All Your Plugins are updated!<br>';
					echo '<pre>' . wp_remote_retrieve_body( $final_plugin_update ) . '<pre>';
				}
			}
		} else {
			echo 'All Done updating your plugins...<br>';
			echo '<pre>' . wp_remote_retrieve_body( $plugin_update ) . '<pre>';
		}
		?>
        </div>
		<?php

	}


	/**
	 * If no updates, restrict the form and show a message
	 *
	 * @param bool $restriction
	 *
	 * @return bool|string
	 */
	public function restrict_form( $restriction = false ) {

		// Added in case another restriction already applies
		if ( $restriction ) {
			return $restriction;
		}

		// Grab our array of available backups
		$plugins = json_decode( $this->get_all_plugin_updates(), true );

		$needs_upgrade = false;
		if ( $plugins ) {
			foreach ( $plugins as $plugin ) {
				if ( $plugin['update'] === 'available' ) {
					$needs_upgrade = true;
					break;
				}
			}
		}

		if ( $needs_upgrade === false ) {
			$data = '';
			ob_start();
			?>
            <div id="no-backups-created" class="blockquote-box blockquote-info clearfix">
                <div class="square pull-left">
                    <i class="fal fa-check-circle"></i>
                </div>
                <h4 class="padding-bottom-none margin-top-none">
					<?php esc_html_e( 'Everything is up to date!', 'dollie' ); ?>
                </h4>
                <p>
					<?php esc_html_e( 'There are no plugins available to update. Good job!', 'dollie' ); ?>
                </p>
            </div>
			<?php
			$data .= ob_get_clean();

			return $data;
		}

		return $restriction;
	}

	public function populate_plugins( $field ) {

		// Grab our array of available backups
		$plugins = json_decode( $this->get_all_plugin_updates(), true );

		$choices = [];

		if ( ! $plugins ) {
			return $field;
		}

		foreach ( $plugins as $plugin ) {
			if ( $plugin['status'] === 'must-use' || $plugin['update'] === 'none' ) {
				continue;
			}

			$choices[ $plugin['name'] ] = $plugin['title'] . $plugin['version'];
		}

		if ( ! empty( $choices ) ) {
			$field['choices'] = $choices;
		}

		return $field;
	}


	/**
	 * Get available plugin updates for container
	 *
	 * @return mixed|string
	 */
	private function get_all_plugin_updates() {

		$currentQuery  = dollie()->get_current_object();
		$container_uri = get_post_meta( $currentQuery->id, 'wpd_container_uri', true );

		if ( $updates = get_transient( 'dollie_plugin_updates_' . $container_uri ) ) {
			return $updates;
		}

		$update = Api::post( Api::ROUTE_PLUGINS_UPDATES_GET, [ 'container_uri' => $container_uri ] );

		$answer = API::process_response( $update );

		if ( ! $answer ) {
			return false;
		}

		$xml   = simplexml_load_string( $answer, 'SimpleXMLElement', LIBXML_NOCDATA );
		$json  = json_encode( $xml );
		$array = json_decode( $json, true );

		$execution_id = $array['executions']['execution']['@attributes']['id'];

		sleep( 6 );

		// Set up the request
		$update = Api::post( Api::ROUTE_EXECUTE_JOB, [ 'job_id' => $execution_id ] );

		$updates = API::process_response( $update );

		if ( ! $updates ) {
			return false;
		}

		set_transient( 'dollie_plugin_updates_' . $container_uri, $updates, 15 );

		return $updates;
	}


	public function change_form_args( $args ) {
		$args['submit_text'] = esc_html__( 'Update Plugins!', 'dollie' );

		return $args;
	}


}
