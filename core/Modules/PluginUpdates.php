<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Helpers;
use Dollie\Core\Log;

/**
 * Class PluginUpdates
 * @package Dollie\Core\Modules
 */
class PluginUpdates extends Singleton {

	/**
	 * @var mixed
	 */
	private $helpers;

	/**
	 * PluginUpdates constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->helpers = Helpers::instance();

		$update_forms = wpd_get_dollie_gravity_form_ids( 'dollie-updates' );
		foreach ( $update_forms as &$form_id ) {
			add_filter( 'gform_pre_render_' . $form_id, 'wpd_list_plugin_updates' );
			add_action( 'gform_after_submission_' . $form_id, 'wpd_update_plugins', 10, 2 );
		}
	}

	public function wfc_get_all_plugin_updates() {
		if ( ob_get_length() > 0 ) {
			@ob_end_flush();
			@flush();
		}
		global $wp_query;
		$post_id   = $wp_query->get_queried_object_id();
		$post_slug = get_queried_object()->post_name;
		//Grab the customer installation.

		//Only run the job on the container of the customer.
		$post_body = '
  {
    "filter":"name: https://' . $post_slug . DOLLIE_DOMAIN . '-' . DOLLIE_RUNDECK_KEY . '"
  }
  ';

		//Set up the request
		$update = wp_remote_post( DOLLIE_RUNDECK_URL . '/api/1/job/0a74013f-1180-45bf-aacd-42455dc5c338/run/', array(
			'headers' => array(
				'X-Rundeck-Auth-Token' => DOLLIE_RUNDECK_TOKEN,
				'Content-Type'         => 'application/json',
			),
			'body'    => $post_body,
		) );
		//Parse the JSON request
		$answer = wp_remote_retrieve_body( $update );
		$xml    = simplexml_load_string( $answer, "SimpleXMLElement", LIBXML_NOCDATA );
		$json   = json_encode( $xml );
		$array  = json_decode( $json, true );

		//print "<pre>";
		$execution_id = $array["executions"]["execution"]["@attributes"]["id"];
//print_r($array);
		//print "</pre>";

		sleep( 6 );

		//Set up the request
		$update = wp_remote_post( DOLLIE_RUNDECK_URL . '/api/5/execution/' . $execution_id . '/output?format=text', array(
			'headers' => array(
				'X-Rundeck-Auth-Token' => DOLLIE_RUNDECK_TOKEN,
				'Content-Type'         => 'application/json',
			),
		) );

		//Parse the JSON request
		return wp_remote_retrieve_body( $update ); ?>
		<?php
	}

	public function wpd_list_plugin_updates( $form ) {
		foreach ( $form['fields'] as &$field ) {
			if ( $field['type'] != 'checkbox' || strpos( $field['cssClass'], 'plugin-updates' ) === false ) {
				continue;
			}

			// Grab our array of available backups
			$plugins = json_decode( wfc_get_all_plugin_updates(), true );
			//print("<pre>".print_r($plugins,true)."</pre>");
			?>

			<?php
			$needs_upgrade = false;
			foreach ( $plugins as $plugin ) {
				if ( $plugin['update'] == 'available' ) {
					$needs_upgrade = true;
				}
			}

			if ( $needs_upgrade == false ) {
				$choices = '';
				?>
                <div id="no-backups-created" class="blockquote-box blockquote-info clearfix">
                    <div class="square pull-left">
                        <i class="fal fa-check-circle"></i>
                    </div>
                    <h4 class="padding-bottom-none margin-top-none">
                        Everything is up to date!
                    </h4>
                    <p>
                        There are no plugins available to update. Good job!
                    </p>
                </div>
			<?php } else {

				foreach ( $plugins as $plugin ) {

					if ( $plugin['status'] == 'must-use' || $plugin['update'] == 'none' ) {
						continue;
					}
					$choices[] = array( 'text' => $plugin['title'] . $plugin['version'], 'value' => $plugin['name'] );
				}
			}

			$field['choices'] = $choices;
		}

		return $form;
	}

	public function wpd_update_plugins( $entry, $form ) {
		global $wp_query;
		$post_id   = $wp_query->get_queried_object_id();
		$post_slug = get_queried_object()->post_name;
		$install   = $post_slug . DOLLIE_DOMAIN;

		//Our form field ID + User meta fields
		$plugins = rgar( $entry, '5' );

		$field_id = 5; // Update this number to your field id number
		$field    = RGFormsModel::get_field( $form, $field_id );
		$value    = is_object( $field ) ? $field->get_value_export( $entry ) : '';
		//echo $value;
		$update_plugins = str_replace( ",", " ", $value );
		//var_dump($update_plugins);

		ob_start(); ?>
        {
        "filter":"name: 'https://<?php echo $install . '-' . DOLLIE_RUNDECK_KEY; ?>'",
        "argString":"-plugins '<?php echo $update_plugins; ?>'"
        }<?php
		//Create our new node details
		$post_body = ob_get_clean();

		//Set up the request
		$update = wp_remote_post( DOLLIE_RUNDECK_URL . '/api/1/job/7976ab1f-23d7-460b-aa24-6222ce17c2f9/run/', array(
			'headers' => array(
				'X-Rundeck-Auth-Token' => DOLLIE_RUNDECK_TOKEN,
				'Content-Type'         => 'application/json',
			),
			'body'    => $post_body,
		) );

		//Parse the JSON request
		$answer = wp_remote_retrieve_body( $update );

		$xml   = simplexml_load_string( $answer, "SimpleXMLElement", LIBXML_NOCDATA );
		$json  = json_encode( $xml );
		$array = json_decode( $json, true );

		//print "<pre>";
		$execution_id = $array["executions"]["execution"]["@attributes"]["id"];
		echo '<div class="alert alert-info mt-30">';

		sleep( 4 );

		//Set up the request
		$plugin_update = wp_remote_post( DOLLIE_RUNDECK_URL . '/api/5/execution/' . $execution_id . '/output?format=text', array(
			'headers' => array(
				'X-Rundeck-Auth-Token' => DOLLIE_RUNDECK_TOKEN,
				'Content-Type'         => 'application/json',
			)
		) );
		$headers       = wp_remote_retrieve_headers( $plugin_update );

		if ( $headers['x-rundeck-execoutput-completed'] == 'false' ) {
			echo 'Plugin update is still running... please hold on<br>';

			sleep( 4 );

			//Set up the request
			$new_plugin_update = wp_remote_post( DOLLIE_RUNDECK_URL . '/api/5/execution/' . $execution_id . '/output?format=text', array(
				'headers' => array(
					'X-Rundeck-Auth-Token' => DOLLIE_RUNDECK_TOKEN,
					'Content-Type'         => 'application/json',
				)
			) );
			//Parse the JSON request
			$newheaders = wp_remote_retrieve_headers( $new_plugin_update );

			if ( $newheaders['x-rundeck-execoutput-completed'] == 'true' ) {
				echo 'Plugin updates completed.<br>';
				echo '<pre>' . wp_remote_retrieve_body( $new_plugin_update ) . '<pre>';
			} else {
				sleep( 4 );
				//Set up the request
				$final_plugin_update = wp_remote_post( DOLLIE_RUNDECK_URL . '/api/5/execution/' . $execution_id . '/output?format=text', array(
					'headers' => array(
						'X-Rundeck-Auth-Token' => DOLLIE_RUNDECK_TOKEN,
						'Content-Type'         => 'application/json',
					)
				) );
				//Parse the JSON request
				$finalheaders = wp_remote_retrieve_headers( $final_plugin_update );
				if ( $finalheaders['x-rundeck-execoutput-completed'] == 'false' ) {
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

}
