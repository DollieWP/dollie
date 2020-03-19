<?php

class AF_Pro_EDD_Integration {

	private $edd_name = 'Advanced Forms Pro';
	private $edd_url = 'https://hookturn.io';

  function __construct() {

    // add_action( 'admin_init', array( $this, 'init_plugin_updater' ), 0 );

    add_action( 'acf/save_post', array( $this, 'update_license_status' ), 1, 1 );

    add_filter( 'acf/prepare_field/name=af_license_key', array( $this, 'display_license_info' ), 10, 1 );

    add_filter( 'af/settings_fields', array( $this, 'add_settings_fields' ), 0, 1 );

  }


 	/**
 	 * Configure EDD updater
 	 *
 	 * @since 1.4.0
 	 *
 	 */
  function init_plugin_updater( $post_id ) {

    $license_key = get_field( 'af_license_key', 'options' );

    $this->updater = new AF_EDD_SL_Plugin_Updater( $this->edd_url, AF()->path . 'advanced-forms.php', array(
        'version' => AF()->version,
        'license' => $license_key,
        'item_name' => 'Advanced Forms Pro',
        'author' => 'Fabian Lindfors',
        'beta' => false,
      )
    );

  }


  /**
   * Activate/deactivate license key on save
   *
   * @since 1.4.0
   *
   */
  function update_license_status( $post_id ) {

		if( empty( $_POST['acf'] ) || ! isset( $_POST['acf']['field_af_license_key'] ) ) {
			return;
		}

		$previous_license = trim (get_field( 'af_license_key', 'options' ) );
		$new_license = trim( $_POST['acf']['field_af_license_key'] );
		$previous_status = get_option( 'af_license_status' );

		// Deactivate previous license key
		if ( 'success' == $previous_status && $previous_license != $new_license ) {

			$api_params = array(
				'edd_action' => 'deactivate_license',
				'license'    => $previous_license,
				'item_name'  => urlencode( $this->edd_name ), // the name of our product in EDD
				'url'        => home_url()
			);

			// Call the custom API.
			$response = wp_remote_post( $this->edd_url, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

			// Make sure the request succeeded
			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

				update_option( 'af_license_status', 'deactivation_failed' );

				return;
				
			}

			// Decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			// $license_data->license will be either "deactivated" or "failed"
			if( $license_data->license != 'deactivated' ) {

				update_option( 'af_license_status', 'deactivation_failed' );

				return;

			}

		}


		// Clear status if user cleared license field
		if ( empty( $new_license ) ) {

			update_option( 'af_license_status', false );

			return;

		}


		// Activate new license key
		if ( $previous_license != $new_license && ! empty( $new_license ) ) {

			$status = false;

			// Data to send in our API request
			$api_params = array(
				'edd_action' => 'activate_license',
				'license'    => $new_license,
				'item_name'  => urlencode( $this->edd_name ), // the name of our product in EDD
				'url'        => home_url()
			);

			// Call the custom API.
			$response = wp_remote_post( $this->edd_url, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

				$status = 'failed';

			} else {

				$license_data = json_decode( wp_remote_retrieve_body( $response ) );

				if ( false === $license_data->success ) {

					$status = $license_data->error;

				} else {

					$status = 'success';

				}

			}

			update_option( 'af_license_status', $status );

		}
    
  }


  /**
   * Display license information in license key field.
   *
   * @since 1.4.0
   *
   */
  function display_license_info( $field ) {

  	$status = get_option( 'af_license_status' );
  	$message = 'Enter your license key and save to activate';

  	if ( ! empty( $status ) ) {

  		if ( 'success' == $status ) {

  			$message = sprintf( '<span style="color: green;">%s</span>', __( 'License valid and active', 'advanced-forms' ) );

  		} else {

  			switch ( $status ) {

  				case 'deactivation_failed':
  					$error_message = __( 'Your previous license could not be deactivated. Please visit hookturn.io and verify that your license is no longer active for this site. If you want to activate a new license, enter it below and save again.', 'advanced-forms' );
  					break;

  				case 'expired':
  					$error_message = __( 'Your license has expired', 'advanced-forms' );
  					break;

  				case 'no_activations_left':
  					$error_message = __( 'Your license has reached its activation limit', 'advanced-forms' );
  					break;

  				case 'invalid':
					case 'site_inactive':
					case 'item_name_mismatch':
					case 'missing':
						$error_message = __( 'Invalid license key', 'advanced-forms' );
						break;

					default:
						$error_message = __( 'An error occurred, please try again', 'advanced-forms' );
						break;

  			}

  			$message = sprintf( '<span style="color: red;">%s</span>', $error_message );

  		}

  	}

  	$field['instructions'] = $message;

  	return $field;

  }


  /**
   * Add license key field to options page
   *
   * @since 1.4.0
   *
   */
  function add_settings_fields( $field_group ) {

    $field_group['fields'][] = array(
      'key' => 'field_af_license_key',
      'name' => 'af_license_key',
      'type' => 'text',
      'label' => 'License Key',
      'instructions' => '',
      'required' => 0,
      'conditional_logic' => 0,
      'wrapper' => array (
        'width' => '',
        'class' => '',
        'id' => '',
      ),
      'hide_admin' => 0,
      'default_value' => '',
      'placeholder' => '',
      'prepend' => '',
      'append' => '',
      'maxlength' => '',
    );

    return $field_group;

  }

}

return new AF_Pro_EDD_Integration();