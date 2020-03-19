<?php
	
/**
 * Get Mailchimp API key
 *
 * @since 1.4.0
 *
 */
function _af_mailchimp_api_key() {
	
	$api_key = get_field( 'af_mailchimp_api_key', 'options' );
	
	return $api_key;
	
}


/**
 * Get Mailchimp data center from API key
 *
 * @since 1.4.0
 *
 */
function _af_mailchimp_data_center() {
	
	$api_key = _af_mailchimp_api_key();
	
	if ( ! $api_key ) {
		return false;
	}
	
	
	$split_key = explode( '-', $api_key );
	
	if ( isset( $split_key[1] ) ) {
		return $split_key[1];
	}
	
	return false;
	
}


/**
 * Get lists from Mailchimp API
 * Returns false if API key is not se
 *
 * @since 1.4.0
 *
 */
function _af_mailchimp_lists() {

	$api_key = _af_mailchimp_api_key();
	$data_center = _af_mailchimp_data_center();
	
	if ( ! $api_key || ! $data_center ) {
		return false;
	}
	
	
	$endpoint = sprintf( 'https://%s.api.mailchimp.com/3.0/lists', $data_center );
	$auth_header = 'Basic ' . base64_encode( sprintf( 'mailchimp:%s', $api_key ) ); 
	
	$response = wp_remote_get( $endpoint, array(
		'headers' => array(
			'Authorization' => $auth_header,
		),
	));
	
	if ( 200 != wp_remote_retrieve_response_code( $response ) ) {
		return false;
	}
	
	
	$decoded_response = json_decode( wp_remote_retrieve_body( $response ), true );
	
	if ( isset( $decoded_response['lists'] ) ) {
		return $decoded_response['lists'];
	}
	
	
	return false;
	
}